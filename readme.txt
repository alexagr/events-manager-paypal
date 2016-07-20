=== Events Manager Paypal ===
Contributors: alex.agranov@gmail.com
Requires at least: 3.8
Stable tag: 1.0
Tested up to: 4.5.1

PayPal extension for Events Manager plugin.

== Description ==

This plugin extends Events Manager plugin functionality to support payments via PayPal.
The following workflow is implemented:

   * New booking is created (by customer) in 'Pending' state
   * Booking is reviewed (by administrator) and its state is changed to 'Awaiting Payment'
   * An e-mail is sent to the customer with a link to PayPal site
   * Customer pays for the booking
   * Booking state is updated to 'Approved' and PayPal transaction ID is recorded

In order to use this plugin do the following:
   * set Events > Settings > Bookings > General Options > Approval Required to 'Yes'
   * configure PayPal account under Events > Settings > General > PayPal Options
   * configure payment e-mail to be sent to user under Events > Settings > Emails >
     PayPal Email Templates; use #_PAYPAL placeholder as part of href link
   * when new booking is created (under Events > Bookings) click 'Edit/View' and change 
     booking status to 'Awaiting Payment' (but NOT 'Awaiting Online Payment' - this one 
     is reserved for Events Manager Pro); proper e-mail will be sent to customer
     with a link to PayPal
   * when customer pays for the booking transaction will appear under PayPal IPN
     (and Events Manager Pro's Transactions table if Pro is installed) and booking
     state will change to 'Approved' 
  
In addition to the above a few additional "goodies" are added:
   * new 'Admin Discount' field in 'Modify Booking' dialog that enables adding 
     arbitrary discount to the booking
   * Events > Settings > Miscellaneous Options (for Limmud) > Show Ticket Price
     controls whether ticket price is shown when reservation is made
   * Events > Settings > Miscellaneous Options (for Limmud) > Count Admin Tickets
     controls whether tickets with zero or negative price and/or tickets available
     to registered users only are counted as total and/or pending (for both booking
     and event); this is needed for "total spaces" cut-off to work correctly
     when "special tickets" are used
     

== Dependencies ==

   * [Events Manager](http://wp-events-plugin.com/)
   * [PayPal IPN for WordPress](https://www.angelleye.com/product/paypal-ipn-wordpress/)
