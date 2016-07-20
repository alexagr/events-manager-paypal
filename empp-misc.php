<?php

class EM_Paypal_Misc {
    public static function init() {
        add_filter('em_booking_form_tickets_cols', array(__CLASS__, 'em_booking_form_tickets_cols'), 10, 2);
        add_filter('em_booking_get_spaces', array(__CLASS__, 'em_booking_get_spaces'), 10, 2);
        add_filter('em_booking_get_person', array(__CLASS__, 'em_booking_get_person'), 10, 2);
        add_filter('manage_event_posts_columns', array(__CLASS__, 'show_edit_columns'), 11);
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
}

EM_Paypal_Misc::init();