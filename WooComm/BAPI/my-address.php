<?php
/**
 * My Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && get_option( 'woocommerce_calc_shipping' ) !== 'no' ) {
	$page_title    = apply_filters( 'woocommerce_my_account_my_address_title', __( 'My Addresses', 'woocommerce' ) );
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing Address', 'woocommerce' ),
			'shipping' => __( 'Shipping Address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$page_title    = apply_filters( 'woocommerce_my_account_my_address_title', __( 'My Address', 'woocommerce' ) );
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing Address', 'woocommerce' ),
		),
		$customer_id
	);
}

$col = 1;
?>
<div class="address-book">
<?php

$primary_address = get_field( 'primary_address', 'user_' . $customer_id );
$address_book    = get_field( 'address_book', 'user_' . $customer_id );

echo '<div class="primary">
<div class="header">
<h3>Primary Address</h3>';
if ( $primary_address['address']['address_1'] === '' && $primary_address['address']['city'] === '' && $primary_address['address']['state'] === '' ) {
	echo '<a href="#" class="add_primary_address">Add Address</a>';
}

echo '</div>
<p>This address is used as the default shipping and billing address for your online orders. It is also used as the basis for product and service tax calculations.</p>';

if ( $primary_address && $primary_address['address']['address_1'] !== '' && $primary_address['address']['city'] !== '' && $primary_address['address']['state'] !== '' ) {


	echo WC()->countries->get_formatted_address( $primary_address['address'] );

}
echo '</div>';


echo '<div class="additional"><div class="header"><h3>Address Book</h3><a href="#" class="add_address_book_item">Add Address</a></div>';
if ( ! empty( $address_book ) ) {

	echo '<ul>';
	// foreach($address_book as $index => $ab_item){
	while ( have_rows( 'address_book', 'user_' . $customer_id ) ) {
		the_row();
		$address_index = get_row_index() - 1;
		echo '<li>' .
		WC()->countries->get_formatted_address( get_sub_field( 'address' ) ) .
		'<div class="actions"><a data-row_index="' . $address_index . '" data-user_id="' . $customer_id . '" href="#" class="edit">Edit</a><a data-row_index="' . $address_index . '" data-user_id="' . $customer_id . '" href="#" class="delete">Delete</a></div>' .
		'</li>';
	}
	echo '</ul>';
} else {
	echo '<p>You do not currently have any saved addresses.</p>';
}
echo '</div>';
?>

</div>
