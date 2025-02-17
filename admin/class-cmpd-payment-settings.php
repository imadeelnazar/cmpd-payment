<?php
class CMPD_Payment_Settings {

    /**
     * Add settings page for CMPD payment settings.
     */
    public function add_settings_page() {
        add_options_page(
            'CMPD Payment Settings',
            'Payment Settings',
            'manage_options',
            'cmpd-payment-settings',
            [ $this, 'display_settings_page' ]
        );
    }

    /**
     * Display the settings page with tabs for Stripe and PayPal settings.
     */
    public function display_settings_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'stripe_settings';

        echo '<div class="wrap"><h1>CMPD Payment Settings</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=cmpd-payment-settings&tab=api_settings" class="nav-tab ' . ($active_tab === 'api_settings' ? 'nav-tab-active' : '') . '">API Settings</a>';
        echo '<a href="?page=cmpd-payment-settings&tab=stripe_settings" class="nav-tab ' . ($active_tab === 'stripe_settings' ? 'nav-tab-active' : '') . '">Stripe Settings</a>';
        echo '<a href="?page=cmpd-payment-settings&tab=paypal_settings" class="nav-tab ' . ($active_tab === 'paypal_settings' ? 'nav-tab-active' : '') . '">PayPal Settings</a>';

        echo '</h2>';
        echo '<form method="post" action="options.php">';

        if ($active_tab === 'stripe_settings') {
            settings_fields('cmpd_stripe_settings');
            do_settings_sections('cmpd_stripe_settings');
        } elseif ($active_tab === 'paypal_settings') {
            settings_fields('cmpd_paypal_settings');
            do_settings_sections('cmpd_paypal_settings');
        }elseif ($active_tab === 'api_settings') {
            settings_fields('cmpd_api_settings');
            do_settings_sections('cmpd_api_settings');
        }

