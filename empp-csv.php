<?php

/**
 * Limmud-specific export to CSV
 */

class EM_Paypal_CSV {
    public static function init() {
        add_action('init', array(__CLASS__, 'intercept_csv_export'), 10); 
        add_action('em_bookings_table_export_options', array(__CLASS__, 'em_bookings_table_export_options'), 11);
    }

    public static function em_bookings_table_export_options() {
        ?>
        <p>Limmud 2016 export <input type="checkbox" name="limmud_export" value="1" />
        <a href="#" title="Limmud 2016 specific export format">?</a>
        <?php
    }

    public static function intercept_csv_export() {
        if( !empty($_REQUEST['action']) && $_REQUEST['action'] == 'export_bookings_csv' && !empty($_REQUEST['limmud_export']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'export_bookings_csv')) {

            header("Content-Type: application/octet-stream; charset=utf-8");
            header("Content-Disposition: Attachment; filename=".sanitize_title(get_bloginfo())."-bookings-export.csv");
            do_action('em_csv_header_output');
            echo "\xEF\xBB\xBF"; // UTF-8 for MS Excel (a little hacky... but does the job)

            $handle = fopen("php://output", "w");
            $delimiter = !defined('EM_CSV_DELIMITER') ? ',' : EM_CSV_DELIMITER;
            $delimiter = apply_filters('em_csv_delimiter', $delimiter);

            $headers = array('id', 'name', 'email', 'status', 'event_name', 'ticket_name', 'ticket_price', 'phone', 'address', 'city', 'participant_role', 'accomodation_type', 'room_type', 'room_mate', 'shabbat_area', 'bus_needed', 'discount_student', 'dbem_comment', 'first_name', 'last_name', 'birthday', 'israeli', 'passport');
            fputcsv($handle, $headers, $delimiter);


            $events = EM_Events::get(array('scope'=>'future'));
            foreach ($events as $EM_Event) {
                if (($EM_Event->event_name != 'Limmud 2016 Registration') && ($EM_Event->event_name != 'Limmud 2016 Private Registration') && ($EM_Event->event_name != 'Limmud 2016 Self-Accomodation')) {
                    continue;
                }
                foreach ($EM_Event->get_bookings()->bookings as $EM_Booking) {
                    $attendees_data = EM_Attendees_Form::get_booking_attendees($EM_Booking);
                    foreach($EM_Booking->get_tickets_bookings()->tickets_bookings as $EM_Ticket_Booking) {
                        $row = array();
                        $row[] = $EM_Booking->booking_id;
                        $row[] = $EM_Booking->get_person()->get_name();
                        $row[] = $EM_Booking->get_person()->user_email;
                        $row[] = $EM_Booking->get_status(true);
                        $row[] = $EM_Booking->get_event()->event_name;
                        $row[] = $EM_Ticket_Booking->get_ticket()->ticket_name;
                        $row[] = $EM_Ticket_Booking->get_ticket()->get_price(true);
                        $row[] = $EM_Booking->get_person()->phone;
                        $row[] = $EM_Booking->booking_meta['registration']['dbem_address'];
                        $row[] = $EM_Booking->booking_meta['registration']['dbem_city'];

                        $event_id = $EM_Booking->get_event()->event_id;
                        $EM_Form = EM_Booking_Form::get_form($event_id, $EM_Booking);
                        $row[] = $EM_Form->get_formatted_value($EM_Form->form_fields['participant_role'], $EM_Booking->booking_meta['booking']['participant_role']);
                        $row[] = $EM_Form->get_formatted_value($EM_Form->form_fields['accomodation_type'], $EM_Booking->booking_meta['booking']['accomodation_type']);
                        if ($EM_Event->event_name == 'Limmud 2016 Self-Accomodation') {
                            $row[] = '';
                            $row[] = '';
                            $row[] = '';
                            $row[] = '';
                            $row[] = '';
                        } else {
                            $row[] = $EM_Form->get_formatted_value($EM_Form->form_fields['room_type'], $EM_Booking->booking_meta['booking']['room_type']);
                            $row[] = $EM_Form->get_formatted_value($EM_Form->form_fields['room_mate'], $EM_Booking->booking_meta['booking']['room_mate']);
                            $row[] = $EM_Form->get_formatted_value($EM_Form->form_fields['shabbat_area'], $EM_Booking->booking_meta['booking']['shabbat_area']);
                            $row[] = $EM_Form->get_formatted_value($EM_Form->form_fields['bus_needed'], $EM_Booking->booking_meta['booking']['bus_needed']);
                            $row[] = $EM_Form->get_formatted_value($EM_Form->form_fields['discount_student'], $EM_Booking->booking_meta['booking']['discount_student']);
                        }
                        $row[] = $EM_Form->get_formatted_value($EM_Form->form_fields['dbem_comment'], $EM_Booking->booking_meta['booking']['dbem_comment']);
                         
                        if (!empty($attendees_data[$EM_Ticket_Booking->ticket_id])) {
                            foreach($attendees_data[$EM_Ticket_Booking->ticket_id] as $attendee_title => $attendee_data) {
                                $full_row = $row;
                                foreach( $attendee_data as $field_value) {
                                    $full_row[] = $field_value;
                                }
                                fputcsv($handle, $full_row, $delimiter);
                            }
                        }
                    }
                }
            }
            fclose($handle);
            exit();
        }
    }
}

EM_Paypal_CSV::init();
