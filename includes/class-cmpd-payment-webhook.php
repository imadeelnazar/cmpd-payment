<?php

/**
 * Class CMPD_Payment_Webhook
 *
 * Handles Stripe webhook notifications and processes events received from Stripe.
 */
class CMPD_Payment_Webhook {

    /**
     * Initializes the webhook by adding it to the rest_api_init action.
     * Sets up custom REST API endpoints for Stripe webhook and form data storage.
     *
     * @return void
     */
    public static function init() {
        add_action( 'rest_api_init', [ self::class, 'register_webhook_route' ] );
        add_action( 'rest_api_init', [ self::class, 'register_webhook_stripe_route' ] );
        add_action( 'rest_api_init', [ self::class, 'register_paypal_route' ] );


    }

    /**
     * Registers the REST route for the webhook within the rest_api_init action.
     * This route listens for POST requests sent to /wp-json/cmpd/v1/webhook.
     *
     * @return void
     */
    public static function register_webhook_route() {
        register_rest_route( 'cmpd/v1', '/webhook', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'handle_webhook' ],
            'permission_callback' => '__return_true',
        ] );
    }

    public static function register_webhook_stripe_route() {
        register_rest_route( 'cmpd/v1', '/stripe', [
            'methods'  => 'POST',  // Adjust to 'POST' if you plan to use POST
            'callback'            => [ self::class, 'handle_stripe_webhook_api' ],
            'permission_callback' => '__return_true',
        ] );
    }

    /**
     * Handles the incoming Stripe webhook request.
     * Verifies the Stripe signature, processes the event, and performs any necessary actions.
     *
     * @param WP_REST_Request $request The incoming REST API request.
     * @return WP_REST_Response Response for Stripe or error handling.
     */
    public static function handle_webhook(WP_REST_Request $request) {
        // Retrieve the raw request body and Stripe signature
        $payload = $request->get_body();
        $signature = $request->get_header('stripe-signature');

        // Retrieve the endpoint's secret key from your settings
        $endpoint_secret = get_option('endpoint_secret');

        // Verify the Stripe signature
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, // Raw payload from Stripe
                $signature, // Stripe signature header
                $endpoint_secret // Endpoint secret
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            error_log('Webhook signature verification failed: ' . $e->getMessage());
            return new WP_REST_Response('Webhook signature verification failed', 400);
        } catch (\Exception $e) {
            error_log('Error processing webhook payload: ' . $e->getMessage());
            return new WP_REST_Response('Invalid payload', 400);
        }

        // Log the incoming event for debugging
        error_log('Received Stripe event: ' . print_r($event, true));

        // Handle the event type
        switch ($event->type) {
            case 'checkout.session.completed':
                // Handle successful payment
                $session = $event->data->object;
                break;

            case 'checkout.session.async_payment_failed':
                // Handle async payment failure
                $session = $event->data->object;
                self::handle_failed_payment($session);
                break;

            // Add support for additional event types here
            case 'invoice.payment_failed':
                // Example: Handle a failed invoice payment
                $invoice = $event->data->object;
                error_log('Invoice payment failed for: ' . $invoice->id);
                break;

            case 'payment_intent.succeeded':
                // Example: Handle successful payment intent
                $payment_intent = $event->data->object;
                error_log('Payment succeeded for intent: ' . $payment_intent->id);
                break;

            default:
                // Log unsupported event types
                error_log('Unhandled event type: ' . $event->type);
                return new WP_REST_Response('Event type not supported', 400);
        }

        // Return a response if no matching event type was found
        return new WP_REST_Response('Webhook processed.', 200);
    }


    /**
     * Handles a failed payment.
     *
     * @param \Stripe\Checkout\Session $session The Stripe session object.
     * @return void
     */
    private static function handle_failed_payment($session) {
        // Log the failed payment details
        error_log('Payment failed for session: ' . $session->id);
        $email = $session->customer_email;

        if ($email) {
            error_log('Notifying customer: ' . $email);

            // Example: Send a notification to the customer
            // wp_mail($email, 'Payment Failed', 'Your payment has failed. Please try again.');
        }
    }

    public static function handle_stripe_webhook_api($request) {
         // Retrieve session_id from the request parameters
        $session_id = $request->get_param('session_id');

        // Check if session_id exists and is not empty
        if ( ! empty( $session_id ) ) {
            // Sanitize session_id for safety
            $session_id = sanitize_text_field( $session_id );

            // Set up Stripe with the secret key
            $stripe_secret_key = get_option( 'stripe_secret_key' );
            \Stripe\Stripe::setApiKey( $stripe_secret_key );

            // Retrieve the Stripe session using the sanitized session_id
            try {
                $session = \Stripe\Checkout\Session::retrieve( $session_id );
            } catch (\Exception $e) {
                error_log('Error retrieving Stripe session: ' . $e->getMessage());
                return new WP_REST_Response('Failed to retrieve Stripe session', 400);
            }

            // Check if customer_email exists and is valid, then sanitize
            $email = isset($session->customer_email) ? sanitize_email( $session->customer_email ) : null;
            if ( ! $email ) {
                error_log('Customer email not found in Stripe session: ' . print_r($session, true));
                return new WP_REST_Response('Customer email not found in session.', 400);
            }

            // Proceed with processing the payment session
            $post_id = self::process_successful_payment($session);

            return new WP_REST_Response([
                    'status' => 'success',
                    'session_id' => $session_id,
                    'result' => $post_id,
                ], 200);
        }

        // Return a response if session_id is missing or empty
        return new WP_REST_Response('Session ID not provided or empty', 400);

    }

   /**
     * Processes a successful Stripe payment session.
     *
     * @param object $session Stripe session object containing customer and metadata details.
     * @return array|null Returns an array with 'post_id' and 'activation_code' on success, or null on failure.
     */
    private static function process_successful_payment( $session ) {
        // Ensure email exists and sanitize it
        $email = isset($session->customer_email) ? sanitize_email( $session->customer_email ) : null;
        if ( ! $email ) {
            error_log('Customer email not found in Stripe session: ' . print_r($session, true));
            return;  // Stop processing if email is not found
        }

        // Sanitize and validate session_id
        $session_id = isset($session->id) ? sanitize_text_field( $session->id ) : null;
        if ( ! $session_id ) {
            error_log('Session ID not found in Stripe session.');
            return;  // Stop processing if session ID is missing
        }

        // Extract metadata from the session
        $metadata = $session->metadata;

        // Check if metadata is present
        if ( empty( $metadata ) ) {
            error_log('No metadata found for email: ' . $email);
            return;  // Stop processing if metadata is missing
        }

        // Retrieve specific fields from metadata
        $school_id = sanitize_text_field( $metadata['school_id'] );
        $your_email = sanitize_email( $metadata['your_email'] );
        $your_name = sanitize_text_field( $metadata['your_name'] );
        $your_phone = sanitize_text_field( $metadata['your_phone'] );

        // Insert post with payment information
        $post_id = wp_insert_post( [
            'post_title'  => 'Payment Received from ' . $your_email,
            'post_type'   => 'cmpd_payments',
            'post_status' => 'publish'
        ] );

        // Check if the post was created successfully
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            // Save metadata to the post
            update_post_meta( $post_id, 'email_id', $your_email );
            update_post_meta( $post_id, 'payment_id', $session_id );
            update_post_meta( $post_id, 'school_id', $school_id );
            update_post_meta( $post_id, 'your_name', $your_name );
            update_post_meta( $post_id, 'your_phone', $your_phone );
            update_post_meta( $post_id, 'status', 'completed' );

            // API URL for activation
            $activate_api_url = 'https://dev-test.api.classmateplaydate.com/classmate-playdate/api/v1/web/school/activate';

            // Send the activation request to the external API
            $response = wp_remote_post( $activate_api_url, [
                'method'    => 'POST',
                'headers'   => [
                    'accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'apikey'        => CLASSMATE_PLAYDATE_API_KEY, // Use the constant directly
                ],
                'body'      => json_encode( [
                    'phone'              => $your_phone,
                    'firstName'          => $your_name,
                    'lastName'           => $your_name, // Modify as needed
                    'email'              => $your_email,
                    'needHelpToContact'  => true,
                    'paymentId'          => $session_id,
                    'schoolId'           => $school_id,
                ] ),
            ] );

            // Check for errors in the API response
            if ( is_wp_error( $response ) ) {
                error_log( 'Error activating school: ' . $response->get_error_message() );
            } else {
                // Decode and process the API response
                $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

                // Check if the API response is successful
                if ( isset( $response_body['success'] ) && $response_body['success'] ) {
                    $result = $response_body['result'];

                    // Validate and sanitize the necessary fields
                    $activation_code = isset( $result['activationCode'] ) ? sanitize_text_field( $result['activationCode'] ) : null;
                    $school = isset( $result['school'] ) ? sanitize_text_field( $result['school'] ) : null;
                    $activated_at = isset( $result['activatedAt'] ) ? sanitize_text_field( $result['activatedAt'] ) : null;

                    if ( $activation_code && $school && $activated_at ) {
                        // Save necessary details to post meta
                        update_post_meta( $post_id, 'activation_code', $activation_code );
                        update_post_meta( $post_id, 'school', $school );
                        update_post_meta( $post_id, 'activated_at', $activated_at );

                        // Return success response with activation code and post ID
                        return [
                            'status'          => 'success',
                            'message'         => 'School successfully activated.',
                            'post_id'         => $post_id,
                            'activation_code' => $activation_code,
                        ];
                    } else {
                        // Log error if required fields are missing
                        error_log( 'API response missing required fields: ' . wp_remote_retrieve_body( $response ) );

                        return [
                            'status'  => 'error',
                            'message' => 'Required fields are missing in the API response.',
                            'post_id' => $post_id,
                        ];
                    }
                } elseif ( isset( $response_body['statusCode'] ) && $response_body['statusCode'] === 400 ) {
                    // Handle specific error case: School already activated
                    if ( isset( $response_body['message'] ) && $response_body['message'] === 'School already activated' ) {
                        return [
                            'status'  => 'already_activated',
                            'message' => 'The school is already activated.',
                            'post_id' => $post_id,
                        ];
                    } else {
                        // Handle other 400-level errors
                        error_log( 'Bad Request: ' . wp_remote_retrieve_body( $response ) );

                        return [
                            'status'  => 'error',
                            'message' => 'Failed to activate the school. ' . $response_body['message'],
                            'post_id' => $post_id,
                        ];
                    }
                } else {
                    // Log unexpected responses
                    error_log( 'Unexpected API response: ' . wp_remote_retrieve_body( $response ) );

                    return [
                        'status'  => 'error',
                        'message' => 'An unexpected error occurred during activation.',
                        'post_id' => $post_id,
                    ];
                }

            }

            // Return the post ID if no activation code is available
            return [
                'post_id'         => $post_id,
                'activation_code' => null,
            ];
        } else {
            // Log the error if the post insertion failed
            error_log( 'Failed to insert payment post: ' . $post_id->get_error_message() );
            return null;  // Stop processing if post insertion fails
        }
    }

}

// Initialize the class to set up the API endpoints
CMPD_Payment_Webhook::init();
