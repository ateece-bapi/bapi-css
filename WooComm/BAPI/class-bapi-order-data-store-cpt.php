<?php
global $wpdb;
if ( ! empty( $wpdb->prefix ) ) {
	$wp_table_prefix = $wpdb->prefix;
} elseif ( ! empty( $table_prefix ) ) {
	$wp_table_prefix = $table_prefix;
}

define( 'WPSC_TABLE_CATEGORY_TM', "{$wp_table_prefix}wpsc_category_tm" );
define( 'WPSC_TABLE_ALSO_BOUGHT', "{$wp_table_prefix}wpsc_also_bought" );
define( 'WPSC_TABLE_CART_CONTENTS', "{$wp_table_prefix}wpsc_cart_contents" );
define( 'WPSC_TABLE_META', "{$wp_table_prefix}wpsc_meta" );
define( 'WPSC_TABLE_CART_ITEM_VARIATIONS', "{$wp_table_prefix}wpsc_cart_item_variations" );
define( 'WPSC_TABLE_CHECKOUT_FORMS', "{$wp_table_prefix}wpsc_checkout_forms" );
define( 'WPSC_TABLE_CURRENCY_LIST', "{$wp_table_prefix}wpsc_currency_list" );
define( 'WPSC_TABLE_DOWNLOAD_STATUS', "{$wp_table_prefix}wpsc_download_status" );
define( 'WPSC_TABLE_ITEM_CATEGORY_ASSOC', "{$wp_table_prefix}wpsc_item_category_assoc" );
define( 'WPSC_TABLE_PRODUCT_CATEGORIES', "{$wp_table_prefix}wpsc_product_categories" );
define( 'WPSC_TABLE_PRODUCT_FILES', "{$wp_table_prefix}wpsc_product_files" );
define( 'WPSC_TABLE_PRODUCT_IMAGES', "{$wp_table_prefix}wpsc_product_images" );
define( 'WPSC_TABLE_PRODUCT_LIST', "{$wp_table_prefix}wpsc_product_list" );
define( 'WPSC_TABLE_PRODUCT_ORDER', "{$wp_table_prefix}wpsc_product_order" );
define( 'WPSC_TABLE_PRODUCT_RATING', "{$wp_table_prefix}wpsc_product_rating" );
define( 'WPSC_TABLE_PRODUCT_VARIATIONS', "{$wp_table_prefix}wpsc_product_variations" );
define( 'WPSC_TABLE_PURCHASE_LOGS', "{$wp_table_prefix}wpsc_purchase_logs" );
define( 'WPSC_TABLE_PURCHASE_STATUSES', "{$wp_table_prefix}wpsc_purchase_statuses" );
define( 'WPSC_TABLE_REGION_TAX', "{$wp_table_prefix}wpsc_region_tax" );
define( 'WPSC_TABLE_SUBMITED_FORM_DATA', "{$wp_table_prefix}wpsc_submited_form_data" );
define( 'WPSC_TABLE_VARIATION_ASSOC', "{$wp_table_prefix}wpsc_variation_assoc" );
define( 'WPSC_TABLE_VARIATION_PROPERTIES', "{$wp_table_prefix}wpsc_variation_properties" );
define( 'WPSC_TABLE_VARIATION_VALUES', "{$wp_table_prefix}wpsc_variation_values" );
define( 'WPSC_TABLE_VARIATION_VALUES_ASSOC', "{$wp_table_prefix}wpsc_variation_values_assoc" );
define( 'WPSC_TABLE_COUPON_CODES', "{$wp_table_prefix}wpsc_coupon_codes" );
define( 'WPSC_TABLE_LOGGED_SUBSCRIPTIONS', "{$wp_table_prefix}wpsc_logged_subscriptions" );
define( 'WPSC_TABLE_PRODUCTMETA', "{$wp_table_prefix}wpsc_productmeta" );
define( 'WPSC_TABLE_CATEGORISATION_GROUPS', "{$wp_table_prefix}wpsc_categorisation_groups" );
define( 'WPSC_TABLE_VARIATION_COMBINATIONS', "{$wp_table_prefix}wpsc_variation_combinations" );
define( 'WPSC_TABLE_CLAIMED_STOCK', "{$wp_table_prefix}wpsc_claimed_stock" );

class BAPI_Order_Data_Store_CPT extends WC_Order_Data_Store_CPT implements WC_Object_Data_Store_Interface, WC_Order_Data_Store_Interface {
	public $all                = false;
	public $connected_accounts = array();

	protected $internal_meta_keys = array(
		'_po_number',
		'_attn',
	);

