<?php

/**
 * custom meta box
 * https://github.com/mustafauysal/post-meta-box-order/blob/master/post-meta-box-order.php
 * https://developer.wordpress.org/reference/functions/do_meta_boxes/
 *
 */


namespace Admin\JDAIO;

use Jerry_Divi_AIO;

defined('ABSPATH') || exit;


class MetaBox extends Jerry_Divi_AIO
{


    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'jdaio_remove_metabox'], 100);

        add_filter('get_user_option_meta-box-order_post', [$this, 'jdaio_reorder_post_metabox'], 99);
        add_filter('get_user_option_meta-box-order_page', [$this, 'jdaio_reorder_post_metabox'], 99);
        add_filter('get_user_option_meta-box-order_product', [$this, 'jdaio_reorder_post_metabox'], 99);

        //add_filter('default_hidden_meta_boxes', [$this, 'hide_meta_box'], 10, 2);

        //預設關閉文章摘要
        add_action('user_register', [$this, 'jdaio_save_on_registration'], 10, 1);
        add_action('admin_init', [$this, 'jdaio_flush_metabox_setting'], 10, 1);
    }


    function jdaio_remove_metabox()
    {

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
        if (!DEV_ENV) {
            //POST
            remove_meta_box(
                'postcustom',
                'post',
                'normal'
            );
            remove_meta_box(
                'commentsdiv', //留言
                'post',
                'normal'
            );
            remove_meta_box(
                'formatdiv',
                'post',
                'side'
            );
            remove_meta_box(
                'slider_revolution_metabox',
                'post',
                'normal'
            );

            //PAGE
            remove_meta_box(
                'postcustom',
                'page',
                'normal'
            );
            remove_meta_box(
                'pageparentdiv',
                'page',
                'side'
            );


            //SHOP_ORDER
            remove_meta_box(
                'postcustom',
                'shop_order',
                'normal'
            );
            remove_meta_box(
                'woocommerce-order-downloads',
                'shop_order',
                'normal'
            );

            //PRODUCT
            remove_meta_box(
                'postcustom',
                'product',
                'normal'
            );
        }
    }

    function jdaio_reorder_post_metabox($order)
    {
        $post_type = get_post_type();

        //join=將 array轉為字串
        switch ($post_type) {
            case 'post':
                return array(
                    'normal'   => join(",", array(
                        'et_pb_layout',
                        'postimagediv',
                        'tsf-inpost-box',
                        'slider_revolution_metabox',

                    )),
                    'side'     => join(",", array(
                        'submitdiv',
                        'categorydiv',
                        'tagsdiv-post_tag',
                        'et_settings_meta_box',
                    )),
                    'advanced' => join(",", array(
                        'postexcerpt',
                        'authordiv',
                        'pageparentdiv',
                    )),
                );
                break;
            case 'page':
                return array(
                    'normal'   => join(",", array(
                        'et_pb_layout',
                        'postimagediv',
                        'tsf-inpost-box',
                        'slider_revolution_metabox',

                    )),
                    'side'     => join(",", array(
                        'submitdiv',
                        'categorydiv',
                        'tagsdiv-post_tag',
                        'et_settings_meta_box',
                    )),
                    'advanced' => join(",", array(
                        'postexcerpt',
                        'authordiv',
                        'pageparentdiv',
                    )),
                );
                break;
            case 'product':
                return array(
                    'normal'   => join(",", array(
                        'et_pb_layout',
                        'woocommerce-product-data',
                        'postimagediv',
                        'woocommerce-product-images',
                        'tsf-inpost-box',
                        'slider_revolution_metabox',

                    )),
                    'side'     => join(",", array(
                        'submitdiv',
                        'categorydiv',
                        'tagsdiv-post_tag',
                        'et_settings_meta_box',
                    )),
                    'advanced' => join(",", array(
                        'postexcerpt',
                        'authordiv',
                        'pageparentdiv',
                    )),
                );
                break;

            default:
                # code...
                break;
        }
    }


    function hide_meta_box($hidden, $screen)
    {
        //make sure we are dealing with the correct screen
        if (('post' == $screen->base) || ('page' == $screen->base)) {
            //lets hide everything
            $hidden = ['postexcerpt', 'slugdiv', 'postcustom', 'trackbacksdiv', 'commentstatusdiv', 'commentsdiv', 'authordiv', 'revisionsdiv'];
            //$hidden[] ='my_custom_meta_box';//for custom meta box, enter the id used in the add_meta_box() function.
        }


        return $hidden;
    }

    function jdaio_save_on_registration($user_id)
    {


        //update_user_meta($user_id, 'closedpostboxes_post', $hide_metabox);
        update_user_meta($user_id, 'metaboxhidden_post', self::$hidden_metabox);
        update_user_meta($user_id, 'closedpostboxes_post', self::$close_metabox);
        update_user_meta($user_id, 'metaboxhidden_page', self::$hidden_metabox);
        update_user_meta($user_id, 'closedpostboxes_page', self::$close_metabox);
        update_user_meta($user_id, 'metaboxhidden_product', self::$hidden_metabox);
        update_user_meta($user_id, 'closedpostboxes_product', self::$close_metabox);
    }
    function jdaio_flush_metabox_setting()
    {
        if (FLUSH_METABOX == true) {
            $users = get_users();
            foreach ($users as $user) {
                $user_id = $user->data->ID;
                update_user_meta($user_id, 'metaboxhidden_post', self::$hidden_metabox);
                update_user_meta($user_id, 'closedpostboxes_post', self::$close_metabox);
                update_user_meta($user_id, 'metaboxhidden_page', self::$hidden_metabox);
                update_user_meta($user_id, 'closedpostboxes_page', self::$close_metabox);
                update_user_meta($user_id, 'metaboxhidden_product', self::$hidden_metabox);
                update_user_meta($user_id, 'closedpostboxes_product', self::$close_metabox);
            }
        }
    }
}
