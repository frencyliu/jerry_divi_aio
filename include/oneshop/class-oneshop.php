<?php

/**
 * One Shop
 */

namespace OneShop\JDAIO;

use Jerry_Divi_AIO;

defined('ABSPATH') || exit;

if (ONESHOP) {

    class OneShop_Mode extends Jerry_Divi_AIO
    {
        public function __construct()
        {



            add_action('wp_enqueue_scripts', [$this, 'jdaio_enqueue_front_css'], 101);

            add_filter('body_class', [$this, 'jdaio_add_bodyclass']);

            //Remove single page
            //add_filter('woocommerce_register_post_type_product', [$this, 'jdaio_hide_product_page'], 12, 1);

            //override woocommerce template
            //https://www.skyverge.com/blog/override-woocommerce-template-file-within-a-plugin/
            //add_filter( 'woocommerce_locate_template', [$this, 'jdaio_oneshop_override_woocommerce_template' ], 20, 3 );


        }

        public function jdaio_enqueue_front_css()
        {
            wp_enqueue_style('Jerry_Divi_AIO ONESHOP front css', plugins_url('/../../assets/css/jdaio_front_oneshop.css', __FILE__));
        }

        public function jdaio_add_bodyclass($classes)
        {
            $oneshop_class = ['jdaio_oneshop'];
            return array_merge($classes, $oneshop_class);
        }

        //Remove single page
        public function jdaio_hide_product_page($args)
        {
            $args["publicly_queryable"] = false;
            $args["public"] = false;
            return $args;
        }

        function get_plugin_abs_path()
        {

            // gets the absolute path to this plugin directory

            return untrailingslashit(plugin_dir_path(__FILE__));
        }

        function jdaio_oneshop_override_woocommerce_template($template, $template_name, $template_path)
        {
            global $woocommerce;

            $_template = $template;

            if (!$template_path) $template_path = $woocommerce->template_url;

            $plugin_path  = $this->get_plugin_abs_path() . '/woocommerce/';
            //var_dump($plugin_path);
            // Look within passed path within the theme - this is priority
            $template = locate_template(

                array(
                    $template_path . $template_name,
                    $template_name
                )
            );

            // Modification: Get the template from this plugin, if it exists
            if (!$template && file_exists($plugin_path . $template_name))
                $template = $plugin_path . $template_name;

            // Use default template
            if (!$template)
                $template = $_template;

            // Return what we found
            return $template;
        }


    }


    new OneShop_Mode();
}