	public function read( &$order ) {
		try {
			parent::read( $order );
		} catch ( Exception $e ) {
			       global $wpdb;
			       $wpec_object = new BAPI_WPEC( $order->get_id() );

			       $processed = (isset($wpec_object->extrainfo) && isset($wpec_object->extrainfo->processed)) ? $wpec_object->extrainfo->processed : null;
			       $date      = (isset($wpec_object->extrainfo) && isset($wpec_object->extrainfo->date)) ? $wpec_object->extrainfo->date : null;

			       $status = null;
			       if ($processed !== null) {
				       $sql    = 'SELECT name FROM ' . WPSC_TABLE_PURCHASE_STATUSES . ' where id = ' . intval($processed);
				       $result = $wpdb->get_results( $sql );
				       if (!empty($result) && isset($result[0]->name)) {
					       $status = $result[0]->name;
				       }
			       }

			       $order->set_props(
				       array(
					       'wpec'          => true,
					       'parent_id'     => -1,
					       'date_created'  => ($date !== null && $date > 0) ? $date : null,
					       'date_modified' => ($date !== null && $date > 0) ? $date : null,
					       'status'        => $status,
				       )
			       );

			       $this->read_order_data( $order, $wpec_object );
			       $order->read_meta_data();
			       $order->set_object_read( true );
		}

	}

