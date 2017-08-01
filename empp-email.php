<?php
class EM_Paypal_Emails {

    public static function init() {
        add_filter('em_booking_email_messages', array(__CLASS__, 'email_messages'), 100, 2);
        add_filter('em_booking_output_placeholder',array(__CLASS__,'placeholders'),2,3);
        add_action('init',array(__CLASS__,'redirect'), 1);
    }

    public static function email_messages( $msg, $EM_Booking ) {
        if ($EM_Booking->booking_status == 5) {
            $msg['user']['subject'] = get_option('dbem_bookings_email_awaiting_payment_subject');
            $msg['user']['body'] = get_option('dbem_bookings_email_awaiting_payment_body');
            //admins should get something (if set to)
            $msg['admin']['subject'] = get_option('dbem_bookings_contact_email_awaiting_payment_subject');
            $msg['admin']['body'] = get_option('dbem_bookings_contact_email_awaiting_payment_body');
        }
        if ($EM_Booking->booking_status == 6) {
            $msg['user']['subject'] = get_option('dbem_bookings_email_waiting_list_subject');
            $msg['user']['body'] = get_option('dbem_bookings_email_waiting_list_body');
            //admins should get something (if set to)
            $msg['admin']['subject'] = get_option('dbem_bookings_contact_email_waiting_list_subject');
            $msg['admin']['body'] = get_option('dbem_bookings_contact_email_waiting_list_body');
        }
        return $msg;
    }

    public static function booking_details($EM_Booking, $full_result, $lang) {
        $replace = '';
        
        $tickets = array();
        foreach($EM_Booking->get_tickets_bookings() as $EM_Ticket_Booking) {
            if (($EM_Ticket_Booking->get_price() >= 0) && ($EM_Ticket_Booking->get_price() < 1)) {
                $tickets[$EM_Ticket_Booking->get_price() * 1000] = $EM_Ticket_Booking;
            }
        }
        krsort($tickets);
        foreach($tickets as $price => $ticket) {
            $replace = $replace . apply_filters('translate_text', $ticket->get_ticket()->name, $lang) . " : " . $ticket->get_spaces() . "\n";
            if (!empty($EM_Booking->booking_meta['attendees'][$ticket->ticket_id]) && is_array($EM_Booking->booking_meta['attendees'][$EM_Ticket_Booking->ticket_id])) {
                $i = 1; //counter
                $EM_Form = EM_Attendees_Form::get_form($EM_Booking->event_id);
                foreach ($EM_Booking->booking_meta['attendees'][$ticket->ticket_id] as $field_values) {
                    $replace = $replace . "&nbsp;&nbsp;&nbsp;&nbsp;#" . $i . "\n";
                    foreach ($EM_Form->form_fields as $fieldid => $field) {
                        if (!array_key_exists($fieldid, $EM_Form->user_fields) && $field['type'] != 'html') {
                            if (isset($field_values[$fieldid])) {
                                $replace = $replace . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . apply_filters('translate_text', $field['label'], $lang) . " : " . apply_filters('translate_text', $field_values[$fieldid], $lang) . "\n";
                            }
                        }
                    }
                    $i++;
                }
            }
        }
        if ($lang == 'ru') {
            $replace = $replace . "E-mail : " . $EM_Booking->get_person()->user_email . "\n";
            $replace = $replace . "Телефон : " . $EM_Booking->get_person()->phone . "\n";
        } else {
            $replace = $replace . "דוא&quot;ל : " . $EM_Booking->get_person()->user_email . "\n";
            $replace = $replace . "טלפון : " . $EM_Booking->get_person()->phone . "\n";
        }
        if (!empty($EM_Booking->booking_meta['registration']['dbem_address'])) {
            if ($lang == 'ru') {
                $replace = $replace . "Адрес : " . $EM_Booking->booking_meta['registration']['dbem_address'] . "\n";
            } else {
                $replace = $replace . "כתובת : " . $EM_Booking->booking_meta['registration']['dbem_address'] . "\n";
            }
        }
        if (!empty($EM_Booking->booking_meta['registration']['dbem_city'])) {
            if ($lang == 'ru') {
                $replace = $replace . "Город : " . $EM_Booking->booking_meta['registration']['dbem_city'] . "\n";
            } else {
                $replace = $replace . "עיר : " . $EM_Booking->booking_meta['registration']['dbem_city'] . "\n";
            }
        }
        if (!empty($EM_Booking->booking_meta['booking'])) {
            $EM_Form = EM_Booking_Form::get_form($EM_Booking->event_id);
            foreach ($EM_Form->form_fields as $fieldid => $field) {
                if (($field['type'] != 'html') && isset($EM_Booking->booking_meta['booking'][$fieldid])) {
                    $replace = $replace . apply_filters('translate_text', $field['label'], $lang) . " : " . apply_filters('translate_text', $EM_Booking->booking_meta['booking'][$fieldid], $lang) . "\n";
                }                        
            }
        }
        return $replace;
    }
    
