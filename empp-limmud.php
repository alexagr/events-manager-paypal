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
                var self_accomodation = false;

                var TICKET_ADULT = 0;
                var TICKET_KID = 0;
                var TICKET_TODDLER = 0;
                
                if (document.getElementsByName("em_tickets[4][spaces]").length > 0) {
                    // regular registration
                    TICKET_ADULT = 4;
                    TICKET_KID = 2;
                    TICKET_TODDLER = 3;
                } else if (document.getElementsByName("em_tickets[39][spaces]").length > 0) {
                    // registration for volunteers and presenters
                    TICKET_ADULT = 39;
                    TICKET_KID = 40;
                    TICKET_TODDLER = 41;
                } else if (document.getElementsByName("em_tickets[91][spaces]").length > 0) {
                    // registration for self-accomodation
                    TICKET_ADULT = 91;
                    TICKET_KID = 92;
                    TICKET_TODDLER = 93;
                    self_accomodation = true;
                }

                var adults = parseInt(document.getElementsByName("em_tickets[" + TICKET_ADULT.toString() + "][spaces]")[0].value); 
                var kids = parseInt(document.getElementsByName("em_tickets[" + TICKET_KID.toString() + "][spaces]")[0].value);
                var toddlers = parseInt(document.getElementsByName("em_tickets[" + TICKET_TODDLER.toString() + "][spaces]")[0].value);
                if (adults == NaN) { adults = 0; }
                if (kids == NaN) { kids = 0; }
                if (toddlers == NaN) { toddlers = 0; }

                var accomodation_element = document.getElementsByName("accomodation_type")[0];
                var transportation_element = document.getElementsByName("bus_needed")[0];

                var volunteer_num = 0;
                var attendee_types = document.getElementsByClassName("attendee_type");
                for (var i = 0 ; i < attendee_types.length ; i++ ) {
                    if (attendee_types.item(i).selectedIndex != 0) {
                        volunteer_num += 1;
                    }
                } 
               
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

                var ticket_1_day_adult = 0;
                var ticket_1_day_kid = 0;
                var ticket_3_day_adult = 0;
                var ticket_3_day_kid = 0;
                
                var comment = "";
                var transportation = 0;
                var volunteer_2_discount = 0;
                var volunteer_3_discount = 0;
                var volunteer_self1_discount = 0;
                var volunteer_self3_discount = 0;
                var student_discount = 0;
                var transportation_discount = 0;

                if (self_accomodation) {
                    if (accomodation_element.selectedIndex == 0) {
                        // self accomodation - 3 nights
                        ticket_3_day_adult = adults;
                        ticket_3_day_kid = kids;
                    } else {
                        // self accomodation - 1 night
                        ticket_1_day_adult = adults;
                        ticket_1_day_kid = kids;
                    }  
                } else if (accomodation_element.selectedIndex == 1) {
                    // self accomodation - 1 night
                    ticket_1_day_adult = adults;
                    ticket_1_day_kid = kids;
                } else if (accomodation_element.selectedIndex == 2) {
                    // self accomodation - 3 nights
                    ticket_3_day_adult = adults;
                    ticket_3_day_kid = kids;
                } else if (kids == 0) {
                    // adults only
                    var e = document.getElementsByName("room_type")[0];
                    if ((e.selectedIndex == 1) || (e.selectedIndex == 3)) {
                        room_3_adult = ~~(adults / 3);
                        place_in_triple_room = adults - (room_3_adult * 3);
                    } else if (e.selectedIndex == 4) {
                        room_1_adult = adults;
                    } else { 
                        room_2_adult = ~~(adults / 2);
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
                
                if (self_accomodation || accomodation_element.selectedIndex > 0) {
                    if (volunteer_num > 0) {
                        if (ticket_3_day_adult > 0) {
                            volunteer_self3_discount = volunteer_num;
                        } else {
                            volunteer_self1_discount = volunteer_num;
                        }
                    }
                } else {
                    if (transportation_element.selectedIndex != 0) {
                        transportation = adults + kids;
                        transportation_discount = volunteer_num; 
                    }
    
                    if (volunteer_num > 0) {
                        if ((room_3_adult > 0) || (place_in_triple_room > 0)) {
                            volunteer_3_discount = volunteer_num;
                        } else {
                            volunteer_2_discount = volunteer_num;
                        }
                    }
                }
                
                // update form
                if (document.getElementsByName("em_tickets[4][spaces]").length > 0) {
                    document.getElementsByName("em_tickets[5][spaces]")[0].value = room_2_adult_2_kid;
                    document.getElementsByName("em_tickets[6][spaces]")[0].value = room_1_adult; 
                    document.getElementsByName("em_tickets[7][spaces]")[0].value = room_2_adult_1_kid; 
                    document.getElementsByName("em_tickets[8][spaces]")[0].value = room_3_adult; 
                    document.getElementsByName("em_tickets[9][spaces]")[0].value = room_1_adult_3_kid;
                    document.getElementsByName("em_tickets[10][spaces]")[0].value = room_2_adult; 
                    document.getElementsByName("em_tickets[11][spaces]")[0].value = room_1_adult_2_kid;  
                    document.getElementsByName("em_tickets[12][spaces]")[0].value = room_1_adult_1_kid;  
                    document.getElementsByName("em_tickets[13][spaces]")[0].value = place_in_double_room;
                    document.getElementsByName("em_tickets[14][spaces]")[0].value = place_in_triple_room;
    
                    document.getElementsByName("em_tickets[15][spaces]")[0].value = ticket_3_day_adult;
                    document.getElementsByName("em_tickets[16][spaces]")[0].value = ticket_1_day_adult;
                    document.getElementsByName("em_tickets[17][spaces]")[0].value = ticket_3_day_kid;
                    document.getElementsByName("em_tickets[18][spaces]")[0].value = ticket_1_day_kid;
    
                    document.getElementsByName("em_tickets[19][spaces]")[0].value = transportation;
                    
                    document.getElementsByName("em_tickets[20][spaces]")[0].value = transportation_discount;
                    document.getElementsByName("em_tickets[21][spaces]")[0].value = student_discount;
                    document.getElementsByName("em_tickets[22][spaces]")[0].value = volunteer_3_discount;
                    document.getElementsByName("em_tickets[23][spaces]")[0].value = volunteer_2_discount;
                    document.getElementsByName("em_tickets[100][spaces]")[0].value = volunteer_self3_discount;
                    document.getElementsByName("em_tickets[101][spaces]")[0].value = volunteer_self1_discount;
                } else if (document.getElementsByName("em_tickets[39][spaces]").length > 0) {
                    document.getElementsByName("em_tickets[24][spaces]")[0].value = room_2_adult_2_kid;
                    document.getElementsByName("em_tickets[25][spaces]")[0].value = room_1_adult; 
                    document.getElementsByName("em_tickets[26][spaces]")[0].value = room_2_adult_1_kid; 
                    document.getElementsByName("em_tickets[27][spaces]")[0].value = room_3_adult; 
                    document.getElementsByName("em_tickets[28][spaces]")[0].value = room_1_adult_3_kid;
                    document.getElementsByName("em_tickets[29][spaces]")[0].value = room_2_adult; 
                    document.getElementsByName("em_tickets[30][spaces]")[0].value = room_1_adult_2_kid;  
                    document.getElementsByName("em_tickets[31][spaces]")[0].value = room_1_adult_1_kid;  
                    document.getElementsByName("em_tickets[32][spaces]")[0].value = place_in_double_room;
                    document.getElementsByName("em_tickets[33][spaces]")[0].value = place_in_triple_room;
    
                    document.getElementsByName("em_tickets[34][spaces]")[0].value = ticket_3_day_adult;
                    document.getElementsByName("em_tickets[35][spaces]")[0].value = ticket_1_day_adult;
                    document.getElementsByName("em_tickets[36][spaces]")[0].value = ticket_3_day_kid;
                    document.getElementsByName("em_tickets[37][spaces]")[0].value = ticket_1_day_kid;
    
                    document.getElementsByName("em_tickets[38][spaces]")[0].value = transportation;
                    
                    document.getElementsByName("em_tickets[42][spaces]")[0].value = transportation_discount;
                    document.getElementsByName("em_tickets[43][spaces]")[0].value = student_discount;
                    document.getElementsByName("em_tickets[44][spaces]")[0].value = volunteer_3_discount;
                    document.getElementsByName("em_tickets[45][spaces]")[0].value = volunteer_2_discount;
                    document.getElementsByName("em_tickets[102][spaces]")[0].value = volunteer_self3_discount;
                    document.getElementsByName("em_tickets[103][spaces]")[0].value = volunteer_self1_discount;
                } else if (document.getElementsByName("em_tickets[91][spaces]").length > 0) {
                    document.getElementsByName("em_tickets[94][spaces]")[0].value = ticket_3_day_adult;
                    document.getElementsByName("em_tickets[95][spaces]")[0].value = ticket_1_day_adult;
                    document.getElementsByName("em_tickets[96][spaces]")[0].value = ticket_3_day_kid;
                    document.getElementsByName("em_tickets[97][spaces]")[0].value = ticket_1_day_kid;
                    document.getElementsByName("em_tickets[98][spaces]")[0].value = volunteer_self3_discount;
                    document.getElementsByName("em_tickets[99][spaces]")[0].value = volunteer_self1_discount;
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

        var FIRST_RUN = true;
        var ROOM_PLACE_IN_DOUBLE;
        var ROOM_PLACE_IN_TRIPLE;
        var ROOM_DOUBLE;
        var ROOM_TRIPLE;
        var ROOM_SINGLE;
        var ROOM_FAMILY;
        var ROOM_NONE;
        var BED_TWIN;
        var BED_DOUBLE;

        function initGlobals() {
            if (!FIRST_RUN) {
                return;
            }
            FIRST_RUN = false;
            var roomType = document.getElementsByName("room_type");
            if (roomType.length > 0) {
                ROOM_PLACE_IN_DOUBLE = roomType[0].options[0].text;
                ROOM_PLACE_IN_TRIPLE = roomType[0].options[1].text;
                ROOM_DOUBLE = roomType[0].options[2].text;
                ROOM_TRIPLE = roomType[0].options[3].text;
                ROOM_SINGLE = roomType[0].options[4].text;
                ROOM_FAMILY = roomType[0].options[5].text;
                ROOM_NONE = roomType[0].options[6].text;
            }

            var bedType = document.getElementsByName("bed_type");
            if (bedType.length > 0) {
                BED_TWIN = bedType[0].options[0].text;
                BED_DOUBLE = bedType[0].options[1].text;
            }
            
            var shabbatRoom = document.getElementsByName("shabbat_room");
            if (shabbatRoom.length > 0) {
                SHABBAT_NA = shabbatRoom[0].options[0].text;
                SHABBAT_NO = shabbatRoom[0].options[1].text;
                SHABBAT_YES = shabbatRoom[0].options[2].text;
            }

            var childProgram = document.getElementsByName("child_program");
            if (childProgram.length > 0) {
                CHILD_SHABBAT_NA = childProgram[0].options[0].text;
                CHILD_SHABBAT_NO = childProgram[0].options[1].text;
                CHILD_SHABBAT_YES = childProgram[0].options[2].text;
                CHILD_NONE = childProgram[0].options[3].text;
            }
        }
        
        function updateComboBox(name, types) {
            var curType = document.getElementsByName(name)[0];
            var i;

            if (types.length == curType.options.length) {
                var updateNeeded = false; 
                for (i = curType.options.length - 1 ; i >= 0 ; i--) {
                    if (curType.options[i].text != types[i]) {
                        updateNeeded = true;
                    }
                }
                if (!updateNeeded)
                    return;
            }
                
            for (i = curType.options.length - 1 ; i >= 0 ; i--) {
                curType.remove(i);
            } 

            for (i = 0 ; i < types.length ; i++) {
                curType.options[curType.options.length] = new Option(types[i]);
            }
            curType.value = types[0];
            curType.selectedIndex = 0;
        }

        function updateRoomType(types) {
            updateComboBox("room_type", types);
        }
    
        $('select').change(function() {
            var adults = null; 
            var kids = null;
            var accomodation = null;
            var roomType = null;
            var self_accomodation = false;
            
            var els = document.getElementsByName("em_tickets[4][spaces]");
            if (els.length > 0) {
                adults = els[0];
                kids = document.getElementsByName("em_tickets[2][spaces]")[0];
            } else {
                els = document.getElementsByName("em_tickets[39][spaces]");
                if (els.length > 0) {
                    adults = els[0];
                    kids = document.getElementsByName("em_tickets[40][spaces]")[0];
                } else {
                    els = document.getElementsByName("em_tickets[91][spaces]");
                    if (els.length > 0) {
                        adults = els[0];
                        kids = document.getElementsByName("em_tickets[92][spaces]")[0];
                        self_accomodation = true;
                    }
                }
            }
            
            els = document.getElementsByName("accomodation_type");
            if (els.length > 0) {
                accomodation = els[0];
            }
            els = document.getElementsByName("room_type");
            if (els.length > 0) {
                roomType = els[0];
            }
            
            if (adults == null) {
                return;
            }
            
            initGlobals();

            if (!self_accomodation) {
                if (accomodation && accomodation.selectedIndex > 0) {
                    document.getElementsByName("room_label")[0].style.display = "none";
                    document.getElementsByClassName("input-field-bed_type")[0].style.display = "none";
                    document.getElementsByClassName("input-field-bus_needed")[0].style.display = "none";
                    document.getElementsByClassName("input-field-shabbat_room")[0].style.display = "none";
    
                    updateRoomType([ROOM_NONE]);
                    document.getElementsByClassName("input-field-room_type")[0].style.display = "none";
                } else {
                    document.getElementsByClassName("input-field-room_type")[0].style.display = "block";
                    document.getElementsByClassName("input-field-bed_type")[0].style.display = "block";
                    document.getElementsByClassName("input-field-bus_needed")[0].style.display = "block";
    
                    document.getElementsByName("room_label")[0].style.display = "none";
                    if (kids.selectedIndex > 0) {
                        updateRoomType([ROOM_FAMILY]);
                    } else if (adults.selectedIndex == 1) {
                        updateRoomType([ROOM_PLACE_IN_DOUBLE, ROOM_PLACE_IN_TRIPLE, ROOM_SINGLE]);
                    } else if (adults.selectedIndex == 2) {
                        updateRoomType([ROOM_PLACE_IN_TRIPLE, ROOM_DOUBLE]);
                    } else if (adults.selectedIndex == 3) {
                        updateRoomType([ROOM_TRIPLE]);
                    } else if (adults.selectedIndex == 4) {
                        updateRoomType([ROOM_PLACE_IN_TRIPLE, ROOM_DOUBLE]);
                    } else if (adults.selectedIndex == 5) {
                        updateRoomType([ROOM_PLACE_IN_DOUBLE, ROOM_PLACE_IN_TRIPLE]);
                    } else if (adults.selectedIndex == 6) {
                        updateRoomType([ROOM_DOUBLE, ROOM_TRIPLE]);
                    } else {
                        updateRoomType([ROOM_FAMILY]);
                    }
    
                    if (roomType.value == ROOM_PLACE_IN_TRIPLE) {
                        document.getElementsByClassName("input-field-bed_type")[0].style.display = "none";
                        updateComboBox("bed_type", [BED_DOUBLE]);
                    } else if (roomType.value == ROOM_SINGLE) {
                        document.getElementsByClassName("input-field-bed_type")[0].style.display = "none";
                        updateComboBox("bed_type", [BED_TWIN]);
                    } else {
                        updateComboBox("bed_type", [BED_TWIN, BED_DOUBLE]);
                    }
    
                    if ((roomType.value == ROOM_PLACE_IN_DOUBLE) || (roomType.value == ROOM_PLACE_IN_TRIPLE)) {
                        document.getElementsByName("room_label")[0].style.display = "block";
                        document.getElementsByClassName("input-field-shabbat_room")[0].style.display = "block";
                        updateComboBox("shabbat_room", [SHABBAT_NO, SHABBAT_YES]);
                    } else {
                        document.getElementsByName("room_label")[0].style.display = "none";
                        document.getElementsByClassName("input-field-shabbat_room")[0].style.display = "none";
                        updateComboBox("shabbat_room", [SHABBAT_NA]);
                    }
                }
            }

            if (kids) {
                if (kids.selectedIndex > 0) {
                    document.getElementsByName("child_label")[0].style.display = "block";
                    document.getElementsByClassName("input-field-child_program")[0].style.display = "block";
                    updateComboBox("child_program", [CHILD_SHABBAT_NO, CHILD_SHABBAT_YES, CHILD_NONE]);
                } else {
                    document.getElementsByName("child_label")[0].style.display = "none";
                    document.getElementsByClassName("input-field-child_program")[0].style.display = "none";
                    updateComboBox("child_program", [CHILD_SHABBAT_NA]);
                }
            } 

        }).change();
    <?php
	}     
}

EM_Paypal_Limmud::init();
