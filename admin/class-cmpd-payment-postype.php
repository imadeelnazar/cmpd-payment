<?php

// Register the CMPD Payment Post Type
function register_cmpd_payments_post_type() {
    $labels = [
        'name'               => 'CMPD Payments',
        'singular_name'      => 'CMPD Payment',
        'menu_name'          => 'CMPD Payments',
        'name_admin_bar'     => 'CMPD Payment',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New CMPD Payment',
        'new_item'           => 'New CMPD Payment',
        'edit_item'          => 'Edit CMPD Payment',
        'view_item'          => 'View CMPD Payment',
        'all_items'          => 'All CMPD Payments',
        'search_items'       => 'Search CMPD Payments',
        'not_found'          => 'No CMPD Payments found.',
        'not_found_in_trash' => 'No CMPD Payments found in Trash.',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'show_in_menu'       => true,
        'supports'           => ['title','custom-fields'],
        'capability_type'    => 'post',
        'register_meta_box_cb' => 'add_cmpd_payments_meta_boxes', // Add meta boxes
    ];

    register_post_type( 'cmpd_payments', $args );
}
add_action( 'init', 'register_cmpd_payments_post_type' );

// Add Meta Boxes for CMPD Payment Fields
function add_cmpd_payments_meta_boxes() {
    add_meta_box(
        'cmpd_payments_meta',
        'CMPD Payment Details',
        'render_cmpd_payments_meta_box',
        'cmpd_payments',
        'normal',
        'default'
    );
}

function render_cmpd_payments_meta_box( $post ) {
    $verification_code = get_post_meta( $post->ID, 'verification_code', true );
    $created_time = get_post_meta( $post->ID, 'created_time', true );
    $code_revealed = get_post_meta( $post->ID, 'code_revealed', true );
    $session_id = get_post_meta( $post->ID, 'session_id', true );
    $email_id = get_post_meta( $post->ID, 'email_id', true );

    echo '<p><strong>Verification Code:</strong> ' . esc_html( $verification_code ) . '</p>';
    echo '<p><strong>Created Time:</strong> ' . esc_html( $created_time ) . '</p>';
    echo '<p><strong>Session ID:</strong> ' . esc_html( $session_id ) . '</p>';
    echo '<p><strong>Email ID:</strong> ' . esc_html( $email_id ) . '</p>';
    echo '<p><strong>Code Revealed:</strong> ' . ( $code_revealed === 'yes' ? 'Yes' : 'No' ) . '</p>';


}

// Add Custom Columns in Admin View
function set_cmpd_payments_columns( $columns ) {
    $columns['verification_code'] = 'Verification Code';
    $columns['created_time'] = 'Created Time';
    $columns['code_revealed'] = 'Code Revealed';
    return $columns;
}
add_filter( 'manage_cmpd_payments_posts_columns', 'set_cmpd_payments_columns' );

function custom_cmpd_payments_column( $column, $post_id ) {
    if ( 'verification_code' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'verification_code', true ) );
    } elseif ( 'created_time' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'created_time', true ) );
    } elseif ( 'code_revealed' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'code_revealed', true ) === 'yes' ? 'Yes' : 'No' );
    }
}
add_action( 'manage_cmpd_payments_posts_custom_column', 'custom_cmpd_payments_column', 10, 2 );


function update_code_reveal_status() {
    if ( isset( $_POST['post_id'] ) && isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'reveal_code_nonce' ) ) {
        $post_id = intval( $_POST['post_id'] );
        update_post_meta( $post_id, 'code_revealed', 'yes' );
        wp_send_json_success( 'Code reveal status updated.' );
    } else {
        wp_send_json_error( 'Invalid nonce or missing Post ID.' );
    }
}
add_action( 'wp_ajax_update_code_reveal_status', 'update_code_reveal_status' );
add_action( 'wp_ajax_nopriv_update_code_reveal_status', 'update_code_reveal_status' );
