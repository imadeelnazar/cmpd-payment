<?php
/*
Template Name: Process Payment
*/
if (isset($_GET['amount']) && $_GET['amount'] != '') {
    // Retrieve and set Stripe secret key
    $stripe_secret_key = get_option('stripe_secret_key');
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    // Sanitize and retrieve input parameters
    $amount = intval(sanitize_text_field($_GET['amount'])); // Convert amount to integer
    $email = sanitize_email($_GET['email']);
    $name = sanitize_text_field($_GET['name']);
    $phone = sanitize_text_field($_GET['phone']);
    $description = urldecode(sanitize_text_field($_GET['description']));

    // Prepare metadata
    $metadata = [
        'your_name' => $name,
        'your_email' => $email,
        'your_phone' => $phone,
        'acceptance' => sanitize_text_field($_GET['acceptance']),
        'if_duplicates' => sanitize_text_field($_GET['ifDuplicates']),
        'school_id' => sanitize_text_field($_GET['schoolId']),
        'school_name' => sanitize_text_field($_GET['schoolname']),
    ];

    try {
        // Create a secure Stripe Checkout session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $email, // Set email directly
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $description,
                    ],
                    'unit_amount' => $amount * 100, // Amount in cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'expand' => ['line_items'],
            'success_url' => home_url('/thank-you?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => home_url('/payment-cancel'),
            'metadata' => $metadata,
        ]);

        // Redirect to Stripe Checkout
        header("Location: " . $session->url);
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Handle error
        error_log("Stripe API error: " . $e->getMessage());
        wp_die('There was an error processing your payment. Please try again later.');
    }
}
?>
