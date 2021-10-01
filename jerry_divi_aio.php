<?php

/**
 * Plugin Name
 *
 * @package           Jerry-Divi-AIO
 * @author            Jerry Liu
 * @copyright         2021 YC-TECH
 *
 * @wordpress-plugin
 * Plugin Name:       Jerry-Divi-AIO
 * Plugin URI:
 * Description:       EZ update all the site
 * Version:           1.1.0
 * Requires at least: 5.5.0
 * Requires PHP:      7.3
 * Author:            Jerry Liu
 * Author URI:
 * Text Domain:       Jerry_Divi_AIO
 */


/**
 *
 *
 *
 */


defined('ABSPATH') or die('hey, you can\'t see this.');




if (!class_exists('Jerry_Divi_AIO')) {
    class Jerry_Divi_AIO
    {

        static $level_0 = ['administrator'];
        static $level_1 = ['designer'];
        static $level_2 = ['shop_manager_super']; //可以新增用戶
        static $level_3 = ['shop_manager', 'editor', 'author', 'translator']; //不可以新增
        static $current_user_level = 1;
        //隱藏的用戶
        static $hide_user = ['JerryLiu', 'KarenShen', 'Emily'];






        public function __construct()
        {
            //add_action('admin_head', [ $this, 'test' ]);
            add_action('init', [$this, 'jdaio_get_current_user_level']);
            add_action('init', [$this, 'jdaio_set_default']);

            if (!defined('DEV_ENV')) define('DEV_ENV', false);
            if (!defined('COMMENTS_OPEN')) define('COMMENTS_OPEN', false);
            if (!defined('PROJECT_OPEN')) define('PROJECT_OPEN', false);
            if (!defined('FLUSH_METABOX')) define('FLUSH_METABOX', false);
            if (!defined('ONESHOP')) define('ONESHOP', false);
            if (!defined('FA_ENABLE')) define('FA_ENABLE', true);
            if (!defined('FLIPSTER_ENABLE')) define('FLIPSTER_ENABLE', false);
            //是否啟用擴充模組
            if (!defined('JDAIO_EXTENSION')) define('JDAIO_EXTENSION', false);



            //預設wp statistics 最多保存365天數據
            add_filter('wp_statistics_option_schedule_dbmaint', function () {
                return 'on';
            });
            add_filter('wp_statistics_option_schedule_dbmaint_days', function () {
                return '365';
            });

            //i18n
            add_action( 'init', [ $this, 'jdaio_i18n' ] );

            //Override Woocommerce template
            add_filter('woocommerce_locate_template', [$this, 'jdaio_override_woocommerce_template'], 10, 3);
        }

        public function jdaio_wps_setting($options)
        {
            //var_dump($options);
        }

        public function jdaio_set_default()
        {
            //修改預設資料
            update_option('thumbnail_size_w', 0);
            update_option('thumbnail_size_h', 0);
            update_option('medium_size_w', 0);
            update_option('medium_size_h', 0);
            update_option('large_size_w', 5000);
            update_option('large_size_h', 20000);
            update_option('thumbnail_crop', '');

            //WC
            update_option('woocommerce_allow_tracking', 'no');
            update_option('woocommerce_show_marketplace_suggestions', 'no');

            //破解divi mega menu pro
            update_option('divilife_edd_divimegapro_license_status', 'valid'); // by jerryliu


            //修改WP Statistics Read capability權限
            add_filter("wp_statistics_option_read_capability", function () {
                return 'read';
            }, 99, 1);
        }


        public function jdaio_get_current_user_level()
        {
            /*
             * 限制載入CSS跟JS的角色 Admin除外
             */
            if (!is_user_logged_in()) return;
            $user = wp_get_current_user();
            if ($user->roles[0] == 'administrator') {
                self::$current_user_level = 0;
                return;
            }
            $user_levels[0] = self::$level_0;
            $user_levels[1] = self::$level_1;
            $user_levels[2] = self::$level_2;
            $user_levels[3] = self::$level_3;
            foreach ($user_levels as $key => $user_level) {
                if (array_intersect($user_level, $user->roles)) {
                    self::$current_user_level = $key;
                    //return $key;
                }
            }
        }


        public function jdaio_hide_user($user_search)
        {
            global $current_user;
            $username = $current_user->user_login;
            $hide_users = self::$hide_user;

            if (!in_array($username, $hide_users)) {
                global $wpdb;
                $text = 'WHERE 1=1 ';
                foreach ($hide_users as $key => $hide_user) {
                    $text .= "AND {$wpdb->users}.user_login != '$hide_user' ";
                }
                $user_search->query_where = str_replace('WHERE 1=1', $text, $user_search->query_where);
            }
        }

        function get_plugin_abs_path()
        {

            // gets the absolute path to this plugin directory
            return untrailingslashit(plugin_dir_path(__FILE__));
        }

        function jdaio_override_woocommerce_template($template, $template_name, $template_path)
        {
            global $woocommerce;

            $_template = $template;

            if (!$template_path) $template_path = $woocommerce->template_url;

            $plugin_path  = $this->get_plugin_abs_path() . '/templates/woocommerce/';

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

        function jdaio_i18n() {
            load_plugin_textdomain( 'Jerry_Divi_AIO', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }



        function activate()
        {
            flush_rewrite_rules();
        }
        function deactivate()
        {
            flush_rewrite_rules();
        }
    }
}

if (class_exists('Jerry_Divi_AIO')) {
    $Jerry_Divi_AIO = new Jerry_Divi_AIO();
}

require_once(__DIR__ . '/include/admin/class-admin.php');
require_once(__DIR__ . '/include/sync/sync.php');
require_once(__DIR__ . '/include/shortcode/shortcode.php');
require_once(__DIR__ . '/include/oneshop/class-oneshop.php');
require_once(__DIR__ . '/include/extensions/class-extension.php');


new Custom_Admin();

/**
 * Activate the plugin.
 */
register_activation_hook(__FILE__, array($Jerry_Divi_AIO, 'activate'));

/**
 * Deactivation hook.
 */
register_deactivation_hook(__FILE__, array($Jerry_Divi_AIO, 'deactivate'));
