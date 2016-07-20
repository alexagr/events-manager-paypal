<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Events Manager PayPal
 * Description:       PayPal extension for Events Manager plugin
 * Version:           1.0
 * Author:            Alex Agranov
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

define('EM_PAYPAL_VERSION', 1.0);

class EM_Paypal {

    public static function init() {

        //check that Events Manager is installed
        if (!defined('EM_VERSION')) {
            add_action('admin_notices', array(__CLASS__, 'em_install_warning'));
            add_action('network_admin_notices', array(__CLASS__, 'em_install_warning'));
            return false;
        }

        //check that PayPal IPN for WordPress is installed
        if (!defined('PIW_PLUGIN_URL')) {
            add_action('admin_notices', array(__CLASS__, 'ipn_install_warning'));
            add_action('network_admin_notices', array(__CLASS__, 'ipn_install_warning'));
            return false;
        }

        if (is_admin()) {
            include('empp-admin.php');
        }
        include('empp-email.php');
        include('empp-ipn.php');
        include('empp-discount.php');
        include('empp-misc.php');
    }
        

    public static function em_install_warning() {
        ?>
        <div class="error"><p>Please make sure you install Events Manager as well. You can search and install this plugin from your plugin installer or download it <a href="http://wordpress.org/extend/plugins/events-manager/">here</a>. <em>Only admins see this message</em></p></div>
        <?php
    }

    public static function ipn_install_warning() {
        ?>
        <div class="error"><p>Please make sure you install Paypal IPN for WordPress as well. You can search and install this plugin from your plugin installer or download it <a href="http://wordpress.org/plugins/paypal-ipn/">here</a>. <em>Only admins see this message</em></p></div>
        <?php
    }
}

add_action('plugins_loaded', 'EM_Paypal::init');

?>