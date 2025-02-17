<?php
class CMPD_Payment_Script_Handler {
    public function enqueue_scripts() {
        wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/' );
        wp_enqueue_script( 'cmpd-payment-handler', plugin_dir_url( __FILE__ ) . 'assets/js/stripe-handler.js', ['jquery'], null, true );
        wp_localize_script( 'cmpd-payment-handler', 'cmpd_ajax', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'stripe_key' => get_option( 'stripe_publishable_key' ),
            'stripe_amount' => get_option( 'stripe_amount' ),
            'stripe_description' => get_option( 'stripe_description' ),
            'nonce' => wp_create_nonce('process_payment')
        ] );
    }
}

// Initialize the CMPD_Payment_Script_Handler
$handler = new CMPD_Payment_Script_Handler();
add_action( 'wp_enqueue_scripts', [ $handler, 'enqueue_scripts' ] );
