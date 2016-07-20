<?php

class EM_Paypal_Admin {
    public static function init() {
        add_action('em_options_page_footer_emails', array(__CLASS__, 'email_options'));
        add_action('em_options_page_footer', array(__CLASS__, 'paypal_options'));
        add_action('em_options_page_footer', array(__CLASS__, 'misc_options'));
    }

    public static function email_options() {
        global $save_button;
        ?>
        <div  class="postbox " id="em-opt-email-paypal" >
        <div class="handlediv" title="Click to toggle"><br /></div><h3>Paypal Email Templates</h3>
        <div class="inside">
            <table class='form-table'>
                <?php
                $email_subject_tip = __('You can disable this email by leaving the subject blank.','dbem'); 
                $email_hashtag_tip = ' This accepts all regular placeholders and #_PAYPAL for PayPal link.'; 
                ?>
                <tr class="em-header"><td colspan='2'><h4><?php _e('Event Admin/Owner Emails', 'dbem'); ?></h4></td></tr>
                    <tbody class="em-subsection">
                    <tr class="em-subheader"><td colspan='2'>
                        <h5><?php _e('Awaiting payment email','dbem') ?></h5> 
                        <em><?php echo __('This is sent when a person\'s booking is awaiting payment.'.$email_hashtag_tip,'dbem') ?></em>
                    </td></tr>
                    <?php
                        em_options_input_text ( __( 'Awaiting payment email subject', 'em-paypal' ), 'dbem_bookings_contact_email_awaiting_payment_subject', $email_subject_tip );                 
                        em_options_textarea ( __( 'Awaiting payment email', 'em-paypal' ), 'dbem_bookings_contact_email_awaiting_payment_body', '' );
                    ?>
                <tr class="em-header"><td colspan='2'><h4><?php _e('Booked User Emails', 'dbem'); ?></h4></td></tr>
                    <tbody class="em-subsection">
                    <tr class="em-subheader"><td colspan='2'>
                        <h5><?php _e('Awaiting payment email','dbem') ?></h5> 
                        <em><?php echo __('This will be sent to the person when their booking is awaiting payment.'.$email_hashtag_tip,'dbem') ?></em>
                    </td></tr>
                    <?php
                        em_options_input_text ( __( 'Awaiting payment email subject', 'em-paypal' ), 'dbem_bookings_email_awaiting_payment_subject', $email_subject_tip );                 
                        em_options_textarea ( __( 'Awaiting payment email', 'em-paypal' ), 'dbem_bookings_email_awaiting_payment_body', '' );
                    ?>
                <?php echo $save_button; ?>
            </table>
        </div> <!-- . inside -->
        </div> <!-- .postbox -->
        <?php
    }

    public static function paypal_options() {
        global $save_button;
        ?>
        <div  class="postbox " id="em-opt-paypal-options" >
        <div class="handlediv" title="Click to toggle"><br /></div><h3>Paypal Options</h3>
        <div class="inside">
            <table class='form-table'>
                <?php
                    em_options_input_text ( __( 'PayPal Email', 'em-paypal' ), 'dbem_paypail_email', '' );
                    em_options_select ( __( 'PayPal Mode', 'em-paypal' ), 'dbem_paypail_status', array ('live' => 'Live Site', 'test' => 'Test Mode (Sandbox)'), '' );                                      
                ?>
            </table>
        </div> <!-- . inside -->
        </div> <!-- .postbox -->
        <?php
    }

    public static function misc_options() {
        global $save_button;
        ?>
        <div  class="postbox " id="em-opt-misc-options" >
        <div class="handlediv" title="Click to toggle"><br /></div><h3>Miscellaneous Options</h3>
        <div class="inside">
            <table class='form-table'>
                <?php
                    em_options_select ( __( 'Show Ticket Price', 'em-paypal' ), 'dbem_show_ticket_price', array ('show' => 'Show', 'hide' => 'Hide'), '' );                                      
                    em_options_input_text ( __( 'Tickets Name', 'em-paypal' ), 'dbem_tickets_name', '' );
                    em_options_input_text ( __( 'Spaces Name', 'em-paypal' ), 'dbem_spaces_name', '' );
                    em_options_select ( __( 'Count Admin Tickets', 'em-paypal' ), 'dbem_show_admin_tickets', array ('count' => 'Count', 'ignore' => 'Ignore'), '' );                                      
                    em_options_select ( __( 'Show Event Details', 'em-paypal' ), 'dbem_show_event_details', array ('show' => 'Show', 'hide' => 'Hide'), '' );                                      
                ?>
            </table>
        </div> <!-- . inside -->
        </div> <!-- .postbox -->
        <?php
    }
}

EM_Paypal_Admin::init();