<?php

class EM_Paypal_Misc {
    public static function init() {
        add_filter('em_booking_form_tickets_cols', array(__CLASS__, 'em_booking_form_tickets_cols'), 10, 2);
        add_filter('em_booking_get_spaces', array(__CLASS__, 'em_booking_get_spaces'), 10, 2);
        add_filter('em_booking_get_person', array(__CLASS__, 'em_booking_get_person'), 10, 2);
        add_filter('manage_event_posts_columns', array(__CLASS__, 'show_edit_columns'), 11);
        add_filter('em_bookings_table_cols_col_action', array(__CLASS__, 'em_bookings_table_cols_col_action'), 10, 2);
        add_action('em_booking', array(__CLASS__, 'em_booking'), 10, 2);
        add_filter('em_booking_set_status', array(__CLASS__, 'em_booking_set_status'), 10, 2);
        add_action('empp_hourly_hook', array(__CLASS__, 'empp_hourly_hook'));
    }

    public static function em_booking_form_tickets_cols($collumns, $EM_Event) { 
        if (get_option('dbem_show_ticket_price') == 'hide') {
            unset($collumns['price']);
        }
        $tickets = get_option('dbem_tickets_name');
        if (!empty($tickets)) {
            $collumns['type'] = $tickets;
        }
        $spaces = get_option('dbem_spaces_name');
        if (!empty($spaces)) {
            $collumns['spaces'] = $spaces;
        }
        return $collumns;
    }
    
    public static function em_booking_get_spaces($spaces, $obj) {
        if (get_option('dbem_show_admin_tickets') == 'ignore') {
            if (get_class($obj) == 'EM_Tickets') {
                $spaces = 0;
                foreach( $obj->tickets as $EM_Ticket ){
                    if (!$EM_Ticket->ticket_members && ($EM_Ticket->ticket_price > 0)) {
                        $spaces += $EM_Ticket->get_spaces();
                    }
                }
            }
            if (get_class($obj) == 'EM_Tickets_Bookings') {
                $spaces = 0;
                foreach( $obj->tickets_bookings as $EM_Ticket_Booking) {
                    if (!$EM_Ticket_Booking->get_ticket()->ticket_members && ($EM_Ticket_Booking->get_ticket()->ticket_price > 0)) {
                        $spaces += $EM_Ticket_Booking->get_spaces();
                    }
                }
            }
            if (get_class($obj) == 'EM_Booking') {
                $spaces = $obj->get_tickets_bookings()->get_spaces();
            }
        }
        return $spaces;
    }

    public static function em_booking_get_person($EM_Person, $EM_Booking) {
        if (($EM_Person->display_name == 'Guest User') && isset($EM_Booking->booking_meta['attendees'])) {
            foreach ($EM_Booking->get_tickets_bookings()->tickets_bookings as $EM_Ticket_Booking ) {
                if (isset($EM_Booking->booking_meta['attendees'][$EM_Ticket_Booking->ticket_id][0]) &&
                    isset($EM_Booking->booking_meta['attendees'][$EM_Ticket_Booking->ticket_id][0]['attendee_first_name'])) {
                    $EM_Person->first_name = $EM_Booking->booking_meta['attendees'][$EM_Ticket_Booking->ticket_id][0]['attendee_first_name'];
                    $EM_Person->last_name = $EM_Booking->booking_meta['attendees'][$EM_Ticket_Booking->ticket_id][0]['attendee_last_name'];
                    $EM_Person->display_name = $EM_Person->first_name . " " . $EM_Person->last_name;
                    break;
                }
            }
        }
        return $EM_Person;
    }
    
    public static function show_edit_columns($columns) { 
        if (get_option('dbem_show_event_details') == 'hide') {
            unset($columns['place']);
            unset($columns['description']);
            unset($columns['event_repeating']);
        }
        return $columns;
    }
    
    public static function em_bookings_table_cols_col_action($booking_actions, $EM_Booking) {
        if ($EM_Booking->booking_status == 6) {
			$booking_actions = array(
				'approve' => '<a class="em-bookings-approve" href="'.em_add_get_params($url, array('action'=>'bookings_approve', 'booking_id'=>$EM_Booking->booking_id)).'">'.__('Approve','events-manager').'</a>',
				'reject' => '<a class="em-bookings-reject" href="'.em_add_get_params($url, array('action'=>'bookings_reject', 'booking_id'=>$EM_Booking->booking_id)).'">'.__('Reject','events-manager').'</a>',
				'delete' => '<span class="trash"><a class="em-bookings-delete" href="'.em_add_get_params($url, array('action'=>'bookings_delete', 'booking_id'=>$EM_Booking->booking_id)).'">'.__('Delete','events-manager').'</a></span>',
				'edit' => '<a class="em-bookings-edit" href="'.em_add_get_params($EM_Booking->get_event()->get_bookings_url(), array('booking_id'=>$EM_Booking->booking_id, 'em_ajax'=>null, 'em_obj'=>null)).'">'.__('Edit/View','events-manager').'</a>',
			);
        }
        if (get_option('dbem_admin_actions') == 'edit') {
            unset($booking_actions['approve']);
            unset($booking_actions['reject']);
            unset($booking_actions['unapprove']);
        }
        return $booking_actions; 
    } 

    public static function em_booking($EM_Booking, $booking_data) {
        if (get_option('dbem_hide_online_payment') == 'hide') {
            unset($EM_Booking->status_array[4]);
        }
        $EM_Booking->status_array[6] = 'Waiting List';         
    }
    
    public static function em_booking_set_status($result, $EM_Booking) {
        if ($EM_Booking->booking_status == 5) {
            $EM_Booking->add_note('Awaiting Payment');
        }
        if ($EM_Booking->booking_status == 1) {
            $EM_Booking->add_note('Approved');
        }
        if ($EM_Booking->booking_status == 2) {
            $EM_Booking->add_note('Rejected');
        }
        if ($EM_Booking->booking_status == 6) {
            $EM_Booking->add_note('Waiting List');
        }
        return $result;
    }
    
    public static function empp_hourly_hook() {
        $diffdays = intval(get_option('dbem_days_for_payment', '0'));
        if ($diffdays == 0) {
            return;
        }
    
        $events = EM_Events::get(array('scope'=>'future'));
        foreach ($events as $EM_Event) {
            foreach ($EM_Event->get_bookings()->bookings as $EM_Booking) {
                if ($EM_Booking->booking_status == 5) {
                    $date1 = date_create();
                    $date2 = date_create();
                    foreach ($EM_Booking->get_notes() as $note) {
                        if ($note['note'] == 'Awaiting Payment') {
                            date_timestamp_set($date1, $note['timestamp']);
                        }
                    }
                    $weekday = intval(date_format($date1, "w"));
                    if ($weekday > (4 - $diffdays)) {
                        $diffdays += 2;
                    } 
                    $diff = date_diff($date1, $date2);
                    if ($diff->d > $diffdays) {
                        $EM_Booking->set_status(6);
                    }
                }
            }
        }
    }

}

EM_Paypal_Misc::init();