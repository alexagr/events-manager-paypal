<?php

class EM_Paypal_Discount {
    public static function init() {
        add_action('em_bookings_admin_ticket_totals_header', array(__CLASS__, 'admin_discount'));
        add_filter('em_booking_get_post', array(__CLASS__, 'em_booking_get_post'), 10, 2);
    }

    public static function admin_discount() {
        global $EM_Booking;
    ?>
        <tr class="em-booking-single-edit">
            <th><?php esc_html_e('Admin Discount','events-paypal'); ?></th>
            <td>
                <?php
                $amount = 0;
                if (!empty($EM_Booking->booking_meta['discounts']) && is_array($EM_Booking->booking_meta['discounts'])) {
                    $discounts = $EM_Booking->booking_meta['discounts']; 
                    foreach($discounts as $discount) {
                        if ($discount['name'] == 'Admin Discount') {
                            $amount = $discount['amount']; 
                        }
                    }
                }
                ?>
                <input name="admin_discount" class="em-ticket-select" id="admin-discount" value="<?php echo $amount; ?>" />
            </td>
            <td><?php echo em_get_currency_symbol()?></td>
        </tr>
    <?php
    }

    public static function em_booking_get_post( $result, $EM_Booking) { 
        if (isset($_REQUEST['admin_discount'])) {
            // EM_Pro::log('em_booking_get_post admin_discount='.$_REQUEST['admin_discount']);
            $discount = array();
            $discount['name'] = 'Admin Discount';
            $discount['desc'] = 'Admin Discount';
            $discount['tax'] = 'post';
            $discount['type'] = '#';
            $discount['amount'] = $_REQUEST['admin_discount'];
            
            $discounts = array();
            if (!empty($EM_Booking->booking_meta['discounts']) && is_array($EM_Booking->booking_meta['discounts'])) {
                $discounts = $EM_Booking->booking_meta['discounts']; 
                foreach($discounts as $key => $value) {
                    if ($value['name'] == 'Admin Discount') {
                        unset($discounts[$key]); 
                    } 
                }
            }
            
            $discounts[] = $discount;
            $EM_Booking->booking_meta['discounts'] = $discounts;
        }
        return $result;
    }
}

EM_Paypal_Discount::init();