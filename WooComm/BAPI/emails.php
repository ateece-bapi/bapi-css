<?php
function sgs_generate_receipt_message( $order, $admin = false ) {
	$user = (method_exists($order, 'get_user')) ? $order->get_user() : null;
	// Initialize variables to avoid undefined warnings
	$product_list = '';
	$total_shipping_email = '';
	$total_price_email = '';
	$purchase_log = array('find_us' => '');
	$purch_id = '';
	if ( ! $admin ) {
		// Subject for the email and the title of the email contents
		$message_subject = 'BAPI Submitted Order Receipt';

		// Get the "Purchase Receipt" value from WP-Admin > Store > Settings > Admin
		$message_intro = stripslashes( get_option( 'wpsc_email_receipt' ) );
	} else {
		// Subject for the email and the title of the email contents
		$message_subject = 'Purchase Request - WebStore Order #' . $purch_id;

		// Get the "Purchase Receipt" value from WP-Admin > Store > Settings > Admin
		$message_intro = stripslashes( get_option( 'wpsc_email_admin' ) );
	}

	// Replace any place holders with the actual values
	$message_intro = str_replace( '%product_list%', $product_list, $message_intro );
	$message_intro = str_replace( '%total_shipping%', $total_shipping_email, $message_intro );
	$message_intro = str_replace( '%total_price%', $total_price_email, $message_intro );
	$message_intro = str_replace( '%shop_name%', get_option( 'blogname' ), $message_intro );
	$message_intro = str_replace( '%find_us%', isset($purchase_log['find_us']) ? $purchase_log['find_us'] : '', $message_intro );

	// Get the order status
	$status = wc_get_order_status_name( $order->get_status() );

	// Get the billing addresses
	$billing_address = $order->get_formatted_billing_address();

	// Get the shipping address
	$shipping_address = $order->get_formatted_shipping_address();
	$order_id = $order->get_id();
	if ( $attn = get_post_meta( $order_id, '_attn', true ) ) {
		$shipping_address .= '<BR>Attn: ' . $attn;
	}

	// Get miscellaneous fields
	$bapi_sales_order_num = get_post_meta( $order_id, '_bapi_order_number', true );
	$partials             = get_post_meta( $order_id, '_partials', true );
	$po                   = get_post_meta( $order_id, '_po_number', true );
	$payment_type         = get_post_meta( $order_id, '_payment_method', true );
	$scheduled_ship_date  = get_post_meta( $order_id, '_scheduled_ship_date', true );
	$actual_ship_date     = get_post_meta( $order_id, '_actual_ship_date', true );
	$invoice_num          = get_post_meta( $order_id, '_invoice_num', true );
	$invoice_date         = get_post_meta( $order_id, '_invoice_date', true );
	$comments = method_exists($order, 'get_customer_note') ? $order->get_customer_note() : (property_exists($order, 'customer_note') ? $order->customer_note : '');

	// Let's start compiling this ugly-ass message...
	$message_html  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . PHP_EOL;
	$message_html .= '<html dir="ltr" lang="en-US">' . PHP_EOL;
	$message_html .= '<head>' . PHP_EOL;
	$message_html .= '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">' . PHP_EOL;
	$message_html .= "<title>$message_subject</title>" . PHP_EOL;
	$message_html .= '<style type="text/css">th {margin:0; font-size:12px; color: #444;} td {margin:0; font-size:12px; color: #444;}</style>' . PHP_EOL;
	$message_html .= '</head>' . PHP_EOL;
	$message_html .= '<body bgcolor="white" style="color: #444; font-weight: normal; font-size:12px; background-color: white; line-height: 1.3333; font-family: Arial, Helvetica, sans-serif; color: black; padding:10px;">' . PHP_EOL;
	if ( ! $admin ) {
		$message_html .= '<p>Problems reading this email? You can also view your submitted order receipt by signing in to <a href="http://bapihvac.com/account/orders/">your account</a> at http://bapihvac.com/account/orders/.</p>' . PHP_EOL;
	}
	$message_html .= $message_intro . PHP_EOL;
	// $message_html .=  '<div class="orders">';
	// Order summary/details table
	$message_html .= '<table style="margin: 10px 0; border-collapse: collapse; text-align:left; width:100%; font-family:Arial, Helvetica, san-serif;">' . PHP_EOL;
	$message_html .= '\t<tbody>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="date" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Order Date</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="order-num" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">WebStore Order #</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="ba-num" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">BAPI Sales Order #</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="status" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Order Status</th>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$order_date = method_exists($order, 'get_date_created') ? $order->get_date_created() : (property_exists($order, 'order_date') ? $order->order_date : '');
	$order_id_val = method_exists($order, 'get_id') ? $order->get_id() : (property_exists($order, 'id') ? $order->id : '');
	$message_html .= '\t\t\t<td align="left" valign="middle" class="date" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . ($order_date ? date( 'm/d/y', strtotime( (string)$order_date ) ) : '') . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="order-num" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $order_id_val . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="ba-num" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $bapi_sales_order_num . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="status" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;"><big>' . wc_get_order_status_name( $order->get_status() ) . '</big></td>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<th colspan="2" align="left" valign="middle" bgcolor="#236ABA" class="bill-to" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Bill To</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th colspan="2" align="left" valign="middle" bgcolor="#236ABA" class="ship-to" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Ship To</th>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<td colspan="2" align="left" valign="top" class="bill-to" style="padding: 5px; vertical-align: top; border-top: 1px solid #e7e7e7;">' . $billing_address . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td colspan="2" align="left" valign="top" class="ship-to" style="padding: 5px; vertical-align: top; border-top: 1px solid #e7e7e7;">' . $shipping_address . '</td>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="buyer" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Buyer</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="fob" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">FOB Point</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="partials" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Partials</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th colspan="2" align="left" valign="middle" bgcolor="#236ABA" class="blank" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">&nbsp;</th>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$buyer_name = '';
	if (is_object($user)) {
		$first_name = '';
		$last_name = '';
		if (property_exists($user, 'first_name')) {
			$first_name = $user->first_name;
		} elseif (method_exists($user, 'get')) {
			$first_name = $user->get('first_name');
		}
		if (property_exists($user, 'last_name')) {
			$last_name = $user->last_name;
		} elseif (method_exists($user, 'get')) {
			$last_name = $user->get('last_name');
		}
		$buyer_name = trim($first_name . ' ' . $last_name);
	}
	$message_html .= '\t\t\t<td align="left" valign="middle" class="buyer" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $buyer_name . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="fob" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">Gays Mills, WI</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="partials" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $partials . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td colspan="2" align="left" valign="middle" class="blank" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">&nbsp;</td>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="po-num" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Customer PO #</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="billto-id" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Bill To ID</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th colspan="2" align="left" valign="middle" bgcolor="#236ABA" class="blank" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">&nbsp;</th>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="po-num" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $po . '</td>' . PHP_EOL;
	$billto_id = '';
	if (is_object($user) && (property_exists($user, 'ID') || method_exists($user, 'get_id'))) {
		$user_id = property_exists($user, 'ID') ? $user->ID : (method_exists($user, 'get_id') ? $user->get_id() : '');
		if ($user_id) {
			$billto_id = get_field( 'filemaker_billing_id', 'user_' . $user_id );
		}
	}
	$message_html .= '\t\t\t<td align="left" valign="middle" class="billto-id" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $billto_id . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td colspan="2" align="left" valign="middle" class="blank" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">&nbsp;</td>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="salesperson" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">BAPI Salesperson</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="payment-type" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Payment Type</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="scheduled-ship" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Scheduled Ship Date</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="actual-ship" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Actual Ship Date</th>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$sales_person = '';
	if (is_object($user) && (property_exists($user, 'ID') || method_exists($user, 'get_id'))) {
		$user_id = property_exists($user, 'ID') ? $user->ID : (method_exists($user, 'get_id') ? $user->get_id() : '');
		if ($user_id) {
			$sales_person = get_field( 'sales_person', 'user_' . $user_id );
		}
	}
	$message_html .= '\t\t\t<td align="left" valign="middle" class="salesperson" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $sales_person . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="payment-type" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $payment_type . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="scheduled-ship" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $scheduled_ship_date . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="actual-ship" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $actual_ship_date . '</td>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="ship-method" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Shipping Method</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="track-num" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Tracking #</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="invoice-num" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Invoice #</th>' . PHP_EOL;
	$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="invoice-date" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Invoice Date</th>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t\t<tr>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="ship-method" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . (method_exists($order, 'get_shipping_method') ? $order->get_shipping_method() : '') . '</td>' . PHP_EOL;
	$message_html .= '          <td align="left" valign="middle" class="track-num" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;"><a href="http://wwwapps.ups.com/etracking/tracking.cgi?TypeOfInquiryNumber=T&amp;InquiryNumber1=' . esc_html(get_post_meta( $order_id, '_tracking_id', true )) . '" title="Track this order (opens new window/tab)" target="_blank" style="color: #1462aa;">' . esc_html(get_post_meta( $order_id, '_tracking_id', true )) . '</a></td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="invoice-num" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $invoice_num . '</td>' . PHP_EOL;
	$message_html .= '\t\t\t<td align="left" valign="middle" class="invoice-date" style="padding: 5px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $invoice_date . '</td>' . PHP_EOL;
	$message_html .= '\t\t</tr>' . PHP_EOL;
	$message_html .= '\t</tbody>' . PHP_EOL;
	$message_html .= '</table>' . PHP_EOL;

	// Get the cart details
	if (method_exists($order, 'get_items')) {
		$items = $order->get_items();
	} else {
		$items = array();
	}
	if (!empty($items)) {
		// Order individual items listing table
		$message_html .= '<h2 style="font-size: 1.7em; margin: 0 0 10px 10px; border-bottom: none; padding: 8px 0 0; ">Items</h2>' . PHP_EOL;
		$message_html .= '<table class="items" style="margin: 10px 0; border-collapse: collapse; text-align: left; width: 100%; font-size:.9em; font-family:Arial, Helvetica, san-serif;s">' . PHP_EOL;
		$message_html .= '\t<thead>' . PHP_EOL;
		$message_html .= '\t\t<tr>' . PHP_EOL;
		$message_html .= '\t\t\t<th class="qty" align="left" valign="middle" bgcolor="#236ABA" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; text-align: center; vertical-align: middle;">Qty</th>' . PHP_EOL;
		$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="sku" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Part Number</th>' . PHP_EOL;
		$message_html .= '\t\t\t<th align="left" valign="middle" bgcolor="#236ABA" class="descript" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; vertical-align: middle;">Name</th>' . PHP_EOL;
		$message_html .= '\t\t\t<th class="ncnr" align="left" valign="middle" bgcolor="#236ABA" style="font-weight: bold; background-color: #236ABA; padding: 5px; color: #fff; text-align: center; vertical-align: middle;"></th>' . PHP_EOL;
		$message_html .= '\t\t\t<th class="price" align="left" valign="middle" bgcolor="#236ABA" style="font-weight: bold; background-color: #236ABA; padding: 5px; white-space: nowrap; color: #fff; text-align: right; vertical-align: middle;">Unit Price</th>' . PHP_EOL;
		$message_html .= '\t\t\t<th class="price" align="left" valign="middle" bgcolor="#236ABA" style="font-weight: bold; background-color: #236ABA; padding: 5px; white-space: nowrap; color: #fff; text-align: right; vertical-align: middle;">Total</th>' . PHP_EOL;
		$message_html .= '\t\t</tr>' . PHP_EOL;
		$message_html .= '\t</thead>' . PHP_EOL;
		$message_html .= '\t<tbody>' . PHP_EOL;

		foreach ($items as $item_id => $item) {
			$product_id = isset($item['product_id']) ? $item['product_id'] : null;
			$product = $product_id ? wc_get_product($product_id) : null;
			$part_number = '';
			if (method_exists($order, 'get_original_order_id') && $order->get_original_order_id() && isset($item['sku'])) {
				$part_number = $item['sku'];
			} elseif (is_object($item) && method_exists($item, 'get_product') && is_object($item->get_product()) && method_exists($item->get_product(), 'get_sku')) {
				$part_number = $item->get_product()->get_sku();
			} elseif (isset($item['sku'])) {
				$part_number = $item['sku'];
			}
			$qty = isset($item['qty']) ? $item['qty'] : '';
			$name = isset($item['name']) ? $item['name'] : '';
			$unit_price = (is_object($order) && method_exists($order, 'get_item_total')) ? wc_price($order->get_item_total($item, false, true)) : '';
			$line_subtotal = (is_object($order) && method_exists($order, 'get_formatted_line_subtotal')) ? $order->get_formatted_line_subtotal($item) : '';
			$message_html .= '\t\t\t<tr class="cart_row">' . PHP_EOL;
			$message_html .= '\t\t\t\t<td class="qty" align="center" valign="middle" style="padding: 5px 5px 10px; text-align: center; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $qty . '</td>' . PHP_EOL;
			$message_html .= '\t\t\t\t<td class="sku" valign="middle" style="padding: 5px 5px 10px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $part_number . '</td>' . PHP_EOL;
			$message_html .= '\t\t\t\t<td class="descript" valign="middle" style="padding: 5px 5px 10px; vertical-align: middle; border-top: 1px solid #e7e7e7;">' . $name . '</td>' . PHP_EOL;
			$message_html .= '\t\t\t\t<td class="ncnr" align="center" valign="middle" style="padding: 5px 5px 10px; text-align: center; vertical-align: middle; border-top: 1px solid #e7e7e7;"></td>' . PHP_EOL;
			$message_html .= '\t\t\t\t<td class="price" align="right" valign="middle" style="white-space: nowrap; padding: 5px 5px 10px; text-align: right; vertical-align: middle; border-top: 1px solid #e7e7e7;"><span class="pricedisplay">' . $unit_price . '</span></td>' . PHP_EOL;
			$message_html .= '\t\t\t\t<td class="price" align="right" valign="middle" style="white-space: nowrap; padding: 5px 5px 10px; text-align: right; vertical-align: middle; border-top: 1px solid #e7e7e7;"><span class="pricedisplay">' . $line_subtotal . '</span></td>' . PHP_EOL;
			$message_html .= '\t\t\t</tr>' . PHP_EOL;
		} // End item foreach

		$message_html .= '\t</tbody>' . PHP_EOL;
		$message_html .= '\t<tfoot>' . PHP_EOL;
		$message_html .= '\t\t<tr>' . PHP_EOL;
		$item_count = (is_object($order) && method_exists($order, 'get_item_count')) ? $order->get_item_count() : '';
		$message_html .= '\t\t\t<td class="qty" id="total_qty" align="center" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; color: #000; text-align: center; vertical-align: middle; border-top: 1px solid #55A2E9;">' . $item_count . '</td>' . PHP_EOL;
		$message_html .= '\t\t\t<td class="line-title" align="right" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; color: #000; text-align: right; vertical-align: middle; border-top: 1px solid #55A2E9;" colspan="4">Subtotal</td>' . PHP_EOL;
		$subtotal = (is_object($order) && method_exists($order, 'get_subtotal')) ? wc_price($order->get_subtotal()) : '';
		$message_html .= '\t\t\t<td class="price" id="total_price" align="right" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; white-space: nowrap; color: #000; text-align: right; vertical-align: middle; border-top: 1px solid #55A2E9;"><span class="pricedisplay">' . $subtotal . '</span></td>' . PHP_EOL;
		$message_html .= '\t\t</tr>' . PHP_EOL;
		if (is_object($order) && method_exists($order, 'get_total_discount') && $order->get_total_discount() > 0) {
			$message_html .= '<tr>' . PHP_EOL;
			$message_html .= '          <td class="line-title" align="right" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; color: #000; text-align: right; vertical-align: middle;" colspan="5">Discount</td>' . PHP_EOL;
			$discount_display = method_exists($order, 'get_discount_to_display') ? $order->get_discount_to_display() : '';
			$message_html .= '          <td class=" price" align="right" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; white-space: nowrap; color: #000; text-align: right; vertical-align: middle; border-top: 1px solid #e7e7e7;"><span class="pricedisplay">' . $discount_display . '</span></td>' . PHP_EOL;
			$message_html .= '</tr>' . PHP_EOL;
		}
		$message_html  .= '\t\t<tr>' . PHP_EOL;
		$message_html  .= '\t\t\t<td class="line-title" align="right" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; color: #000; text-align: right; vertical-align: middle; border-top: 1px solid #55A2E9;" colspan="5">Shipping &amp; Handling</td>' . PHP_EOL;
		$shipping_price = 'TBD';
		$shipping_method = (is_object($order) && method_exists($order, 'get_shipping_method')) ? $order->get_shipping_method() : '';
		$shipping_country = (is_object($order) && method_exists($order, 'get_shipping_country')) ? $order->get_shipping_country() : '';
		$shipping_state = (is_object($order) && method_exists($order, 'get_shipping_state')) ? $order->get_shipping_state() : '';
		if (
			$shipping_method == 'Ground' &&
			$shipping_country == 'USA' &&
			$shipping_state != 'Alaska' && $shipping_state != 'Hawaii'
		) {
			$shipping_price = 'Free';
		}
		if (is_object($order) && method_exists($order, 'get_total_shipping') && $order->get_total_shipping() > 0) {
			$shipping_price = wc_price($order->get_total_shipping());
		}
		$message_html .= '\t\t\t<td class="shipping price" align="right" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; white-space: nowrap; color: #000; text-align: right; vertical-align: middle; border-top: 1px solid #e7e7e7;"><span class="pricedisplay">' . $shipping_price . '</span></td>' . PHP_EOL;
		$message_html .= '\t\t</tr>' . PHP_EOL;
		if (is_object($order) && method_exists($order, 'get_total_tax') && $order->get_total_tax() > 0) {
			$message_html .= '   <tr>' . PHP_EOL;
			$message_html .= '     <td class="line-title" align="right" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; color: #000; text-align: right; vertical-align: middle;" colspan="5">Tax</td>' . PHP_EOL;
			$message_html .= '     <td class="shipping price" align="right" valign="middle" bgcolor="#fff" style="font-weight: normal; background-color: #fff; padding: 5px; white-space: nowrap; color: #000; text-align: right; vertical-align: middle; border-top: 1px solid #e7e7e7;"><span class="pricedisplay">' . wc_price($order->get_total_tax()) . '</span></td>' . PHP_EOL;
			$message_html .= '   </tr>' . PHP_EOL;
		}
		$message_html .= '\t\t<tr>' . PHP_EOL;
		$grand_total = (is_object($order) && method_exists($order, 'get_total')) ? wc_price($order->get_total()) : '';
		$message_html .= '\t\t\t<td class="grand-total line-title" align="right" valign="middle" bgcolor="#fff" style="font-weight: bold; background-color: #fff; padding: 5px; color: #000; text-align: right; vertical-align: middle; border-top: 1px solid #55A2E9;" colspan="5">Total</td>' . PHP_EOL;
		$message_html .= '\t\t\t<td class="grand-total price" align="right" valign="middle" bgcolor="#fff" style="font-weight: bold; background-color: #fff; padding: 5px; white-space: nowrap; color: #000; text-align: right; vertical-align: middle; border-top: 1px solid #55A2E9;"><span class="pricedisplay">' . $grand_total . '</span></td>' . PHP_EOL;
		$message_html .= '\t\t</tr>' . PHP_EOL;
		$message_html .= '\t</tfoot>' . PHP_EOL;
		$message_html .= '</table>' . PHP_EOL;
	} // End item listing IF check

	$message_html .= '<p class="note" style="font-size: 1em; margin: 5px 0; padding: 0; color: #444; font-style: italic;">All orders and products are subject to review to confirm availability and pricing. You will be contacted promptly with any necessary changes. You may also wish to view our <a href="http://bapihvac.com/ordering-shipping/" style="color: #1462aa;">Return Policy</a>.</p>' . PHP_EOL;
	if ( strtolower( get_post_meta( $order_id, '_payment_method', true ) ) == 'bill my account' && $order->get_total_shipping() == 0 ) {
		$message_html .= '<p class="note" style="font-size: 1em; margin: 5px 0; padding: 0; color: #444; font-style: italic; float:left;">Actual Shipping and Handling will be calculated at time of shipment.</p>';
	}

	if ( $comments != '' ) {
		$message_html .= '<h3 style="font-size: 14px; border-bottom: 1px solid #55A2E9; margin: 24px 0 0; padding: 0 0 3px; color: #1462AA; ">Buyer Comments</h3>' . PHP_EOL;
		$message_html .= '<div class="comments">' . $comments . '</div>' . PHP_EOL;
	}

	// $message_html .=  '</div><!-- .orders -->' . PHP_EOL;
	// The message footer
	$message_html .= '<hr style="clear:both;">' . PHP_EOL;
	if ( ! $admin ) {
		$message_html .= '<h4>Thank you for your order!</h4>' . PHP_EOL;
	} else {
		$message_html .= '<h4>&nbsp;</h4>' . PHP_EOL;
	}
	$message_html .= '</hr>' . PHP_EOL;

	$message_html .= '<p><a href="http://bapihvac.com">Building Automation Products, Inc.</a><br>' . PHP_EOL;
	$message_html .= '750 North Royal Avenue<br>' . PHP_EOL;
	$message_html .= 'Gays Mills, WI 54631 USA<br>' . PHP_EOL;
	$message_html .= '+1-608-735-4800<br>' . PHP_EOL;
	$message_html .= '<a href="mailto:sales@bapihvac.com">sales@bapihvac.com</a></p>' . PHP_EOL;
	$message_html .= '</body>' . PHP_EOL;
	$message_html .= '</html>' . PHP_EOL;

	// Wheew, that was a PITA, wasn't it?!

	return $message_html;
}
