<?php

/**
 * custom meta box
 * Avada slide's post type: slide
 */

namespace Admin\JAIO;

use Jerry_AIO;

defined( 'ABSPATH' ) || exit;


class MetaBox extends Jerry_AIO{


    public function __construct() {
        //add_action('do_meta_boxes', [ $this, 'remove_thumbnail_box' ], 7);
        add_action('add_meta_boxes', [ $this, 'remove_thumbnail_box' ], 7);
    }



    function remove_thumbnail_box() {

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

        remove_meta_box( 'postimagediv','slide','side' );
        add_meta_box( 'postimagediv','slide image','post_thumbnail_meta_box', 'slide', 'advanced', 'high' );
    }
}