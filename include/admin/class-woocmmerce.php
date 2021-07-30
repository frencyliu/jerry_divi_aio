<?php

/**
 * customise admin
 */

namespace Admin\JAIO;

use Jerry_AIO;

defined( 'ABSPATH' ) || exit;

class Woocommerce extends Jerry_AIO{


    public function __construct() {
        //change woocommerce default recipient
        add_filter('woocommerce_email_recipient_new_order', [ $this, 'my_email_heading_customisation_function_ent' ], 99999, 2);
        add_filter( 'woocommerce_email_recipient_cancelled_order', [ $this, 'quadlayers_add_email_recipient_to' ], 9999, 3 );
        //---- woocommerce ----//
        add_filter('woocommerce_customer_meta_fields', [$this, 'jaio_remove_shipping_fields'], 999);
        //remove woocommerce setting tab
        add_filter('woocommerce_settings_tabs_array', [$this, 'jaio_remove_woocommerce_setting_tabs'], 200, 1);
    }


    public function my_email_heading_customisation_function_ent($recipient, $order)
    {

        global $woocommerce;


        $recipient = "123@email.cz";

        return $recipient;
    }


    public function quadlayers_add_email_recipient_to($email_recipient, $email_object, $email)
    {
        $email_recipient .= ', 456@mail.com';
        return $email_recipient;
    }

    function jaio_remove_woocommerce_setting_tabs($tabs)
    {
        $user_level = $this->jaio_get_current_user_level();
        switch ($user_level) {
            case 0:
                # do nothing
                break;
            default:
                unset($tabs['integration']);
                unset($tabs['advanced']);
                break;
        }
        return $tabs;
    }

    //停用帳單部分欄位跟shipping欄位
    function jaio_remove_shipping_fields($show_fields)
    {
        unset($show_fields['billing']['fields']['billing_last_name']);
        unset($show_fields['billing']['fields']['billing_address_2']);
        unset($show_fields['billing']['fields']['billing_city']);
        unset($show_fields['billing']['fields']['billing_country']);
        unset($show_fields['billing']['fields']['billing_state']);
        unset($show_fields['billing']['fields']['billing_email']);
        unset($show_fields['shipping']);
        //var_dump($show_fields);
        return $show_fields;
    }
}

