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
        <p>Limmud 2016 accomodation <input type="checkbox" name="limmud_accomodation" value="1" />
        <a href="#" title="Limmud 2016 accomodation report">?</a>
        <p>Limmud 2016 transport <input type="checkbox" name="limmud_transport" value="1" />
        <a href="#" title="Limmud 2016 transport report">?</a>
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
                                    if ($field_value != 'n/a') {
                                        $full_row[] = $field_value;
                                    } else {
                                        $full_row[] = '';
                                    }
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

        if( !empty($_REQUEST['action']) && $_REQUEST['action'] == 'export_bookings_csv' && !empty($_REQUEST['limmud_accomodation']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'export_bookings_csv')) {

            header("Content-Type: application/octet-stream; charset=utf-8");
            header("Content-Disposition: Attachment; filename=".sanitize_title(get_bloginfo())."-bookings-accomodation.csv");
            do_action('em_csv_header_output');
            echo "\xEF\xBB\xBF"; // UTF-8 for MS Excel (a little hacky... but does the job)

            $handle = fopen("php://output", "w");
            $delimiter = !defined('EM_CSV_DELIMITER') ? ',' : EM_CSV_DELIMITER;
            $delimiter = apply_filters('em_csv_delimiter', $delimiter);

            $headers = array('room_id', 'room_type', 'order#', 'name', 'surname', 'birthday', 'toddlers', 'hotel', 'shabbat_area', 'role', 'status', 'room_mate', 'comment');
            fputcsv($handle, $headers, $delimiter);

            $orders = array();

            $events = EM_Events::get(array('scope'=>'future'));
            foreach ($events as $EM_Event) {
                if (($EM_Event->event_name != 'Limmud 2016 Registration') && ($EM_Event->event_name != 'Limmud 2016 Private Registration') && ($EM_Event->event_name != 'Limmud 2016 Self-Accomodation')) {
                    continue;
                }
                foreach ($EM_Event->get_bookings()->bookings as $EM_Booking) {

                    $order = array();
                    $order['event'] = $EM_Event->event_name;
                    $order['id'] = $EM_Booking->booking_id;
                    $order['person'] = $EM_Booking->get_person()->get_name();
                    $order['email'] = $EM_Booking->get_person()->user_email;
                    $order['phone'] = $EM_Booking->get_person()->phone;
                    $order['status'] = $EM_Booking->get_status(true);
                    if (($order['status'] != 'Approved') && ($order['status'] != 'Awaiting Payment')) {
                        continue;
                    }
                    $order['event'] = $EM_Booking->get_event()->event_name;
                    $order['address'] = $EM_Booking->booking_meta['registration']['dbem_address'];
                    $order['city'] = $EM_Booking->booking_meta['registration']['dbem_city'];
                    $order['adults'] = array();
                    $order['children'] = array();
                    $order['toddlers'] = array();
                    $order['rooms'] = array();

                    $event_id = $EM_Booking->get_event()->event_id;
                    $EM_Form = EM_Booking_Form::get_form($event_id, $EM_Booking);
                    if ($EM_Event->event_name == 'Limmud 2016 Self-Accomodation') {
                        $order['room_mate'] = '';
                        $order['shabbat_area'] = '';
                        $order['bus_needed'] = '';
                    } else {
                        $order['room_mate'] = $EM_Form->get_formatted_value($EM_Form->form_fields['room_mate'], $EM_Booking->booking_meta['booking']['room_mate']);
                        $order['shabbat_area'] = $EM_Form->get_formatted_value($EM_Form->form_fields['shabbat_area'], $EM_Booking->booking_meta['booking']['shabbat_area']);
                        $order['bus_needed'] = $EM_Form->get_formatted_value($EM_Form->form_fields['bus_needed'], $EM_Booking->booking_meta['booking']['bus_needed']);
                    }
                    $order['comment'] = $EM_Form->get_formatted_value($EM_Form->form_fields['dbem_comment'], $EM_Booking->booking_meta['booking']['dbem_comment']);
                    $order['children_num'] = 0;
                    $order['toddlers_num'] = 0;
                    $order['role'] = $EM_Form->get_formatted_value($EM_Form->form_fields['participant_role'], $EM_Booking->booking_meta['booking']['participant_role']);
                    // $order['accomodation_type'] = $EM_Form->get_formatted_value($EM_Form->form_fields['accomodation_type'], $EM_Booking->booking_meta['booking']['accomodation_type']);

                    // populate arrays from tickets and attendees data
                    $attendees_data = EM_Attendees_Form::get_booking_attendees($EM_Booking);
                    foreach($EM_Booking->get_tickets_bookings()->tickets_bookings as $EM_Ticket_Booking) {
                        $people = array();
                        if (!empty($attendees_data[$EM_Ticket_Booking->ticket_id])) {
                            foreach($attendees_data[$EM_Ticket_Booking->ticket_id] as $attendee_title => $attendee_data) {
                                $person = array();
                                $i = 0;
                                foreach( $attendee_data as $field_value) {
                                    if ($i == 0) {
                                        $person['name'] = $field_value; 
                                    }
                                    if ($i == 1) {
                                        $person['surname'] = $field_value; 
                                    }
                                    if ($i == 2) {
                                        $person['birthday'] = $field_value; 
                                    }
                                    $i++;
                                }
                                $people[] = $person;
                            }
                        }

                        if (($EM_Ticket_Booking->get_ticket()->ticket_name == 'Количество взрослых (старше 18 лет)') ||
                            ($EM_Ticket_Booking->get_ticket()->ticket_name == 'Количество подростков (старше 12 лет)')) {
                            $order['adults'] = array_merge($order['adults'], $people);
                        }

                        if ($EM_Ticket_Booking->get_ticket()->ticket_name == 'Количество детей (до 12 лет)') {
                            if (strpos($order['comment'], 'CHILD#ADULT') !== false) {
                                $order['adults'] = array_merge($order['adults'], $people);
                            } else {
                                $order['children'] = array_merge($order['children'], $people);
                                $order['children_num'] += $EM_Ticket_Booking->get_spaces();
                            }
                        }

                        if ($EM_Ticket_Booking->get_ticket()->ticket_name == 'Количество младенцев (до 3 лет)') {
                            $order['toddlers'] = array_merge($order['toddlers'], $people);
                            $order['toddlers_num'] += $EM_Ticket_Booking->get_spaces();
                        }

                        if ((strpos($EM_Ticket_Booking->get_ticket()->ticket_name, 'Room') !== false) ||
                            (strpos($EM_Ticket_Booking->get_ticket()->ticket_name, 'Place') !== false) ||
                            (strpos($EM_Ticket_Booking->get_ticket()->ticket_name, 'Self') !== false)) {
                            $room_data = array();
                            $room_data['name'] = $EM_Ticket_Booking->get_ticket()->ticket_name;
                            $room_data['people'] = array();
                            for ($i = 0; $i < $EM_Ticket_Booking->get_spaces(); $i++) {
                                $order['rooms'][] = $room_data;
                            }
                        }
                    }

                    // populate rooms
                    $adult_id = 0;
                    $child_id = 0;
                    foreach($order['rooms'] as $key => $room) {
                        if ($room['name'] == 'Room - 2 adults + 2 kids') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                        } elseif ($room['name'] == 'Room - 1 adult alone') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                        } elseif ($room['name'] == 'Room - 2 adults + kid') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                        } elseif ($room['name'] == 'Room - 3 adults') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                        } elseif ($room['name'] == 'Room - adult + 3 kids') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                        } elseif ($room['name'] == 'Room - 2 adults') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                        } elseif ($room['name'] == 'Room - adult + 2 kids') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                        } elseif ($room['name'] == 'Room - adult + kid') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                        } elseif ($room['name'] == 'Self accomodation - 3 days - adult') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                        } elseif ($room['name'] == 'Self accomodation - 1 day - adult') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                        } elseif ($room['name'] == 'Self accomodation - 3 days - kid') {
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                        } elseif ($room['name'] == 'Self accomodation - 1 day - kid') {
                            $order['rooms'][$key]['people'][] = $order['children'][$child_id++];
                        } elseif ($room['name'] == 'Place in double room') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                        } elseif ($room['name'] == 'Place in triple room') {
                            $order['rooms'][$key]['people'][] = $order['adults'][$adult_id++];
                        }
                    }
                    
                    // verify that the order has rooms for all people
                    if (($adult_id < count($order['adults'])) || ($child_id < count($order['children']))) {
                        $room_data = array();
                        $room_data['name'] = "Not enough rooms";
                        $room_data['people'] = array();
                        $person = array();
                        $person['name'] = '';
                        $person['surname'] = '';
                        $person['birthday'] = '';
                        $room_data['people'][] = $person;
                        $order['rooms'][] = $room_data;
                    }

                    $toddler_names = '';
                    $i = 0;
                    foreach($order['toddlers'] as $toddler) {
                        if ($i++ > 0) {
                            $toddler_names .= ', ';
                        }
                        $toddler_names .= $toddler['name'];
                        $toddler_names .= ' ';
                        $toddler_names .= $toddler['surname'];
                        $toddler_names .= ' ';
                        $toddler_names .= $toddler['birthday'];
                    }

                    if ((count($order['rooms']) >= 2)  && (strpos($order['comment'], 'TODDLER#12') !== false)) {
                      $order['rooms'][0]['toddler_names'] = $order['toddlers'][0]['name'] . ' ' . $order['toddlers'][0]['surname'] . ' ' . $order['toddlers'][0]['birthday'];
                      $order['rooms'][0]['toddlers_num'] = 1;
                      $order['rooms'][1]['toddler_names'] = $order['toddlers'][1]['name'] . ' ' . $order['toddlers'][1]['surname'] . ' ' . $order['toddlers'][1]['birthday'];
                      $order['rooms'][1]['toddlers_num'] = 1;
                    } elseif ((count($order['rooms']) >= 3) && (strpos($order['comment'], 'TODDLER#3') !== false)) {
                        $order['rooms'][2]['toddler_names'] = $toddler_names;
                        $order['rooms'][2]['toddlers_num'] = $order['toddlers_num'];
                    } elseif ((count($order['rooms']) >= 2) && (strpos($order['comment'], 'TODDLER#2') !== false)) {
                        $order['rooms'][1]['toddler_names'] = $toddler_names;
                        $order['rooms'][1]['toddlers_num'] = $order['toddlers_num'];
                    } elseif (count($order['rooms']) >= 1) {
                      $order['rooms'][0]['toddler_names'] = $toddler_names;
                      $order['rooms'][0]['toddlers_num'] = $order['toddlers_num'];
                    }

                    // people with children and religious - Resort; otherwise - Club
                    foreach($order['rooms'] as $key => $room) {
                        $hotel = 'Club';
                        if ((strpos($order['shabbat_area'], 'да') !== false) ||
                            (strpos($room['name'], 'kid') !== false) ||
                            ($room['toddler_names'] != '') ||
                            (strpos($order['comment'], '#RESORT') !== false)) {
                            $hotel = 'Resort';
                        }
                        if (strpos($room['name'], 'Self') !== false) {
                            $hotel = '';
                        }
                        if (strpos($order['comment'], '#CLUB') !== false) {
                            $hotel = 'Club';
                        }
                        $order['rooms'][$key]['hotel'] = $hotel;
                    }

                    $orders[$order['id']] = $order;
                }
            }

            ksort($orders);

            $room_count = 1;
            foreach($orders as $order) {
               foreach($order['rooms'] as $room) {
                   $room_id = '';
                   if (strpos($room['name'], 'Room') !== false) {
                       $room_id = (string)$room_count++;
                   }
                   $i = 0;
                   foreach($room['people'] as $person) {
                       $row = array();
                       $row[] = $room_id;
                       $row[] = $room['name'];
                       // $row[] = $order['event'];
                       // $row[] = $order['email'];
                       // $row[] = $order['phone'];
                       $row[] = $order['id'];
                       $row[] = $person['name'];
                       $row[] = $person['surname'];
                       $row[] = $person['birthday'];
                       if ($i++ == 0) {
                           $row[] = $room['toddler_names'];
                       } else {
                           $row[] = '';
                       }
                       $row[] = $room['hotel'];
                       $row[] = $order['shabbat_area'];
                       $row[] = $order['role'];
                       $row[] = $order['status'];
                       $row[] = $order['room_mate'];
                       $row[] = $order['comment'];
                       fputcsv($handle, $row, $delimiter);
                   }
               }
            }

            fclose($handle);
            exit();
        }

        if( !empty($_REQUEST['action']) && $_REQUEST['action'] == 'export_bookings_csv' && !empty($_REQUEST['limmud_transport']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'export_bookings_csv')) {

            header("Content-Type: application/octet-stream; charset=utf-8");
            header("Content-Disposition: Attachment; filename=".sanitize_title(get_bloginfo())."-bookings-transport.csv");
            do_action('em_csv_header_output');
            echo "\xEF\xBB\xBF"; // UTF-8 for MS Excel (a little hacky... but does the job)

            $handle = fopen("php://output", "w");
            $delimiter = !defined('EM_CSV_DELIMITER') ? ',' : EM_CSV_DELIMITER;
            $delimiter = apply_filters('em_csv_delimiter', $delimiter);

            $headers = array('ticket_id', 'ticket_type', 'order#', 'email', 'phone', 'destination', 'name', 'surname', 'birthday', 'toddlers', 'role', 'status', 'comment');
            fputcsv($handle, $headers, $delimiter);

            $orders = array();

            $events = EM_Events::get(array('scope'=>'future'));
            foreach ($events as $EM_Event) {
                if (($EM_Event->event_name != 'Limmud 2016 Registration') && ($EM_Event->event_name != 'Limmud 2016 Private Registration') && ($EM_Event->event_name != 'Limmud 2016 Self-Accomodation')) {
                    continue;
                }
                foreach ($EM_Event->get_bookings()->bookings as $EM_Booking) {

                    $order = array();
                    $order['event'] = $EM_Event->event_name;
                    $order['id'] = $EM_Booking->booking_id;
                    $order['person'] = $EM_Booking->get_person()->get_name();
                    $order['email'] = $EM_Booking->get_person()->user_email;
                    $order['phone'] = $EM_Booking->get_person()->phone;
                    $order['status'] = $EM_Booking->get_status(true);
                    if (($order['status'] != 'Approved') && ($order['status'] != 'Awaiting Payment')) {
                        continue;
                    }
                    $order['event'] = $EM_Booking->get_event()->event_name;
                    $order['address'] = $EM_Booking->booking_meta['registration']['dbem_address'];
                    $order['city'] = $EM_Booking->booking_meta['registration']['dbem_city'];
                    $order['people'] = array();
                    $order['toddlers'] = array();
                    $order['tickets'] = array();

                    $event_id = $EM_Booking->get_event()->event_id;
                    $EM_Form = EM_Booking_Form::get_form($event_id, $EM_Booking);
                    if ($EM_Event->event_name == 'Limmud 2016 Self-Accomodation') {
                        $order['room_mate'] = '';
                        $order['shabbat_area'] = '';
                        $order['bus_needed'] = '';
                    } else {
                        $order['room_mate'] = $EM_Form->get_formatted_value($EM_Form->form_fields['room_mate'], $EM_Booking->booking_meta['booking']['room_mate']);
                        $order['shabbat_area'] = $EM_Form->get_formatted_value($EM_Form->form_fields['shabbat_area'], $EM_Booking->booking_meta['booking']['shabbat_area']);
                        $order['bus_needed'] = $EM_Form->get_formatted_value($EM_Form->form_fields['bus_needed'], $EM_Booking->booking_meta['booking']['bus_needed']);
                    }
                    $order['comment'] = $EM_Form->get_formatted_value($EM_Form->form_fields['dbem_comment'], $EM_Booking->booking_meta['booking']['dbem_comment']);
                    $order['role'] = $EM_Form->get_formatted_value($EM_Form->form_fields['participant_role'], $EM_Booking->booking_meta['booking']['participant_role']);
                    // $order['accomodation_type'] = $EM_Form->get_formatted_value($EM_Form->form_fields['accomodation_type'], $EM_Booking->booking_meta['booking']['accomodation_type']);

                    // populate arrays from tickets and attendees data
                    $attendees_data = EM_Attendees_Form::get_booking_attendees($EM_Booking);
                    foreach($EM_Booking->get_tickets_bookings()->tickets_bookings as $EM_Ticket_Booking) {
                        $people = array();
                        if (!empty($attendees_data[$EM_Ticket_Booking->ticket_id])) {
                            foreach($attendees_data[$EM_Ticket_Booking->ticket_id] as $attendee_title => $attendee_data) {
                                $person = array();
                                $i = 0;
                                foreach( $attendee_data as $field_value) {
                                    if ($i == 0) {
                                        $person['name'] = $field_value; 
                                    }
                                    if ($i == 1) {
                                        $person['surname'] = $field_value; 
                                    }
                                    if ($i == 2) {
                                        $person['birthday'] = $field_value; 
                                    }
                                    $i++;
                                }
                                $people[] = $person;
                            }
                        }

                        if (($EM_Ticket_Booking->get_ticket()->ticket_name == 'Количество взрослых (старше 18 лет)') ||
                            ($EM_Ticket_Booking->get_ticket()->ticket_name == 'Количество подростков (старше 12 лет)') ||
                            ($EM_Ticket_Booking->get_ticket()->ticket_name == 'Количество детей (до 12 лет)')) {
                            $order['people'] = array_merge($order['people'], $people);
                        }

                        if ($EM_Ticket_Booking->get_ticket()->ticket_name == 'Количество младенцев (до 3 лет)') {
                            $order['toddlers'] = array_merge($order['toddlers'], $people);
                        }

                        if (strpos($EM_Ticket_Booking->get_ticket()->ticket_name, 'Transport') !== false) {
                            $ticket_data = array();
                            $ticket_data['name'] = $EM_Ticket_Booking->get_ticket()->ticket_name;
                            $ticket_data['people'] = array();
                            for ($i = 0; $i < $EM_Ticket_Booking->get_spaces(); $i++) {
                                $order['tickets'][] = $ticket_data;
                            }
                        }
                    }
                    
                    if (count($order['tickets']) == 0) {
                        continue;
                    }

                    // populate tickets
                    $person_id = 0;
                    foreach($order['tickets'] as $key => $room) {
                        $order['tickets'][$key]['people'][] = $order['people'][$person_id++];
                    }
                    
                    // verify that the order has tickets for all people
                    if ($person_id < count($order['people'])) {
                        $ticket_data = array();
                        $ticket_data['name'] = "Not enough tickets";
                        $ticket_data['people'] = array();
                        $person = array();
                        $person['name'] = '';
                        $person['surname'] = '';
                        $person['birthday'] = '';
                        $ticket_data['people'][] = $person;
                        $order['tickets'][] = $ticket_data;
                    }

                    $toddler_names = '';
                    $i = 0;
                    foreach($order['toddlers'] as $toddler) {
                        if ($i++ > 0) {
                            $toddler_names .= ', ';
                        }
                        $toddler_names .= $toddler['name'];
                        $toddler_names .= ' ';
                        $toddler_names .= $toddler['surname'];
                        $toddler_names .= ' ';
                        $toddler_names .= $toddler['birthday'];
                    }
                    $order['tickets'][0]['toddler_names'] = $toddler_names;

                    $orders[$order['id']] = $order;
                }
            }

            ksort($orders);

            $ticket_count = 1;
            foreach($orders as $order) {
               foreach($order['tickets'] as $ticket) {
                   $i = 0;
                   foreach($ticket['people'] as $person) {
                       $row = array();
                       if ($ticket['name'] == 'Not enough tickets') {
                           $row[] = '';
                       } else {
                           $row[] = $ticket_count++;
                       }
                       $row[] = $ticket['name'];
                       // $row[] = $order['event'];
                       $row[] = $order['id'];
                       $row[] = $order['email'];
                       $row[] = $order['phone'];
                       $row[] = $order['bus_needed'];
                       $row[] = $person['name'];
                       $row[] = $person['surname'];
                       $row[] = $person['birthday'];
                       if ($i++ == 0) {
                           $row[] = $ticket['toddler_names'];
                       } else {
                           $row[] = '';
                       }
                       $row[] = $order['role'];
                       $row[] = $order['status'];
                       $row[] = $order['comment'];
                       fputcsv($handle, $row, $delimiter);
                   }
               }
            }

            fclose($handle);
            exit();
        }
    }
}

EM_Paypal_CSV::init();