        submit_button();
        echo '</form></div>';
    }

    /**
     * Register settings for Stripe and PayPal.
     */
    public function register_settings() {
        // Stripe settings
        register_setting('cmpd_stripe_settings', 'stripe_secret_key');
        register_setting('cmpd_stripe_settings', 'stripe_publishable_key');
        register_setting('cmpd_stripe_settings', 'stripe_amount');
        register_setting('cmpd_stripe_settings', 'stripe_description');
        register_setting('cmpd_stripe_settings', 'stripe_endpoint_secret'); // Register endpoint secret

        add_settings_section('cmpd_stripe_keys', 'Stripe API Keys', null, 'cmpd_stripe_settings');
        add_settings_field('stripe_secret_key', 'Secret Key', [$this, 'stripe_secret_key_callback'], 'cmpd_stripe_settings', 'cmpd_stripe_keys');
        add_settings_field('stripe_publishable_key', 'Publishable Key', [$this, 'stripe_publishable_key_callback'], 'cmpd_stripe_settings', 'cmpd_stripe_keys');
        add_settings_field('stripe_amount', 'Default Amount', [$this, 'stripe_amount_callback'], 'cmpd_stripe_settings', 'cmpd_stripe_keys');
        add_settings_field('stripe_description', 'Default Description', [$this, 'stripe_description_callback'], 'cmpd_stripe_settings', 'cmpd_stripe_keys');
        add_settings_field('stripe_endpoint_secret', 'Endpoint Secret', [$this, 'stripe_endpoint_secret_callback'], 'cmpd_stripe_settings', 'cmpd_stripe_keys'); // Add field for endpoint secret

        // PayPal settings
        register_setting('cmpd_paypal_settings', 'paypal_client_id');
        register_setting('cmpd_paypal_settings', 'paypal_client_secret');
        register_setting('cmpd_paypal_settings', 'paypal_webhook_id');
        register_setting('cmpd_paypal_settings', 'paypal_environment'); // Live or sandbox
        register_setting('cmpd_paypal_settings', 'paypal_return_url');
        register_setting('cmpd_paypal_settings', 'paypal_cancel_url');

        add_settings_section('cmpd_paypal_keys', 'PayPal API Keys', null, 'cmpd_paypal_settings');
        add_settings_field('paypal_client_id', 'Client ID', [$this, 'paypal_client_id_callback'], 'cmpd_paypal_settings', 'cmpd_paypal_keys');
        add_settings_field('paypal_client_secret', 'Client Secret', [$this, 'paypal_client_secret_callback'], 'cmpd_paypal_settings', 'cmpd_paypal_keys');
        add_settings_field('paypal_webhook_id','Webhook ID',[$this, 'paypal_webhook_id_callback'],'cmpd_paypal_settings','cmpd_paypal_keys');
        add_settings_field('paypal_environment', 'Environment', [$this, 'paypal_environment_callback'], 'cmpd_paypal_settings', 'cmpd_paypal_keys');
        add_settings_field('paypal_return_url', 'Return URL', [$this, 'paypal_return_url_callback'], 'cmpd_paypal_settings', 'cmpd_paypal_keys');
        add_settings_field('paypal_cancel_url', 'Cancel URL', [$this, 'paypal_cancel_url_callback'], 'cmpd_paypal_settings', 'cmpd_paypal_keys');



        register_setting('cmpd_api_settings', 'classmate_api_key');
        register_setting('cmpd_api_settings', 'classmate_api_url');
        register_setting('cmpd_api_settings', 'classmate_api_url_new');

        add_settings_section('cmpd_api_keys', 'API Keys', null, 'cmpd_api_settings');
        add_settings_field('classmate_api_key', 'API Secret Key', [$this, 'classmate_api_key_callback'], 'cmpd_api_settings', 'cmpd_api_keys');
        add_settings_field('classmate_api_url', 'API URL', [$this, 'classmate_api_url_key_callback'], 'cmpd_api_settings', 'cmpd_api_keys');
        add_settings_field('classmate_api_url_new', 'API URL Activation', [$this, 'classmate_api_url_new_callback'], 'cmpd_api_settings', 'cmpd_api_keys');


    }

    public function paypal_webhook_id_callback() {
        $webhook_id = get_option('paypal_webhook_id', '');
        echo "<input type='text' name='paypal_webhook_id' value='" . esc_attr($webhook_id) . "' />";
        echo "<p class='description'>The Webhook ID provided by PayPal for your webhook setup.</p>";
    }


    public function paypal_return_url_callback() {
        $returnUrl = get_option('paypal_return_url', home_url('/thank-you'));
        echo "<input type='text' name='paypal_return_url' value='" . esc_attr($returnUrl) . "' />";
        echo "<p class='description'>The URL where users will be redirected after successful payment.</p>";
    }

    public function paypal_cancel_url_callback() {
        $cancelUrl = get_option('paypal_cancel_url', home_url('/payment-cancel'));
        echo "<input type='text' name='paypal_cancel_url' value='" . esc_attr($cancelUrl) . "' />";
        echo "<p class='description'>The URL where users will be redirected if payment is canceled.</p>";
    }


    /**
     * Stripe Secret Key callback.
     */
    public function classmate_api_key_callback() {
        $secret_key = get_option('classmate_api_key');
        echo "<input type='text' name='classmate_api_key' value='" . esc_attr($secret_key) . "' />";
    }

    /**
     * Stripe Secret Key callback.
     */
    public function classmate_api_url_key_callback() {
        $secret_key = get_option('classmate_api_url');
        echo "<input type='text' name='classmate_api_url' value='" . esc_attr($secret_key) . "' />";
    }

    /**
     * Stripe Secret Key callback.
     */
    public function classmate_api_url_new_callback() {
        $secret_key = get_option('classmate_api_url_new');
        echo "<input type='text' name='classmate_api_url_new' value='" . esc_attr($secret_key) . "' />";
    }

    /**
     * Stripe Secret Key callback.
     */
    public function stripe_secret_key_callback() {
        $secret_key = get_option('stripe_secret_key');
        echo "<input type='text' name='stripe_secret_key' value='" . esc_attr($secret_key) . "' />";
    }

    /**
     * Stripe Publishable Key callback.
     */
    public function stripe_publishable_key_callback() {
        $publishable_key = get_option('stripe_publishable_key');
        echo "<input type='text' name='stripe_publishable_key' value='" . esc_attr($publishable_key) . "' />";
    }

    /**
     * Stripe Default Amount callback.
     */
    public function stripe_amount_callback() {
        $amount = get_option('stripe_amount');
        echo "<input type='number' name='stripe_amount' value='" . esc_attr($amount) . "' min='0' step='0.01' />";
    }

    /**
     * Stripe Default Description callback.
     */
    public function stripe_description_callback() {
        $description = get_option('stripe_description');
        echo "<input type='text' name='stripe_description' value='" . esc_attr($description) . "' />";
    }

    /**
     * PayPal Client ID callback.
     */
    public function paypal_client_id_callback() {
        $client_id = get_option('paypal_client_id');
        echo "<input type='text' name='paypal_client_id' value='" . esc_attr($client_id) . "' />";
        echo "<p class='description'>Your PayPal API Client ID. This is required to connect with PayPal.</p>";
    }

    /**
     * Stripe Endpoint Secret callback.
     */
    public function stripe_endpoint_secret_callback() {
        $endpoint_secret = get_option('stripe_endpoint_secret');
        echo "<input type='text' name='stripe_endpoint_secret' value='" . esc_attr($endpoint_secret) . "' />";
        echo "<p class='description'>The secret key used to verify Stripe webhook signatures.</p>";
    }

    /**
     * PayPal Client Secret callback.
     */
    public function paypal_client_secret_callback() {
        $client_secret = get_option('paypal_client_secret');
        echo "<input type='text' name='paypal_client_secret' value='" . esc_attr($client_secret) . "' />";
        echo "<p class='description'>Your PayPal API Client Secret. Keep this key secure and do not share it.</p>";
    }

    /**
     * PayPal Environment callback.
     */
    public function paypal_environment_callback() {
        $environment = get_option('paypal_environment', 'sandbox');
        echo "<select name='paypal_environment'>
                <option value='sandbox'" . selected($environment, 'sandbox', false) . ">Sandbox</option>
                <option value='live'" . selected($environment, 'live', false) . ">Live</option>
              </select>";
        echo "<p class='description'>Select whether you are using the PayPal sandbox for testing or live for production payments.</p>";
    }
}
