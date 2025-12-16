<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
if (!$order) {
    return;
}
$user = $order->get_user();
$invoice_date = ($order->get_invoice_date() !== '') ? date_create_from_format('mdY', $order->get_invoice_date()) : false;
$invoice_date_formatted = 'No Date';
if ($invoice_date) {
    $invoice_date_formatted = $invoice_date->format('m/d/y');
}
$scheduled_ship_date = ($order->get_scheduled_ship_date() !== '') ? date_create_from_format('mdY', $order->get_scheduled_ship_date()) : false;
if ($scheduled_ship_date) {
    $scheduled_ship_date_formatted = $scheduled_ship_date->format('m/d/y');
}
$actual_ship_date = ($order->get_actual_ship_date() !== '') ? date_create_from_format('mdY', $order->get_actual_ship_date()) : false;
if ($actual_ship_date) {
    $actual_ship_date_formatted = $actual_ship_date->format('m/d/y');
}
$admin = $admin ?? false;
?>
<?php if (!$admin) { ?>
    <p><a class="print-order-btn" href="javascript:window.print();">Print Order Detail</a></p>
<?php } ?>
<div class="orders">
    <table class="details">
        <tbody>
        <tr>
            <th class="date">Order Date</th>
            <th class="order-num">WebStore Order #</th>
            <th class="ba-num">BAPI Sales Order #</th>
            <th class="status">Order Status</th>
        </tr>
        <tr>
            <td class="date"><?php echo date('m/d/y', strtotime($order->get_date_created())); ?></td>
            <td class="order-num"><?php echo $order->get_order_number(); ?></td>
            <td class="ba-num"><font><?php echo $order->get_bapi_order_number(); ?></font></td>
            <td class="status"><?php echo wc_get_order_status_name($order->get_status()); ?></td>
        </tr>
        <tr>
            <th class="bill-to" colspan="2">Bill To</th>
            <th class="ship-to" colspan="2">Ship To</th>
        </tr>
        <tr>
            <td class="bill-to" colspan="2"><?php echo $order->get_formatted_billing_address(); ?></td>
            <td class="ship-to" colspan="2">
                <?php
                echo $order->get_formatted_shipping_address();
                if ($attn = get_post_meta($order->get_id(), '_attn', true)) {
                    echo '<BR>Attn: ' . $attn;
                }
                ?>
            </td>
        </tr>
        <tr>
            <th class="buyer">Buyer</th>
            <th class="fob">FOB Point</th>
            <th class="partials">Partials</th>
            <th class="logo">&nbsp;</th>
        </tr>
        <?php
        if ($admin) {
            $buyer = '<a href="user-edit.php?user_id=' . $user->ID . '" target="_blank">' . $user->first_name . ' ' . $user->last_name . '</a>';
        } else {
            $buyer = $user->first_name . ' ' . $user->last_name;
        }
        ?>
        <tr>
            <td class="buyer"><?php echo $buyer; ?></td>
            <td class="fob">Gays Mills, WI</td>
            <td class="partials"><?php echo $order->get_partials(); ?></td>
            <td class="logo"></td>
        </tr>
        <tr>
            <th class="po-num">Customer PO #</th>
            <th class="billto-id">Bill To ID</th>
            <th class="blank" colspan="2">&nbsp;</th>
        </tr>
        <tr>
            <td class="po-num"><?php echo $order->get_po_number(); ?></td>
            <td class="billto-id"><?php echo get_field('filemaker_billing_id', 'user_' . $order->get_user_id()); ?></td>
            <td class="blank" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <th class="salesperson">BAPI Salesperson</th>
            <th class="payment-type">Payment Type</th>
            <th class="scheduled-ship">Scheduled Ship Date</th>
            <th class="actual-ship">Actual Ship Date</th>
        </tr>
        <tr>
            <td class="salesperson"><?php echo get_field('sales_person', 'user_' . $order->get_user_id()); ?></td>
            <td class="payment-type"><?php echo $order->get_payment_method_title(); ?></td>
            <td class="scheduled-ship"><?php echo $scheduled_ship_date_formatted ?? null; ?></td>
            <td class="actual-ship"><?php echo $actual_ship_date_formatted ?? null; ?></td>
        </tr>
        <tr>
            <th class="ship-method">Shipping Method</th>
            <th class="track-num">Tracking #</th>
            <th class="invoice-num">Invoice #</th>
            <th class="invoice-date">Invoice Date</th>
        </tr>
        <tr>
            <td class="ship-method"><?php echo $order->get_shipping_method(); ?></td>
            <td class="track-num"><a title="Track this order (opens new window/tab)" target="_blank"
                                     href="http://wwwapps.ups.com/etracking/tracking.cgi?TypeOfInquiryNumber=T&amp;InquiryNumber1=<?php echo get_post_meta($order->get_id(), '_tracking_id', true); ?>"><?php echo get_post_meta($order->get_id(), '_tracking_id', true); ?></a>
            </td>
            <td class="invoice-num"><?php echo $order->get_bapi_order_number(); ?></td>
            <td class="invoice-date"><?php echo $invoice_date_formatted; ?></td>
        </tr>
        </tbody>
    </table>
    <h2>Items</h2>
    <table class="items">
        <thead>
        <tr>
            <th class="qty">Qty</th>
            <th class="sku">Part Number</th>
            <th class="descript">Name</th>
            <th class="ncnr">&nbsp;</th>
            <th class="price">Unit Price</th>
            <th class="price">Total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($order->get_items() as $item_id => $item) { ?>
            <?php $product = wc_get_product($item['product_id']); ?>
            <tr class="cart_row">
                <td class="qty"><?php echo $item['qty']; ?></td>
                <td class="sku"><?php echo ($order->get_original_order_id()) ? $item['sku'] : $item->get_product()->get_sku(); ?></td>
                <?php
                if ($product) {
                    $link = add_query_arg(
                        array(
                            'combo' => $item['combo'],
                            '#configure' => '',
                        ),
                        $product->get_permalink()
                    );
                    if ($admin) {
                        $link = get_edit_post_link($product->get_id());
                    }
                    ?>
                    <td class="descript"><a href="<?php echo $link; ?>" target="_blank"><?php echo $item['name']; ?></a>
                    </td>
                <?php } else { ?>
                    <td class="descript"><?php echo $item['name']; ?></td>
                <?php } ?>
                <td class="ncnr">&nbsp;</td>
                <td class="price"><span
                            class="pricedisplay"><?php echo wc_price($order->get_item_total($item, false, true)); ?></span>
                </td>
                <td class="price"><span class="pricedisplay"><?php echo wc_price($item['line_total']); ?></span></td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <td class="qty" id="total_qty"><?php echo $order->get_item_count(); ?></td>
            <th class="line-title" colspan="4">Subtotal</th>
            <td class="price" id="total_price"><span
                        class="pricedisplay"><?php echo wc_price($order->get_subtotal()); ?></span></td>
        </tr>
        <!-- Create a table row for each fee -->
        <?php foreach ($order->get_items('fee') as $item_id => $item): ?>
            <tr>
                <th colspan="5"> <?php esc_html_e($item->get_name()); ?></th>
                <td><?php echo wc_price($item->get_total(), ['currency' => $order->get_currency()]); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <th class="line-title" colspan="5">Shipping &amp; Handling</th>
            <td class="shipping price">
                <?php
                $shipping_price = 'TBD';
                if ($order->get_shipping_method() == 'Ground' && $order->get_shipping_country() == 'USA' && $order->get_shipping_state() != 'Alaska' && $order->get_shipping_state() != 'Hawaii') {
                    $shipping_price = 'Free';
                }
                if ($order->get_shipping_total() > 0) {
                    $shipping_price = wc_price($order->get_shipping_total());
                }
                echo $shipping_price;
                ?>
            </td>
        </tr>
        <tr>
            <th class="line-title" colspan="5">Tax</th>
            <td class="tax price"><?php echo wc_price($order->get_total_tax()); ?></td>
        </tr>
        <tr>
            <th class="grand-total line-title" colspan="5">Total</th>
            <td class="grand-total price"><span class="pricedisplay"><?php echo wc_price($order->get_total()); ?></span>
            </td>
        </tr>
        </tfoot>
    </table>
    <p class="note">All orders and products are subject to review to confirm availability and pricing. You will be
        contacted promptly with any necessary changes. You may also wish to view our <a href="/ordering-shipping/">Return
            Policy</a>.</p>
    <?php if (strtolower(get_post_meta($order->get_id(), '_payment_method', true)) == 'bill my account' && $order->get_shipping_total() == 0) { ?>
        <p class="note" style="float:left;">BAPI offers free ground shipping within the contiguous U.S. Other domestic
            shipment charges will be prepaid and added to the invoice.</p>
    <?php } ?>
    <span class="clear">&nbsp;</span>
    <h3>Buyer Comments</h3>
    <div class="comments"><?php echo $order->get_customer_note(); ?></div>
    <div class="submit">
        <p><a class="print-order-btn" href="javascript:window.print();">Print Order Detail</a></p>
        <p><a class="view-order-history-btn" href="<?php echo get_permalink(get_page_by_title('Order History')); ?>">&larr;
                View Order History</a>&nbsp;&nbsp;</p>
    </div>
</div>
