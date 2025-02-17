<?php
/**
 * Plugin Name: CMPD Stripe Integration
 * Description: Integrates Stripe with Elementor form, allowing payments and post metadata retrieval based on payment ID.
 * Version: 1.1
 * Author: Your Name
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('CLASSMATE_PLAYDATE_API_KEY', '5889a6de-25fb-4694-99fc-3d31cd56e671');


// Load Stripe library if available
require_once __DIR__ . '/vendor/autoload.php';

require_once 'vendor/paypal/paypal-server-sdk/src/PaypalServerSdkClient.php';
require_once 'vendor/paypal/paypal-server-sdk/src/PaypalServerSdkClientBuilder.php';

use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;

use PaypalServerSdkLib\PaypalServerSdkClientBuilder;


use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;

require_once 'vendor/stripe/stripe-php/init.php';

/**
 * Class CMPD_Payment_Integration
 *
 * Handles integration of Stripe payment with WordPress, including AJAX handlers, shortcodes,
 * and REST API webhook initialization. Singleton pattern is used to ensure only one instance is loaded.
 */
class CMPD_Payment_Integration {

    /**
     * Holds the singleton instance of this class.
     *
     * @var CMPD_Payment_Integration|null
     */
    private static $instance = null;

    /**
     * Retrieve or create the singleton instance of the class.
     *
     * @return CMPD_Payment_Integration
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor to initialize hooks, load dependencies, and set up required components.
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Loads all required dependencies for the plugin.
     *
     * This method includes files responsible for:
     * - Custom post types setup.
     * - Admin settings for Stripe keys and configurations.
     * - Webhook setup for handling Stripe webhooks.
     * - Payment processing functionalities.
     */
    private function load_dependencies() {
        require_once plugin_dir_path( __FILE__ ) . 'admin/class-cmpd-payment-postype.php';
        require_once plugin_dir_path( __FILE__ ) . 'admin/class-cmpd-payment-settings.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-cmpd-payment-webhook.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-cmpd-payment-handler.php';
        require_once plugin_dir_path( __FILE__ ) . 'public/class-cmpd-payment-script-handler.php';
        require_once plugin_dir_path( __FILE__ ) . 'public/process-payment.php';
    }

    /**
     * Initializes all hooks and filters for the plugin.
     *
     * Registers actions and filters for both admin and frontend contexts:
     * - Sets up admin menu and settings page.
     * - Enqueues frontend scripts for payment handling.
     * - Registers custom rewrite rules for payment endpoints.
     * - Initializes REST API for webhook handling.
     * - Registers AJAX handlers and shortcodes.
     */
    private function init_hooks() {
        if ( is_admin() ) {
            $admin_settings = new CMPD_Payment_Settings();
            add_action( 'admin_menu', [ $admin_settings, 'add_settings_page' ] );
            add_action( 'admin_init', [ $admin_settings, 'register_settings' ] );
        } else {
            $handler = new CMPD_Payment_Script_Handler();
            add_action( 'wp_enqueue_scripts', [ $handler, 'enqueue_scripts' ] );
        }

        // Registers rewrite rules and REST API
        add_action( 'init', [ $this, 'register_payment_endpoints' ] );
        add_action( 'rest_api_init', [ 'CMPD_Payment_Webhook', 'init' ] );

        add_shortcode( 'cmpd_payment_confirmation', [ $this, 'cmpd_payment_confirmation_shortcode' ] );

    }

    /**
     * Registers custom rewrite rules for handling payment processes.
     *
     * This function defines custom URL patterns to handle specific payment processing actions,
     * such as initiating a payment and displaying a success page.
     */
    public function register_payment_endpoints() {
        add_rewrite_rule( '^process-payment/?$', 'index.php?cmpd_process_payment=1', 'top' );
        add_rewrite_rule( '^process-payment-paypal/?$', 'index.php?cmpd_process_payment=1', 'top' );
        add_rewrite_rule( '^payment-success/?$', 'index.php?cmpd_payment_success=1', 'top' );
    }

    /**
     * Shortcode to display payment confirmation details.
     *
     * Outputs a `div` for displaying payment confirmation data. Uses JavaScript to retrieve
     * session data stored in `sessionStorage` and makes an AJAX call to fetch details by `payment_id`.
     *
     * @return string HTML output for the confirmation shortcode.
     */
    public function cmpd_payment_confirmation_shortcode() {
        // Start output buffering
        ob_start();

        // Check if session_id exists in the URL
        if ( isset( $_GET['session_id'] ) && ! empty( $_GET['session_id'] ) ) {
            $session_id = sanitize_text_field( $_GET['session_id'] );

            // Render the HTML for success and error messages
            ?>
            <div class="payment-success-wrapper" style="display: none;" id="success-wrapper">
                <h2>Payment Successful!</h2>
                <p>Thank you for your payment.</p>
                <p id="activation-code" style="display: none;"></p>
                <label>
                    <input type="checkbox" id="reveal-checkbox" onclick="revealActivationCode()"> Reveal Activation Code
                </label>
            </div>

            <div class="payment-error-wrapper" style="display: none;" id="error-wrapper">
                <h2>Error!</h2>
                <p id="message-error"></p>
            </div>

            <script>
                async function fetchActivationCode() {
                    try {
                        // Trigger the REST API to process the payment session
                        const response = await fetch('<?php echo esc_url( home_url( '/wp-json/cmpd/v1/stripe' ) ); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ session_id: '<?php echo esc_js( $session_id ); ?>' })
                        });

                        // Process the response
                        if (response.ok) {
                            const responseData = await response.json();
                            console.log("Response Data:", responseData);
                            const results = responseData.result;

                            // Check for success or specific error status
                            if (results.status === 'success') {
                                document.getElementById('success-wrapper').style.display = 'block';
                                document.getElementById('activation-code').textContent = `Your activation code: ${results.activation_code}`;
                            } else if (results.status === 'already_activated') {
                                document.getElementById('error-wrapper').style.display = 'block';
                                document.getElementById('message-error').textContent = 'The school is already activated. Please check your email or contact support.';
                            } else {
                                document.getElementById('error-wrapper').style.display = 'block';
                                document.getElementById('message-error').textContent = results.message || 'An error occurred while processing your payment.';
                            }
                        } else {
                            // Handle non-200 HTTP responses
                            document.getElementById('error-wrapper').style.display = 'block';
                            document.getElementById('message-error').textContent = 'An error occurred while processing your payment. Please try again or contact support.';
                            console.error('API call failed:', response.statusText);
                        }
                    } catch (error) {
                        // Handle network or other unexpected errors
                        document.getElementById('error-wrapper').style.display = 'block';
                        document.getElementById('message-error').textContent = 'An error occurred while processing your payment. Please try again or contact support.';
                        console.error('Fetch error:', error);
                    }
                }

                // Start fetching activation code when script loads
                fetchActivationCode();

                // Function to reveal activation code on checkbox click
                function revealActivationCode() {
                    const checkbox = document.getElementById('reveal-checkbox');
                    const activationCode = document.getElementById('activation-code');

                    if (checkbox.checked) {
                        activationCode.style.display = 'block'; // Show the activation code
                    } else {
                        activationCode.style.display = 'none'; // Hide the activation code
                    }
                }
            </script>
            <?php
        } else {
            // Handle missing session_id
            ?>
            <div class="payment-error-wrapper">
                <h2>Error!</h2>
                <p>No session ID provided. Please check your payment details or contact support.</p>
            </div>
            <?php
        }

        // Return the buffer contents and clean the buffer
        return ob_get_clean();
    }

}

// Initialize the plugin singleton instance
CMPD_Payment_Integration::get_instance();