    public static function paypal_details($EM_Booking, $full_result, $lang) {
        $discount = $EM_Booking->get_price_discounts_amount('post');

        if ($lang == 'ru') {
            $replace = "<u>УЧАСТНИКИ</u>\n\n";
        } else {
            $replace = "<u>משתתפים</u>\n\n";
        }
        $participants = array();
        foreach($EM_Booking->get_tickets_bookings() as $EM_Ticket_Booking) {
            if (($EM_Ticket_Booking->get_price() >= 0) && ($EM_Ticket_Booking->get_price() < 1)) {
                $participants[$EM_Ticket_Booking->get_price() * 1000] = apply_filters('translate_text', $EM_Ticket_Booking->get_ticket()->name, $lang) . " : " . $EM_Ticket_Booking->get_spaces() . "\n"; 
            }
        }
        krsort($participants);
        foreach ($participants as $price => $descr) {
            $replace = $replace . $descr;
        }

        if ($EM_Booking->get_price() > 1) {
            $replace = $replace . "\n\n";
            if ($lang == 'ru') {
                $replace = $replace . "<u>СТОИМОСТЬ УЧАСТИЯ</u>\n\n";
            } else {
                $replace = $replace . "<u>מחיר השתתפות</u>\n\n";
            }
            foreach($EM_Booking->get_tickets_bookings() as $EM_Ticket_Booking) {
                if ($EM_Ticket_Booking->get_price() >= 1) {
                    $replace = $replace . apply_filters('translate_text', $EM_Ticket_Booking->get_ticket()->name, $lang) . "\n";
                    // $replace = $replace . apply_filters('translate_text', $EM_Ticket_Booking->get_ticket()->ticket_description, $lang) . "\n";
                    if ($lang == 'ru') {
                        $replace = $replace . "&nbsp;&nbsp;&nbsp;&nbsp;Количество : " . $EM_Ticket_Booking->get_spaces() . "\n";
                    } else {
                        $replace = $replace . "&nbsp;&nbsp;&nbsp;&nbsp;כמות : " . $EM_Ticket_Booking->get_spaces() . "\n";
                    }
                    $price = $EM_Ticket_Booking->get_price();
                    $price = floor($price);
                    // $price = $EM_Ticket_Booking->format_price($discount);                
                    if ($lang == 'ru') {
                        $replace = $replace . "&nbsp;&nbsp;&nbsp;&nbsp;Стоимость : " . $price . " &#8362;\n\n";
                    } else {
                        $replace = $replace . "&nbsp;&nbsp;&nbsp;&nbsp;מחיר : " . $price . " &#8362;\n\n";
                    }
                }
                if ($EM_Ticket_Booking->get_price() < 0) {
                    $discount += -$EM_Ticket_Booking->get_price();
                }
            }
            if ($discount > 0) {
                // $price = $EM_Ticket_Booking->format_price($discount);                
                $replace = $replace . "--------------\n";
                if ($lang == 'ru') {
                    $replace = $replace . "Скидка : -" . $discount . " &#8362;\n\n";
                } else {
                    $replace = $replace . "הנחה : -" . $discount . " &#8362;\n\n";
                }
            }
            $replace = $replace . "--------------\n";
            $price = $EM_Booking->get_price();
            $price = floor($price);
            // $price = $EM_Booking->format_price($price);                
            if ($lang == 'ru') {
                $replace = $replace . "Итого : " . $price . " &#8362;\n\n";
            } else {
                $replace = $replace . "סה&quot;כ : " . $price . " &#8362;\n\n";
            }
        }
        return $replace;
    }
    
