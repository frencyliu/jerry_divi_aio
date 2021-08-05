<?php

/**
 * custom meta box
 */

namespace Admin\JDAIO;

use Jerry_Divi_AIO;

defined( 'ABSPATH' ) || exit;


class MetaBox extends Jerry_Divi_AIO{


    public function __construct() {
        add_action('add_meta_boxes', [ $this, 'jdaio_remove_metabox' ], 100);
    }



    function jdaio_remove_metabox() {

        /*remove_meta_box(
            string $id, //require
            string|array|WP_Screen $screen, //require, like post type
            string $context //require, 'normal', 'side', and 'advanced'
        )
        add_meta_box(
            string $id, //require
            string $title, //require
            callable $callback, //require
            string|array|WP_Screen $screen = null, //require, like post type
            string $context = 'advanced', //require, 'normal',(右下) 'side',(右上) and 'advanced'
            string $priority = 'default', // 'high', 'core', 'default', or 'low'.
            array $callback_args = null
        )
        */

        //POST
        remove_meta_box(
            'postcustom', //自訂欄位
            'post', //require, like post type
            'normal' //require, 'normal', 'side', and 'advanced'
        );
        remove_meta_box(
            'commentsdiv', //留言
            'post', //require, like post type
            'normal' //require, 'normal', 'side', and 'advanced'
        );
        remove_meta_box(
            'formatdiv', //文章格式
            'post', //require, like post type
            'side' //require, 'normal', 'side', and 'advanced'
        );

        //PAGE
        remove_meta_box(
            'postcustom', //自訂欄位
            'page', //require, like post type
            'normal' //require, 'normal', 'side', and 'advanced'
        );

        //SHOP_ORDER
        remove_meta_box(
            'postcustom', //自訂欄位
            'shop_order', //require, like post type
            'normal' //require, 'normal', 'side', and 'advanced'
        );
        remove_meta_box(
            'woocommerce-order-downloads', //自訂欄位
            'shop_order', //require, like post type
            'normal' //require, 'normal', 'side', and 'advanced'
        );

    }
}