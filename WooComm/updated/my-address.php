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
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

// Custom BAPI address book logic (from previous template)
$primary_address = get_field( 'primary_address', 'user_' . $customer_id );
$address_book    = get_field( 'address_book', 'user_' . $customer_id );

?>
<div class="address-book">
<?php
// Primary Address Block

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

// Address Book Block
echo '<div class="additional"><div class="header"><h3>Address Book</h3><a href="#" class="add_address_book_item">Add Address</a></div>';
if ( ! empty( $address_book ) ) {
	echo '<ul>';
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
