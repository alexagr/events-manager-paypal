<?php

class EM_Paypal_IPN {

    public static function init() {
        add_action('paypal_ipn_for_wordpress_payment_status_completed', array(__CLASS__, 'ipn_response'), 10, 1); 
        add_action('paypal_ipn_for_wordpress_payment_status_processed', array(__CLASS__, 'ipn_response'), 10, 1); 
        add_action('paypal_ipn_for_wordpress_payment_status_reversed',  array(__CLASS__, 'ipn_response'), 10, 1); 
        add_action('paypal_ipn_for_wordpress_payment_status_denied',    array(__CLASS__, 'ipn_response'), 10, 1); 
        add_action('paypal_ipn_for_wordpress_payment_status_refunded',  array(__CLASS__, 'ipn_response'), 10, 1); 
    }

    // record transaction in Event Manager Pro table
    public static function record_transaction($EM_Booking, $amount, $currency, $timestamp, $txn_id, $status) {
        if (!defined('EMP_VERSION')) {
            return;
        }

        global $wpdb;
        if( EM_MS_GLOBAL ){
            $prefix = $wpdb->base_prefix;
        }else{
            $prefix = $wpdb->prefix;
        }
        $table_transaction = $prefix.'em_transactions';
        
        $data = array();
        $data['booking_id'] = $EM_Booking->booking_id;
        $data['transaction_gateway_id'] = $txn_id;
        $data['transaction_timestamp'] = $timestamp;
        $data['transaction_currency'] = $currency;
        $data['transaction_status'] = $status;
        $data['transaction_total_amount'] = $amount;
        $data['transaction_note'] = '';
        $data['transaction_gateway'] = 'paypal';
        
        if( !empty($txn_id) ){
            $existing = $wpdb->get_row( $wpdb->prepare( "SELECT transaction_id, transaction_status, transaction_gateway_id, transaction_total_amount FROM ".$table_transaction." WHERE transaction_gateway = %s AND transaction_gateway_id = %s", 'paypal', $txn_id ) );
        }
        
        if( !empty($existing->transaction_gateway_id) && $amount == $existing->transaction_total_amount && $status != $existing->transaction_status ) {
            // Update only if txn id and amounts are the same (e.g. pending payments changing status)
            $wpdb->update( $table_transaction, $data, array('transaction_id' => $existing->transaction_id) );
        } else {
            // Insert
            $wpdb->insert( $table_transaction, $data );
        }
    }

    public static function ipn_response( $posted ) {
        $amount = isset($posted['mc_gross']) ? $posted['mc_gross'] : '';
        $currency = isset($posted['mc_currency']) ? $posted['mc_currency'] : '';
        $timestamp = isset($posted['payment_date']) ? date('Y-m-d H:i:s', strtotime($posted['payment_date'])) : '';
        $txn_id = isset($posted['txn_id']) ? $posted['txn_id'] : ''; 
        $status = isset($posted['payment_status']) ? $posted['payment_status'] : ''; 
        $invoice = isset($posted['invoice']) ? $posted['invoice'] : ''; 

        $invoice_values = explode('#',$invoice);
        $booking_id = -1;
        if ($invoice_values[0] == 'EM-BOOKING') {
            if (!empty($invoice_values[1])) {
                $booking_id = $invoice_values[1];
            } 
        }
        // EM_Pro::log('ipn_response invoice='.$invoice.' txn_id='.$txn_id.' status='.$status.' amount='.$amount.' booking_id='.$booking_id, 'paypal');
        if ($booking_id < 0) {
            return;
        }
        
        $EM_Booking = em_get_booking($booking_id);
        if (!empty($EM_Booking->booking_id)) {
            //booking exists
            $EM_Booking->manage_override = true; //since we're overriding the booking ourselves.

            $price = $EM_Booking->get_price();
            $price = floor($price);
            
            switch ($status) {
                case 'Completed':
                case 'Processed':
                    self::record_transaction($EM_Booking, $amount, $currency, $timestamp, $txn_id, $status);
                    if ($amount >= $price) {
                        $EM_Booking->set_status(1); // approve
                    } else {
                        $EM_Booking->set_status(0, false); // pending
                    }
                    break;

                case 'Reversed':
                case 'Denied':
                    self::record_transaction($EM_Booking, $amount, $currency, $timestamp, $txn_id, $status);
                    $EM_Booking->cancel();
                    break;

                case 'Refunded':
                    self::record_transaction($EM_Booking, $amount, $currency, $timestamp, $txn_id, $status);
                    if ($price >= $amount) {
                        $EM_Booking->cancel();
                    } else {
                        $EM_Booking->set_status(0, false); // pending
                    }
                    break;

                default:
            }
        }
    }
}

EM_Paypal_IPN::init();