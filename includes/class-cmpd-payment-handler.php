<?php

use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;

/**
 * Class CMPD_Payment_Webhook
 *
 * Handles webhook notifications and payment processing for Stripe and PayPal.
 * Retrieves API keys from WordPress theme options.
 */
class CMPD_Payment_Handler {

    /**
     * @var \PaypalServerSdkLib\PaypalServerSdkClient
     * Holds the PayPal client instance
     */
    private $paypalClient;

    /**
     * Constructor: Initializes Stripe and PayPal clients.
     */
    public function __construct() {
        $this->initialize_stripe_client();
        $this->initialize_paypal_client();
    }

    /**
     * Initializes the Stripe client with API credentials from WordPress options.
     */
    private function initialize_stripe_client() {
        $stripeSecretKey = get_option('stripe_secret_key');
        if (!$stripeSecretKey) {
            wp_die('Stripe secret key is missing. Please configure it in your WordPress settings.');
        }
        \Stripe\Stripe::setApiKey($stripeSecretKey);
    }

    /**
     * Initializes the PayPal client with sandbox or live credentials from WordPress options.
     */
    private function initialize_paypal_client() {
        $isSandbox = get_option('paypal_environment') === 'sandbox'; // Toggle between sandbox and live based on option
        $paypalClientId = get_option('paypal_client_id');
        $paypalClientSecret = get_option('paypal_client_secret');

        if (!$paypalClientId || !$paypalClientSecret) {
            wp_die('PayPal client credentials are missing. Please configure them in your WordPress settings.');
        }

        $this->paypalClient = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    $paypalClientId,
                    $paypalClientSecret
                )
            )
            ->environment($isSandbox ? Environment::SANDBOX : Environment::LIVE)
            ->build();
    }

    /**
     * Creates a Stripe Checkout session.
     *
     * @param float $amount Payment amount in USD.
     * @param string $email Customer's email address.
     * @param string $description Description of the item or service.
     * @param array $metadata Additional metadata to attach to the payment.
     * @return string URL of the Stripe Checkout session.
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function create_stripe_checkout_session($amount, $email, $description, $metadata = []) {
        try {
            if ($amount <= 0) {
                throw new Exception('Invalid amount specified for payment.');
            }

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $email,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $description],
                        'unit_amount' => $amount * 100, // Convert dollars to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => home_url('/thank-you?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => home_url('/payment-cancel'),
                'metadata' => $metadata,
            ]);

            return $session->url;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Creates a PayPal order and retrieves the approval link.
     *
     * @param float $amount Payment amount in USD.
     * @param string $description Description of the item or service.
     * @return string PayPal approval URL for the order.
     * @throws Exception If unable to create the PayPal order.
     */
    public function create_paypal_order($amount, $description) {
        try {
            $orderBody = OrderRequestBuilder::init(
                CheckoutPaymentIntent::CAPTURE,
                [
                    PurchaseUnitRequestBuilder::init(
                        AmountWithBreakdownBuilder::init('USD', $amount)->build()
                    )->build()
                ]
            )->build();

            $apiResponse = $this->paypalClient->getOrdersController()->ordersCreate(['body' => $orderBody]);
            $orderData = $apiResponse->getResult();

            // Use the getter method to retrieve the links
            $links = $orderData->getLinks();

            foreach ($links as $link) {
                if ($link->getRel() === 'approve') {
                    return $link->getHref();
                }

            }

            throw new Exception('Unable to retrieve PayPal approval URL.');
        } catch (Exception $e) {
            error_log("PayPal API error: " . $e->getMessage());
            throw $e;
        }
    }

}

// Initialize the webhook class
$cmpdWebhook = new CMPD_Payment_Webhook();