	protected function read_order_data( &$order, $wpec_object ) {
		if ( ! $order->get_wpec() ) {
			$id    = $order->get_id();
			$props = array(
				'po_number'           => get_post_meta( $id, '_po_number', true ),
				'bapi_order_number'   => get_post_meta( $id, '_bapi_order_number', true ),
				'attn'                => get_post_meta( $id, '_attn', true ),
				'original_order_id'   => get_post_meta( $id, '_original_order_id', true ),
				'tracking_id'         => get_post_meta( $id, '_tracking_id', true ),
				'invoice_date'        => get_post_meta( $id, '_invoice_date', true ),
				'scheduled_ship_date' => get_post_meta( $id, '_scheduled_ship_date', true ),
				'actual_ship_date'    => get_post_meta( $id, '_actual_ship_date', true ),
				'shipping_id'         => get_post_meta( $id, '_actual_ship_date', true ),
			);

			$order->set_props(
				$props
			);
			parent::read_order_data( $order, $wpec_object );
		} else {
			global $wpdb;
			$id = $order->get_id();

			       $date_completed = isset($wpec_object->extrainfo->date) ? $wpec_object->extrainfo->date : null;
			       $date_paid      = isset($wpec_object->extrainfo->date) ? $wpec_object->extrainfo->date : null;

			       $billing_state = null;
			       if (isset($wpec_object->extrainfo->billing_region)) {
				       $sql           = 'SELECT `name` FROM `' . WPSC_TABLE_REGION_TAX . '` WHERE id=' . $wpec_object->extrainfo->billing_region;
				       $billing_state = $wpdb->get_var( $sql );
			       }

			       $payment_type = isset($wpec_object->customcheckoutfields['Payment Type']['value']) ? $wpec_object->customcheckoutfields['Payment Type']['value'] : '';

			       $order->set_props(
				       array(
					       'order_key'            => null, // get_post_meta( $id, '_order_key', true ),
					       'customer_id'          => isset($wpec_object->extrainfo->user_ID) ? $wpec_object->extrainfo->user_ID : null, // get_post_meta( $id, '_customer_user', true ),
					       'billing_first_name'   => isset($wpec_object->userinfo['billingfirstname']['value']) ? $wpec_object->userinfo['billingfirstname']['value'] : '', // get_post_meta( $id, '_billing_first_name', true ),
					       'billing_last_name'    => isset($wpec_object->userinfo['billinglast']['value']) ? $wpec_object->userinfo['billinglast']['value'] : '', // get_post_meta( $id, '_billing_last_name', true ),
					       'billing_company'      => isset($wpec_object->customcheckoutfields['Billing Company Name']['value']) ? $wpec_object->customcheckoutfields['Billing Company Name']['value'] : '', // get_post_meta( $id, '_billing_company', true ),
					       'billing_address_1'    => isset($wpec_object->userinfo['Address']['value']) ? $wpec_object->userinfo['Address']['value'] : '', // get_post_meta( $id, '_billing_address_1', true ),
					       'billing_address_2'    => isset($wpec_object->userinfo['Address2']['value']) ? $wpec_object->userinfo['Address2']['value'] : '', // get_post_meta( $id, '_billing_address_2', true ),
					       'billing_city'         => isset($wpec_object->userinfo['billingcity']['value']) ? $wpec_object->userinfo['billingcity']['value'] : '', // get_post_meta( $id, '_billing_city', true ),
					       'billing_state'        => $billing_state, // get_post_meta( $id, '_billing_state', true ),
					       'billing_postcode'     => isset($wpec_object->userinfo['billingpostcode']['value']) ? $wpec_object->userinfo['billingpostcode']['value'] : '', // get_post_meta( $id, '_billing_postcode', true ),
					       'billing_country'      => isset($wpec_object->extrainfo->billing_country) ? $wpec_object->extrainfo->billing_country : '', // get_post_meta( $id, '_billing_country', true ),
					       'billing_email'        => isset($wpec_object->userinfo['billingemail']['value']) ? $wpec_object->userinfo['billingemail']['value'] : '', // get_post_meta( $id, '_billing_email', true ),
					       'billing_phone'        => '', // get_post_meta( $id, '_billing_phone', true ),
					       'shipping_first_name'  => isset($wpec_object->shippinginfo['shippingfirstname']['value']) ? $wpec_object->shippinginfo['shippingfirstname']['value'] : '', // get_post_meta( $id, '_shipping_first_name', true ),
					       'shipping_last_name'   => isset($wpec_object->shippinginfo['shippinglastname']['value']) ? $wpec_object->shippinginfo['shippinglastname']['value'] : '', // get_post_meta( $id, '_shipping_last_name', true ),
					       'shipping_company'     => isset($wpec_object->customcheckoutfields['Shipping Company Name']['value']) ? $wpec_object->customcheckoutfields['Shipping Company Name']['value'] : '', // get_post_meta( $id, '_shipping_company', true ),
					       'shipping_address_1'   => isset($wpec_object->shippinginfo['shippingaddress']['value']) ? $wpec_object->shippinginfo['shippingaddress']['value'] : '', // get_post_meta( $id, '_shipping_address_1', true ),
					       'shipping_address_2'   => isset($wpec_object->shippinginfo['shippingaddress2']['value']) ? $wpec_object->shippinginfo['shippingaddress2']['value'] : '', // get_post_meta( $id, '_shipping_address_2', true ),
					       'shipping_city'        => isset($wpec_object->shippinginfo['shippingcity']['value']) ? $wpec_object->shippinginfo['shippingcity']['value'] : '', // get_post_meta( $id, '_shipping_city', true ),
					       'shipping_state'       => isset($wpec_object->shippinginfo['shippingstate']['value']) ? $wpec_object->shippinginfo['shippingstate']['value'] : '', // get_post_meta( $id, '_shipping_state', true ),
					       'shipping_postcode'    => isset($wpec_object->shippinginfo['shippingpostcode']['value']) ? $wpec_object->shippinginfo['shippingpostcode']['value'] : '', // get_post_meta( $id, '_shipping_postcode', true ),
					       'shipping_country'     => isset($wpec_object->extrainfo->shipping_country) ? $wpec_object->extrainfo->shipping_country : '', // get_post_meta( $id, '_shipping_country', true ),
					       'shipping_attn'        => isset($wpec_object->customcheckoutfields['Attn']['value']) ? $wpec_object->customcheckoutfields['Attn']['value'] : '',
					       'payment_method'       => $payment_type, // $wpec_object->userinfo->date, //get_post_meta( $id, '_payment_method', true ),
					       'payment_method_title' => $payment_type, // $wpec_object->userinfo->date, //get_post_meta( $id, '_payment_method_title', true ),
					       'date_completed'       => $date_completed,
					       'date_paid'            => $date_paid,
					       'cart_hash'            => '', // get_post_meta( $id, '_cart_hash', true ),
					       'customer_note'        => '', // $post_object->post_excerpt,
					       'bapi_order_number'    => isset($wpec_object->customcheckoutfields['Bapi Order Number']['value']) ? $wpec_object->customcheckoutfields['Bapi Order Number']['value'] : '',
					       'partials'             => isset($wpec_object->customcheckoutfields['Partials']['value']) ? $wpec_object->customcheckoutfields['Partials']['value'] : '',
					       'po_number'            => isset($wpec_object->customcheckoutfields['PO/Reference Number']['value']) ? $wpec_object->customcheckoutfields['PO/Reference Number']['value'] : '',
					       'scheduled_ship_date'  => isset($wpec_object->customcheckoutfields['Scheduled Ship Date']['value']) ? $wpec_object->customcheckoutfields['Scheduled Ship Date']['value'] : '',
					       'actual_ship_date'     => isset($wpec_object->customcheckoutfields['Actual Ship Date']['value']) ? $wpec_object->customcheckoutfields['Actual Ship Date']['value'] : '',
				       )
			       );
		}

	}

