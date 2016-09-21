<?php

/**
 * Limmud-specific adjustments and custom coding
 */

class EM_Paypal_Limmud {
    public static function init() {
        add_action('em_bookings_admin_ticket_totals_header', array(__CLASS__, 'admin_wizard'));
        add_action('em_options_page_footer', array(__CLASS__, 'misc_options'));
        add_action('em_booking_js', array(__CLASS__, 'em_booking_js'));
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
                var parents = 0;
                var teenagers = 0;
                var kids = 0;
                var toddlers = 0;
                
                if (document.getElementsByName("em_tickets[81][spaces]")[0]) {
                    parents = parseInt(document.getElementsByName("em_tickets[81][spaces]")[0].value); 
                    teenagers = parseInt(document.getElementsByName("em_tickets[82][spaces]")[0].value);
                    kids = parseInt(document.getElementsByName("em_tickets[83][spaces]")[0].value);
                    toddlers = parseInt(document.getElementsByName("em_tickets[86][spaces]")[0].value);
                } else {
                    parents = parseInt(document.getElementsByName("em_tickets[139][spaces]")[0].value); 
                    teenagers = parseInt(document.getElementsByName("em_tickets[140][spaces]")[0].value);
                    kids = parseInt(document.getElementsByName("em_tickets[141][spaces]")[0].value);
                    toddlers = parseInt(document.getElementsByName("em_tickets[142][spaces]")[0].value);
                }

                var accomodation_element = document.getElementsByName("accomodation_type")[0];
                var transportation_element = document.getElementsByName("bus_needed")[0];
                var volunteer_element = document.getElementsByName("participant_role")[0];
                var student_element = document.getElementsByName("discount_student")[0];

                if (parents == NaN) { parents = 0; }
                if (teenagers == NaN) { teenagers = 0; }
                if (kids == NaN) { kids = 0; }
                if (toddlers == NaN) { toddlers = 0; }

                var adults = parents + teenagers;
                
                // init
                var room_3_adult = 0;
                var room_2_adult = 0;
                var room_1_adult = 0;
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
                var student_discount = 0;

                if (accomodation_element.selectedIndex == 1) {
                    // self accomodation - 1 night
                    ticket_1_night_adult = adults;
                    ticket_1_night_kid = kids;
                } else if (accomodation_element.selectedIndex == 2) {
                    // self accomodation - 3 nights
                    ticket_3_night_adult = adults;
                    ticket_3_night_kid = kids;
                } else if (kids == 0) {
                    // adults only
                    var e = document.getElementsByName("room_type")[0];
                    if (e.selectedIndex == 1) {
                        room_3_adult = ~~(adults / 3);
                        place_in_triple_room = adults - (room_3_adult * 3);
                    } else if (e.selectedIndex == 0) {
                        room_2_adult = ~~(adults / 2);
                        place_in_double_room = adults - (room_2_adult * 2);
                    } else {
                        room_1_adult = 1;
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
                    else if (kids == 3) {
                        room_1_adult_1_kid = 1;
                        room_1_adult_2_kid = 1;
                    }
                    else if (kids == 4) {
                        room_1_adult_2_kid = 1;
                        room_1_adult_2_kid = 1;
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

                if (student_element.selectedIndex != 0) {
                    student_discount = 1;
                }
                
                // update form
                if (document.getElementsByName("em_tickets[81][spaces]")[0]) {
                    document.getElementsByName("em_tickets[97][spaces]")[0].value = place_in_double_room;
                    document.getElementsByName("em_tickets[98][spaces]")[0].value = place_in_triple_room;
                    document.getElementsByName("em_tickets[99][spaces]")[0].value = room_2_adult; 
                    document.getElementsByName("em_tickets[100][spaces]")[0].value = room_1_adult_1_kid;  
                    document.getElementsByName("em_tickets[101][spaces]")[0].value = room_1_adult_2_kid;  
                    document.getElementsByName("em_tickets[102][spaces]")[0].value = room_1_adult_3_kid;
                    document.getElementsByName("em_tickets[103][spaces]")[0].value = room_2_adult_1_kid; 
                    document.getElementsByName("em_tickets[104][spaces]")[0].value = room_2_adult_2_kid;
                    document.getElementsByName("em_tickets[111][spaces]")[0].value = room_3_adult; 
                    document.getElementsByName("em_tickets[138][spaces]")[0].value = room_1_adult; 
    
                    document.getElementsByName("em_tickets[105][spaces]")[0].value = ticket_3_night_adult;
                    document.getElementsByName("em_tickets[106][spaces]")[0].value = ticket_1_night_adult;
                    document.getElementsByName("em_tickets[107][spaces]")[0].value = ticket_3_night_kid;
                    document.getElementsByName("em_tickets[108][spaces]")[0].value = ticket_1_night_kid;
    
                    document.getElementsByName("em_tickets[112][spaces]")[0].value = transportation;
                    // document.getElementsByName("em_tickets[113][spaces]")[0].value = volunteer_discount;
                    document.getElementsByName("em_tickets[110][spaces]")[0].value = student_discount;
                } else {
                    document.getElementsByName("em_tickets[151][spaces]")[0].value = place_in_double_room;
                    document.getElementsByName("em_tickets[152][spaces]")[0].value = place_in_triple_room;
                    document.getElementsByName("em_tickets[148][spaces]")[0].value = room_2_adult; 
                    document.getElementsByName("em_tickets[150][spaces]")[0].value = room_1_adult_1_kid;  
                    document.getElementsByName("em_tickets[149][spaces]")[0].value = room_1_adult_2_kid;  
                    document.getElementsByName("em_tickets[147][spaces]")[0].value = room_1_adult_3_kid;
                    document.getElementsByName("em_tickets[145][spaces]")[0].value = room_2_adult_1_kid; 
                    document.getElementsByName("em_tickets[143][spaces]")[0].value = room_2_adult_2_kid;
                    document.getElementsByName("em_tickets[146][spaces]")[0].value = room_3_adult; 
                    document.getElementsByName("em_tickets[144][spaces]")[0].value = room_1_adult; 
    
                    document.getElementsByName("em_tickets[153][spaces]")[0].value = ticket_3_night_adult;
                    document.getElementsByName("em_tickets[154][spaces]")[0].value = ticket_1_night_adult;
                    document.getElementsByName("em_tickets[155][spaces]")[0].value = ticket_3_night_kid;
                    document.getElementsByName("em_tickets[156][spaces]")[0].value = ticket_1_night_kid;
    
                    document.getElementsByName("em_tickets[157][spaces]")[0].value = transportation;
                    document.getElementsByName("em_tickets[159][spaces]")[0].value = student_discount;
                }
                
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
    
	public static function em_booking_js() {
    ?>
    
        $('select').change(function() {
            var parents = document.getElementsByName("em_tickets[81][spaces]")[0]; 
            var teenagers = document.getElementsByName("em_tickets[82][spaces]")[0];
            var kids = document.getElementsByName("em_tickets[83][spaces]")[0];
            var accomodation = document.getElementsByName("accomodation_type")[0];
            var participant = document.getElementsByName("participant_role")[0];
            var room_type = document.getElementsByName("room_type")[0];
            
            if (parents == null) {
                parents = document.getElementsByName("em_tickets[139][spaces]")[0]; 
                teenagers = document.getElementsByName("em_tickets[140][spaces]")[0];
                kids = document.getElementsByName("em_tickets[141][spaces]")[0];
            }

            if (accomodation.selectedIndex > 0) {
                document.getElementsByName("shabbat_area")[0].style.display = "none";
                $('label[for="shabbat_area"]').hide();
                document.getElementsByName("bus_needed")[0].style.display = "none";
                $('label[for="bus_needed"]').hide();
            } else {
                document.getElementsByName("shabbat_area")[0].style.display = "inline-block";
                $('label[for="shabbat_area"]').show();
                document.getElementsByName("bus_needed")[0].style.display = "inline-block";
                $('label[for="bus_needed"]').show();
            }

            if ((accomodation.selectedIndex > 0) || (kids.selectedIndex > 0)) {
                document.getElementsByName("room_type")[0].style.display = "none";
                $('label[for="room_type"]').hide();
                document.getElementsByName("room_mate")[0].style.display = "none";
                $('label[for="room_mate"]').hide();
                document.getElementsByName("discount_student")[0].style.display = "none";
                $('label[for="discount_student"]').hide();
            } else {
                document.getElementsByName("room_type")[0].style.display = "inline-block";
                $('label[for="room_type"]').show();
                document.getElementsByName("room_mate")[0].style.display = "inline-block";
                $('label[for="room_mate"]').show();
                if (room_type.selectedIndex == 1) {
                    document.getElementsByName("discount_student")[0].style.display = "inline-block";
                    $('label[for="discount_student"]').show();
                } else {
                    document.getElementsByName("discount_student")[0].style.display = "none";
                    $('label[for="discount_student"]').hide();
                }
            }
        }).change();
    <?php
	}     
}

EM_Paypal_Limmud::init();
