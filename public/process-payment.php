<?php

// Check if required parameters are present
if (isset($_GET['amount']) && $_GET['amount'] != '' && isset($_GET['method'])) {

    // Sanitize and validate input parameters
    $amount = intval(sanitize_text_field($_GET['amount'])); // Convert amount to integer
    $email = sanitize_email($_GET['email']);
    $name = sanitize_text_field($_GET['name']);
    $method = sanitize_text_field($_GET['method']);
    $phone = sanitize_text_field($_GET['phone']);
    $description = sanitize_text_field(urldecode($_GET['description']));
    $acceptance = sanitize_text_field($_GET['acceptance']);
    $ifDuplicates = sanitize_text_field($_GET['ifDuplicates']);
    $schoolId = sanitize_text_field($_GET['schoolId']);
    $schoolname = sanitize_text_field($_GET['schoolname']);

    // Validate essential parameters
    if (!$amount || !$email || !$name || !$description || $amount <= 0) {
        wp_die('Invalid payment details. Please check your input.');
    }

    // Prepare metadata
    $metadata = [
        'your_name' => $name,
        'your_email' => $email,
        'your_phone' => $phone,
        'acceptance' => $acceptance,
        'if_duplicates' => $ifDuplicates,
        'school_id' => $schoolId,
        'school_name' => $schoolname,
    ];

    if ($method === 'stripe') {
        // Stripe payment processing
        $stripe_handler = new CMPD_Payment_Handler();
        $redirect_url = $stripe_handler->create_stripe_checkout_session($amount, $email, $name, $description, $metadata);
        header("Location: " . $redirect_url);
        exit;
    } elseif ($method === 'paypal') {
        // PayPal payment processing
        $paypal_handler = new CMPD_Payment_Handler();
        $redirect_url = $paypal_handler->create_paypal_order($amount, $description);
        header("Location: " . $redirect_url);
        exit;
    }
}
?>
