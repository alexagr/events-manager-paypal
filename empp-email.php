<?php
class EM_Paypal_Emails {

    public static function init() {
        add_filter('em_booking_email_messages', array(__CLASS__, 'email_messages'), 100, 2);
        add_filter('em_booking_output_placeholder',array(__CLASS__,'placeholders'),2,3);
    }

    public static function email_messages( $msg, $EM_Booking ) {
        if ($EM_Booking->booking_status == 5) {
            $msg['user']['subject'] = get_option('dbem_bookings_email_awaiting_payment_subject');
            $msg['user']['body'] = get_option('dbem_bookings_email_awaiting_payment_body');
            //admins should get something (if set to)
            $msg['admin']['subject'] = get_option('dbem_bookings_contact_email_awaiting_payment_subject');
            $msg['admin']['body'] = get_option('dbem_bookings_contact_email_awaiting_payment_body');
        }
        return $msg;
    }
    
    public static function placeholders($replace, $EM_Booking, $full_result){
        if (empty($replace) || $replace == $full_result) {
            if ($full_result == '#_PAYPAL') {
                if ($EM_Booking->get_price_post_taxes() > 0) {            
                    // construct PayPal link            
                    $paypal_email = get_option('dbem_paypail_email');
                    $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?';
                    if (get_option('dbem_paypail_status') == 'test') {
                        $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
                    }
        
                    $paypal_vars = array(
                        'business' => $paypal_email, 
                        'cmd' => '_cart',
                        'upload' => 1,
                        'currency_code' => get_option('dbem_bookings_currency', 'USD'),
                        'invoice' => 'EM-BOOKING#' . $EM_Booking->booking_id,                 
                        'custom' => $EM_Booking->booking_id . ':' . $EM_Booking->event_id,
                        'charset' => 'UTF-8',
                        'bn'=>'NetWebLogic_SP'
                    );
                    
                    $paypal_vars['email'] = $EM_Booking->get_person()->user_email;             
                    $paypal_vars['first_name'] = $EM_Booking->get_person()->first_name;
                    $paypal_vars['last_name'] = $EM_Booking->get_person()->last_name;
                    
                    $discount = $EM_Booking->get_price_discounts_amount('post');
                    $count = 1;
                    foreach( $EM_Booking->get_tickets_bookings()->tickets_bookings as $EM_Ticket_Booking ) {
                        // divide price by spaces for per-ticket price
                        // we divide this way rather than by $EM_Ticket because that can be changed by user in future, yet $EM_Ticket_Booking will change if booking itself is saved.             
                        $price = $EM_Ticket_Booking->get_price() / $EM_Ticket_Booking->get_spaces();
                        if( $price > 0 ){
                            $name = $EM_Ticket_Booking->get_ticket()->name;
                            if (!empty($EM_Ticket_Booking->get_ticket()->ticket_description)) {
                                $name = $name . " | " . $EM_Ticket_Booking->get_ticket()->ticket_description; 
                            } 
                            $paypal_vars['item_name_'.$count] = wp_kses_data($name);
                            $paypal_vars['quantity_'.$count] = $EM_Ticket_Booking->get_spaces();
                            $paypal_vars['amount_'.$count] = round($price,2);
                            $count++;
                        }
                        if( $price < 0 ){
                          $discount += -$price * $EM_Ticket_Booking->get_spaces();
                        }
                    }
                    if( $discount > 0 ){
                        $paypal_vars['discount_amount_cart'] = $discount;
                    } 
                    
                    $replace = $paypal_url.http_build_query($paypal_vars);
                }
                else {
                    $replace = 'https://en.wiktionary.org/wiki/free_as_in_beer';
                }
            }
            
            if ($full_result == '#_BOOKINGDETAILS') {
                $replace = '';
                foreach($EM_Booking->get_tickets_bookings() as $EM_Ticket_Booking) {
                    $replace = $replace . $EM_Ticket_Booking->get_ticket()->name . " : " . $EM_Ticket_Booking->get_spaces() . "\n";
                    if (!empty($EM_Booking->booking_meta['attendees'][$EM_Ticket_Booking->ticket_id]) && is_array($EM_Booking->booking_meta['attendees'][$EM_Ticket_Booking->ticket_id])) {
                        $i = 1; //counter
                    	$EM_Form = EM_Attendees_Form::get_form($EM_Booking->event_id);
                        foreach ($EM_Booking->booking_meta['attendees'][$EM_Ticket_Booking->ticket_id] as $field_values) {
                            $replace = $replace . "&nbsp;&nbsp;&nbsp;&nbsp;#" . $i . "\n";
            	    		foreach ($EM_Form->form_fields as $fieldid => $field) {
            	    			if (!array_key_exists($fieldid, $EM_Form->user_fields) && $field['type'] != 'html') {
                                    if (isset($field_values[$fieldid])) {
                                        $replace = $replace . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $field['label'] . " : " . $field_values[$fieldid] . "\n";
                                    }
                                }
                            }
                            $i++;
                        }
                    }
                }
                $replace = $replace . "E-mail : " . $EM_Booking->get_person()->user_email . "\n";
                $replace = $replace . "Phone : " . $EM_Booking->get_person()->phone . "\n";
                if (!empty($EM_Booking->booking_meta['booking'])) {
                	$EM_Form = EM_Booking_Form::get_form($EM_Booking->event_id);
            		foreach ($EM_Form->form_fields as $fieldid => $field) {
                        if (isset($EM_Booking->booking_meta['booking'][$fieldid])) {
                            $replace = $replace . $field['label'] . " : " . $EM_Booking->booking_meta['booking'][$fieldid] . "\n";
                        }                        
                    }
                }
            }
        }
        return $replace;
    }

}

EM_Paypal_Emails::init();