    public static function placeholders($replace, $EM_Booking, $full_result){
        if (empty($replace) || $replace == $full_result) {
            if ($full_result == '#_PAYPAL') {
                $location = get_option('dbem_payment_page') . '?';
                $redirect_vars = array(
                    'paypal_redirect' => 1,
                    'booking_id' => $EM_Booking->booking_id
                ); 
                $replace = $location.http_build_query($redirect_vars);
            }
            
            if ($full_result == '#_BOOKINGDETAILSRU') {
                $replace = EM_Paypal_Emails::booking_details($EM_Booking, $full_result, 'ru');
            }

            if ($full_result == '#_BOOKINGDETAILSHE') {
                $replace = EM_Paypal_Emails::booking_details($EM_Booking, $full_result, 'he');
            }
            
            if ($full_result == '#_BOOKINGPRICEPAYPAL') {
                $replace = '';
                $price = $EM_Booking->get_price();
                $price = floor($price);
                $replace = $EM_Booking->format_price($price);                
            }

            if ($full_result == '#_BOOKINGSUMMARYPAYPALRU') {
                $replace = EM_Paypal_Emails::paypal_details($EM_Booking, $full_result, 'ru');
            }

            if ($full_result == '#_BOOKINGSUMMARYPAYPALHE') {
                $replace = EM_Paypal_Emails::paypal_details($EM_Booking, $full_result, 'he');
            }
        }
        return $replace;
    }   

    public static function redirect() {
        if (isset($_GET['paypal_redirect']) && isset($_GET['booking_id'])) {
            $EM_Booking = em_get_booking($_GET['booking_id']);
            if ($EM_Booking->booking_status == 5) {
                $location = 'https://en.wiktionary.org/wiki/free_as_in_beer';
                if ($EM_Booking->get_price_post_taxes() > 0) {            
                    // construct PayPal link            
                    $paypal_email = get_option('dbem_paypal_email');
                    $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?';
                    if (get_option('dbem_paypal_status') == 'test') {
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
                    
                    // $paypal_vars['email'] = $EM_Booking->get_person()->user_email;             
                    $paypal_vars['first_name'] = $EM_Booking->get_person()->first_name;
                    $paypal_vars['last_name'] = $EM_Booking->get_person()->last_name;
                    
                    $discount = $EM_Booking->get_price_discounts_amount('post');
                    $count = 1;
                    foreach( $EM_Booking->get_tickets_bookings()->tickets_bookings as $EM_Ticket_Booking ) {
                        // divide price by spaces for per-ticket price
                        // we divide this way rather than by $EM_Ticket because that can be changed by user in future, yet $EM_Ticket_Booking will change if booking itself is saved.             
                        $price = $EM_Ticket_Booking->get_price() / $EM_Ticket_Booking->get_spaces();
                        if (($price > 0) && ($price < 1)) {
                            $price = 0;
                        }
                        if ($price > 0) {
                            $name = $EM_Ticket_Booking->get_ticket()->name;
                            /*
                            if (!empty($EM_Ticket_Booking->get_ticket()->ticket_description)) {
                                $name = $name . " | " . $EM_Ticket_Booking->get_ticket()->ticket_description; 
                            }
                            */ 
                            $paypal_vars['item_name_'.$count] = wp_kses_data($name);
                            $paypal_vars['quantity_'.$count] = $EM_Ticket_Booking->get_spaces();
                            $paypal_vars['amount_'.$count] = round($price,2);
                            $count++;
                        }
                        if ($price < 0) {
                          $discount += -$price * $EM_Ticket_Booking->get_spaces();
                        }
                    }
                    if( $discount > 0 ){
                        $paypal_vars['discount_amount_cart'] = $discount;
                    } 
                    
                    $location = $paypal_url.http_build_query($paypal_vars);
                }
                header ('HTTP/1.1 302 Found');
                header ('Location: ' . $location);
                exit();
            }
            
            if ($EM_Booking->booking_status == 1) {
                $location = get_option('dbem_payment_already_done_page');
                header ('HTTP/1.1 302 Found');
                header ('Location: ' . $location);
                exit();
            }
        }
    }
}

EM_Paypal_Emails::init();