	protected function update_post_meta( &$order ) {
		$updated_props     = array();
		$id                = $order->get_id();
		$meta_key_to_props = array(
			'_po_number' => 'po_number',
			'_attn'      => 'shipping_attn',
		);

		$props_to_update = $this->get_props_to_update( $order, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {

			if ( is_callable( array( $order, 'get_' . $prop ) ) ) {
				$value   = $order->{"get_$prop"}( 'edit' );
				$value   = is_string( $value ) ? wp_slash( $value ) : $value;
				$updated = $this->update_or_delete_post_meta( $order, $meta_key, $value );

				if ( $updated ) {
					$updated_props[] = $prop;
				}
			}
		}

		parent::update_post_meta( $order );
	}

	public function query( $query_vars ) {
		$args = $this->get_wp_query_args( $query_vars );

		if ( ! empty( $args['errors'] ) ) {
			$query = (object) array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			);
		} else {
			$query = new WP_Query( $args );
		}

		$orders = ( isset( $query_vars['return'] ) && 'ids' === $query_vars['return'] ) ? $query->posts : array_filter( array_map( 'wc_get_order', $query->posts ) );

		$wpec_orders = $this->get_wpec_orders( $args );

		$orders = array_merge( $orders, $wpec_orders );
		// $orders = array_slice( $orders, ( ( $query->query_vars['paged'] - 1 ) * $query->query_vars['posts_per_page'] ), $query->query_vars['posts_per_page'] );

		if ( isset( $query_vars['paginate'] ) && $query_vars['paginate'] ) {
			$total         = $query->found_posts + count( $wpec_orders );
			$max_num_pages = ceil( $total / $query->query_vars['posts_per_page'] );
			return (object) array(
				'orders'        => $orders,
				'total'         => $total,
				'max_num_pages' => $max_num_pages,
			);
		}

		return $orders;
	}

	public function get_order_type( $order_id ) {
		if ( get_post_type( $order_id ) ) {
			return get_post_type( $order_id );
		} else {
			return 'wpec';
		}

	}

	private function get_wpec_orders( $args ) {
		global $wpdb;
		$job_estimates_status = 6;
		$favorites_status     = 5;
		$pending_cc_status    = 9;
		
		$user_id = null;

		if ( isset( $args['meta_key'] ) && $args['meta_key'] == '_customer_user' ) {
			$user_id = $args['meta_value'];
		}

		if ( isset( $args['customer'] ) ) {
			$user_id = $args['customer'];
		}

		if ( ! $user_id ) {
			return array();
		}

		$wpec_orders        = array();
		$connected_accounts = $this->connected_accounts;
		if ( $this->all && count( $connected_accounts ) > 0 ) {
			$additional_sql = '(`user_id` = ' . $user_id . ' or `user_id` = ' . implode( ' or `user_id` = ', $connected_accounts ) . ')';
			$strSQL         = 'Select * from `' . WPSC_TABLE_PURCHASE_LOGS . "` where `processed` <> '$job_estimates_status' and `processed` <> '$favorites_status' and `processed` <> '$pending_cc_status' and " . $additional_sql . ' Order by Date desc LIMIT ' . (isset($num_orders) ? $num_orders : 10);
		} else {
			$strSQL = 'Select * from `' . WPSC_TABLE_PURCHASE_LOGS . "` where `processed` <> '$job_estimates_status' and `processed` <> '$favorites_status' and `processed` <> '$pending_cc_status' and `user_id` = " . $user_id . ' ' . ($additional_sql ?? null) . ' Order by Date desc ';
		}

		$orders = $wpdb->get_results( $strSQL, ARRAY_A );

		foreach ( $orders as $key => $order ) {
			$user_sql = 'Select f.name, d.value from ' . WPSC_TABLE_CHECKOUT_FORMS . ' f left join ' . WPSC_TABLE_SUBMITED_FORM_DATA . ' d on f.id = d.form_id where d.log_id = ' . $order['id'];

			$user_info = $wpdb->get_results( $user_sql, ARRAY_A );
			if ( count( $user_info ) > 0 ) {
				foreach ( $user_info as $item ) {
					if ( $item['name'] == 'PO/Reference Number' ) {
						$order_po             = $item['value'];
						$orders[ $key ]['po'] = $order_po;
					} elseif ( $item['name'] == 'Bapi Order Number' ) {
						$order_sales_order_num               = $item['value'];
						$orders[ $key ]['bapi_order_number'] = $order_sales_order_num;
					}
				}
			}

			$orders[ $key ]['wpec_order'] = true;
			if ( $this->search != '' && $this->all ) {
				if ( in_array( strtolower( $this->search ), array_map( 'strtolower', $orders[ $key ] ) ) ) {
					$wpec_orders[] = $orders[ $key ];
				}
			} else {
				$wpec_orders[] = $orders[ $key ];
			}
		}

		$wpec_orders = ( isset( $query_vars['return'] ) && 'ids' === $query_vars['return'] ) ? array( 1, 2 ) : array_filter( array_map( 'wc_get_order', $wpec_orders ) );

		return $wpec_orders;
	}

}
