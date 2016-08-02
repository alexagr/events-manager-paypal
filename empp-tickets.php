<?php

class EM_Paypal_Tickets {
    public static function init() {
        if (current_user_can('manage_others_bookings')) {
            add_action('em_bookings_event_footer', array(__CLASS__, 'output'), 11);
        }
    }

    public static function output($EM_Event) {
        $EM_Tickets = $EM_Event->get_tickets();
        if (count($EM_Tickets->tickets) > 1) {
            ?>
            <div class="table-wrap">            
            <div class="wrap">            
            <h2><?php esc_html_e('Tickets','events-paypal'); ?></h2>
            </div>
            <table class="widefat">
                <thead>
                    <tr valign="top">
                        <th colspan="2"><?php esc_html_e('Ticket Name','events-paypal'); ?></th>
                        <th><?php esc_html_e('Price','events-paypal'); ?></th>
                        <th><?php esc_html_e('Pending Spaces','events-paypal'); ?></th>
                        <th><?php esc_html_e('Booked Spaces','events-paypal'); ?></th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>    
                <?php
                    $col_count = 0;
                    foreach ($EM_Tickets->tickets as $EM_Ticket) {
                        ?>
                        <tbody id="em-ticket-<?php echo $col_count ?>" >
                            <tr class="em-tickets-row">
                                <td class="ticket-status"><span class="<?php if($EM_Ticket->ticket_id && $EM_Ticket->is_available()){ echo 'ticket_on'; }elseif($EM_Ticket->ticket_id > 0){ echo 'ticket_off'; }else{ echo 'ticket_new'; } ?>"></span></td>                                                 
                                <td class="ticket-name">
                                    <span class="ticket_name"><?php if($EM_Ticket->ticket_members) echo '* ';?><?php echo wp_kses_data($EM_Ticket->ticket_name); ?></span>
                                </td>
                                <td class="ticket-price">
                                    <span class="ticket_price"><?php echo ($EM_Ticket->ticket_price) ? esc_html($EM_Ticket->get_price_precise()) : esc_html__('Free','events-manager'); ?></span>
                                </td>
                                <td class="ticket-pending-spaces">
                                    <span class="ticket_pending_spaces"><?php echo $EM_Ticket->get_pending_spaces(); ?></span>
                                </td>
                                <td class="ticket-booked-spaces">
                                    <span class="ticket_booked_spaces"><?php echo $EM_Ticket->get_booked_spaces(); ?></span>
                                </td>
                            </tr>
                        </tbody>
                        <?php
                        $col_count++;
                    }
                ?>
            </table>
            </div>
        <?php
        } 
    }
}

EM_Paypal_Tickets::init();