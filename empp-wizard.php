<?php

class EM_Paypal_Wizard {
    public static function init() {
        add_action('em_bookings_admin_ticket_totals_header', array(__CLASS__, 'admin_wizard'));
        add_action('em_options_page_footer', array(__CLASS__, 'misc_options'));
    }

    public static function admin_wizard() {
        if (get_option('dbem_show_admin_wizard') != 'show') {
            return;
        }

    ?>
        <tr class="em-booking-single-edit">
            <th></th>
            <td>
                <input type="button" id="wizard-button" value="Admin Wizard" />
            </td>
        </tr>
        
		<script type="text/javascript">
                document.getElementById('wizard-button').onclick = function() {
                    var parents = parseInt(document.getElementsByName("em_tickets[81][spaces]")[0].value); 
                    var teenagers = parseInt(document.getElementsByName("em_tickets[82][spaces]")[0].value);
                    var kids = parseInt(document.getElementsByName("em_tickets[83][spaces]")[0].value);
                    var toddlers = parseInt(document.getElementsByName("em_tickets[86][spaces]")[0].value);

                    var accomodation_element = document.getElementsByName("accomodation_type")[0];
                    var accomodation_text = accomodation_element.options[accomodation_element.selectedIndex].text; 

                    var transportation_element = document.getElementsByName("bus_needed")[0];

                    var volunteer_element = document.getElementsByName("participant_role")[0];

                    if (parents == NaN) { parents = 0; }
                    if (teenagers == NaN) { teenagers = 0; }
                    if (kids == NaN) { kids = 0; }
                    if (toddlers == NaN) { toddlers = 0; }

                    var adults = parents + teenagers;
                    
                    // init
                    var room_3_adult = 0;
                    var room_2_adult = 0;
                    var room_2_adult_1_kid = 0;
                    var room_2_adult_2_kid = 0;
                    var room_1_adult_1_kid = 0;
                    var room_1_adult_2_kid = 0;
                    var room_1_adult_3_kid = 0;
                    var place_in_double_room = 0;
                    var place_in_triple_room = 0;

                    var ticket_1_night_adult = 0;
                    var ticket_1_night_kid = 0;
                    var ticket_3_night_adult = 0;
                    var ticket_3_night_kid = 0;
                    
                    var comment = "";
                    var transportation = 0;
                    var volunteer_discount = 0;

                    if (accomodation_text.includes("1")) {
                        // self accomodation - 1 night
                        ticket_1_night_adult = adults;
                        ticket_1_night_kid = kids;
                    } else if (accomodation_text.includes("3")) {
                        // self accomodation - 3 nights
                        ticket_3_night_adult = adults;
                        ticket_3_night_kid = kids;
                    } else if (kids == 0) {
                        // adults only                    
                        var e = document.getElementsByName("room_type")[0];
                        if (e.options[e.selectedIndex].text.includes("3")) {
                            room_3_adult = (int)(adults / 3);
                            place_in_triple_room = adults - (room_3_adult * 3);
                        } else {
                            room_2_adult = (int)(adults / 2);
                            place_in_double_room = adults - (room_2_adult * 2);
                        }
                    }
                    else if (adults == 1) {
                        if (kids == 1) {
                            room_1_adult_1_kid = 1;
                        }
                        else if (kids == 2) {
                            room_1_adult_2_kid = 1;
                        }
                        else if (kids == 3) {
                            room_1_adult_3_kid = 1;
                        } else {
                            comment = "too many kids";
                        }
                    }
                    else if (adults == 2) {
                        if (kids == 1) {
                            room_2_adult_1_kid = 1;
                        }
                        else if (kids == 2) {
                            room_2_adult_2_kid = 1;
                        }
                        else {
                            comment = "too many kids";
                        }
                    }
                    else if (adults == 3) {
                        if (kids == 1) {
                            room_2_adult = 1;
                            room_1_adult_1_kid = 1;
                        }
                        else if (kids == 2) {
                            room_2_adult = 1;
                            room_1_adult_2_kid = 1;
                        }
                        else if (kids == 3) {
                            room_2_adult_1_kid = 1;
                            room_1_adult_2_kid = 1;
                        }
                        else if (kids == 4) {
                            room_2_adult_1_kid = 1;
                            room_1_adult_3_kid = 1;
                        }
                        else if (kids == 5) {
                            room_2_adult_2_kid = 1;
                            room_1_adult_3_kid = 1;
                        }
                        else {
                            comment = "too many kids";
                        }
                    }
                    else if (adults == 4) {
                        if (kids == 1) {
                            room_2_adult = 1;
                            room_2_adult_1_kid = 1;
                        }
                        else if (kids == 2) {
                            room_2_adult = 1;
                            room_2_adult_2_kid = 1;
                        }
                        else if (kids == 3) {
                            room_2_adult_1_kid = 1;
                            room_2_adult_2_kid = 1;
                        }
                        else if (kids == 4) {
                            room_2_adult_2_kid = 1;
                            room_2_adult_2_kid = 1;
                        }
                        else {
                            comment = "too many kids";
                        }
                    }
                    
                    if (transportation_element.selectedIndex != 0) {
                        transportation = adults + kids;
                    }

                    if (volunteer_element.selectedIndex != 0) {
                        volunteer_discount = 1;
                    }
                    
                    // update form
                    document.getElementsByName("em_tickets[97][spaces]")[0].value = place_in_double_room;
                    document.getElementsByName("em_tickets[98][spaces]")[0].value = place_in_triple_room;
                    document.getElementsByName("em_tickets[99][spaces]")[0].value = room_2_adult; 
                    document.getElementsByName("em_tickets[100][spaces]")[0].value = room_1_adult_1_kid;  
                    document.getElementsByName("em_tickets[101][spaces]")[0].value = room_1_adult_2_kid;  
                    document.getElementsByName("em_tickets[102][spaces]")[0].value = room_1_adult_3_kid;
                    document.getElementsByName("em_tickets[103][spaces]")[0].value = room_2_adult_1_kid; 
                    document.getElementsByName("em_tickets[104][spaces]")[0].value = room_2_adult_2_kid;
                    document.getElementsByName("em_tickets[111][spaces]")[0].value = room_3_adult; 

                    document.getElementsByName("em_tickets[105][spaces]")[0].value = ticket_3_night_adult;
                    document.getElementsByName("em_tickets[106][spaces]")[0].value = ticket_1_night_adult;
                    document.getElementsByName("em_tickets[107][spaces]")[0].value = ticket_3_night_kid;
                    document.getElementsByName("em_tickets[108][spaces]")[0].value = ticket_1_night_kid;

                    document.getElementsByName("em_tickets[112][spaces]")[0].value = transportation;
                    document.getElementsByName("em_tickets[113][spaces]")[0].value = volunteer_discount;
                    
                    var delim = "\n--------------------\n"; 
                    var c = document.getElementById("dbem_comment").value;
                    if (c.includes(delim)) {
                        c = c.split(delim)[1];
                    } 
                    if (comment) {
                        document.getElementById("dbem_comment").value = comment + delim + c;
                    } else {
                        document.getElementById("dbem_comment").value = c;
                    }
                }
        </script>
    <?php
    }

    public static function misc_options() {
        global $save_button;
        ?>
        <div  class="postbox " id="em-opt-misc-options" >
        <div class="handlediv" title="Click to toggle"><br /></div><h3>Admin Wizard (for Limmud)</h3>
        <div class="inside">
            <table class='form-table'>
                <?php
                    em_options_select ( __( 'Show Admin Wizard', 'em-paypal' ), 'dbem_show_admin_wizard', array ('hide' => 'Hide', 'show' => 'Show'), '' );                                      
                ?>
            </table>
        </div> <!-- . inside -->
        </div> <!-- .postbox -->
        <?php
    }
}

EM_Paypal_Wizard::init();