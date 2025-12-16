<?php
class BAPI_WPEC {

    var $purchlogid;
    var $extrainfo;

    //the loop
    var $currentitem = - 1;
    var $purchitem;
    var $allcartcontent;
    var $purch_item_count;

    //grand total
    var $totalAmount;

    //usersinfo
    var $userinfo;
    var $shippinginfo;
    var $customcheckoutfields = array();

    function __construct( $id ) {
        $this->purchlogid = $id;
        $this->get_purchlog_details();
    }
    function shippingstate( $id ) {
        global $wpdb;
        if ( is_numeric( $id ) ) {
            $sql = "SELECT `name` FROM `" . WPSC_TABLE_REGION_TAX . "` WHERE id=" . $id;
            $name = $wpdb->get_var( $sql );
            return $name;
        }
        else {
            return $id;
        }
    }
    function get_purchlog_details() {
        global $wpdb;

        $cartcontent = $wpdb->get_results( "SELECT *  FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid`=" . $this->purchlogid . "" );

        //echo $cartsql;
        $this->allcartcontent = $cartcontent;

        //exit('<pre>'.print_r($cartcontent, true).'</pre>');
        $sql = "SELECT DISTINCT `" . WPSC_TABLE_PURCHASE_LOGS . "` . * FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` LEFT JOIN `" . WPSC_TABLE_PURCHASE_LOGS . "` ON `" . WPSC_TABLE_SUBMITED_FORM_DATA . "`.`log_id` = `" . WPSC_TABLE_PURCHASE_LOGS . "`.`id` WHERE `" . WPSC_TABLE_PURCHASE_LOGS . "`.`id`=" . $this->purchlogid;
        $extrainfo = $wpdb->get_results( $sql );

        // Avoid undefined array key warning
        if (!empty($extrainfo) && isset($extrainfo[0])) {
            $this->extrainfo = $extrainfo[0];
        } else {
            $this->extrainfo = null;
        }

        $usersql = "SELECT `" . WPSC_TABLE_SUBMITED_FORM_DATA . "`.`value`, `" . WPSC_TABLE_CHECKOUT_FORMS . "`.`name`, `" . WPSC_TABLE_CHECKOUT_FORMS . "`.`unique_name` FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` LEFT JOIN `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` ON `" . WPSC_TABLE_CHECKOUT_FORMS . "`.id = `" . WPSC_TABLE_SUBMITED_FORM_DATA . "`.`form_id` WHERE `" . WPSC_TABLE_SUBMITED_FORM_DATA . "`.`log_id`=" . $this->purchlogid . " ORDER BY `" . WPSC_TABLE_CHECKOUT_FORMS . "`.`order`";

        //exit($usersql);
        $userinfo = $wpdb->get_results( $usersql, ARRAY_A );

        //exit('<pre>'.print_r($userinfo, true).'</pre>');
        $shippinginfo = array();
        $billingdetails = array();
        $additionaldetails = array();
        foreach ( (array)$userinfo as $input_row ) {
            if ( stristr( $input_row['unique_name'], 'shipping' ) ) {
                $shippinginfo[$input_row['unique_name']] = $input_row;
            }
            elseif ( stristr( $input_row['unique_name'], 'billing' ) ) {
                $billingdetails[$input_row['unique_name']] = $input_row;
            }
            else {
                $additionaldetails[$input_row['name']] = $input_row;
            }
        }
        $this->userinfo = $billingdetails;
        $this->shippinginfo = $shippinginfo;
        $this->customcheckoutfields = $additionaldetails;
        $this->purch_item_count = count( $cartcontent );

        //    exit('<pre>'.print_r($cartcontent, true).'</pre>');


    }

    function next_purch_item() {
        $this->currentitem++;
        $this->purchitem = $this->allcartcontent[$this->currentitem];
        return $this->purchitem;
    }

    function the_purch_item() {
        $this->purchitem = $this->next_purch_item();

        //if ( $this->currentitem == 0 ) // loop has just started


    }

    function have_purch_item() {
        if ( $this->currentitem + 1 < $this->purch_item_count ) {
            return true;
        }
        else if ( $this->currentitem + 1 == $this->purch_item_count && $this->purch_item_count > 0 ) {

                // Do some cleaning up after the loop,
                $this->rewind_purch_item();
            }
        return false;
    }

    function rewind_purch_item() {
        $this->currentitem = - 1;
        if ( $this->purch_item_count > 0 ) {
            $this->purchitem = $this->allcartcontent[0];
        }
    }
    function have_downloads_locked() {
        global $wpdb;
        $sql = "SELECT `ip_number` FROM `" . WPSC_TABLE_DOWNLOAD_STATUS . "` WHERE purchid=" . $this->purchlogid;
        $ip_number = $wpdb->get_var( $sql );
        return $ip_number;
    }
}

