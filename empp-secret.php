<?php

class EM_Paypal_Secret {
    public static function init() {
        add_filter('em_booking_validate', array(__CLASS__, 'em_booking_validate'), 15, 2); //validate object
    }
    
    public static function em_booking_validate($result, $EM_Booking) {
        $secret_code = $EM_Booking->booking_meta['booking']['secret_code'];
        $valid = true;
        $exists = false;
        if (!empty($secret_code) && ($secret_code != '')) {
            // check that code is valid
            $valid = false;
            $file = @fopen(WP_PLUGIN_DIR.'/events-manager-secrets/secrets.txt', 'r'); 
            if ($file) {
                while (($str = fgets($file, 1024)) !== false) {
                    $str = str_replace("\n", '', $str);
                    $str = str_replace("\r", '', $str);
                    if ($secret_code == $str) {
                        $valid = true;
                    }
                }
            }

            // check that secret code was not already used
            foreach ($EM_Booking->get_event()->get_bookings()->bookings as $EM_OtherBooking) {
                if (($EM_Booking->booking_id != $EM_OtherBooking->booking_id) && ($secret_code == $EM_OtherBooking->booking_meta['booking']['secret_code'])) {
                    $exists = true;
                }
            }
        }
        
        if (!$valid) {
            $EM_Booking->add_error('Secret code is invalid');
            $result = false;
        }
        if ($exists) {
            $EM_Booking->add_error('Secret code was already used');
            $result = false;
        }
        return $result;    
    }
}

EM_Paypal_Secret::init();