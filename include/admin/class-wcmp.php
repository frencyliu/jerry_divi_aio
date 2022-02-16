<?php

/**
 * custom WCMP
 */

namespace Admin\JDAIO;

defined('ABSPATH') || exit;

class WCMP
{


    public function __construct()
    {
        if(class_exists('WCMp', false)){
            add_filter( 'wcmp_vendor_dashboard_header_right_panel_nav', [ $this, 'wcmp_rm_wpadmin_vendor_backend' ], 999, 1 );
            add_filter( 'wcmp_show_page_title', 'wcmp_hide_page_title', 999, 1 );
            add_action( 'after_sold_by_text_shop_page', [$this, 'wcmp_show_vendor_img_at_product_page' ], 999 );
        }
    }

    function wcmp_rm_wpadmin_vendor_backend($panel_nav){
        unset($panel_nav['wp-admin']);
        return $panel_nav;
    }
    function wcmp_hide_page_title(){
        return false;
    }


    function wcmp_show_vendor_img_at_product_page($vendor){
        $img_id = get_user_meta($vendor->id);
        $src = wp_get_attachment_image_url($img_id['_vendor_image'][0]);
        if($src == false){
            $src = get_avatar_url($vendor->id);
        }

        //var_dump($img_id['_vendor_image']);
        echo '<a class="wcmp_vendor_img" href="' . $vendor->permalink . '"><img src="' . $src . '" /></a>';
    }
}
