<?php

/**
 * customise admin
 */

//namespace Admin\JDAIO;

defined('ABSPATH') || exit;

require __DIR__ . '/../../vendor/autoload.php';

use ODS\Metabox;
use ODS\Option;

/* ODS\Metabox Usage
 * https://github.com/oberonlai/wp-metabox
 * To create a metabox, first instantiate an instance of `Metabox`.  The class takes one argument, which is an associative array.  The keys to the array are similar to the arguments provided to the [add_meta_box](https://developer.wordpress.org/reference/functions/add_meta_box/) WordPress function; however, you don't provide `callback` or `callback_args`.
 */

class Custom_Admin extends Jerry_Divi_AIO
{

    public function __construct()
    {
        //---- CSS, JS ----//
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_css'], 100);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_css'], 100);
        add_action('admin_footer', [$this, 'enqueue_admin_js'], 100);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_js'], 100);

        //---- 停用 heartbeat ----//
        //add_action('init', [$this, 'jdaio_stop_heartbeat'], 100);

        //---- 註冊設定 ----//
        add_action('admin_init', [$this, 'jdaio_sync_data']);
        add_action('init', [$this, 'jdaio_setting']);

        //---- user ----//
        add_filter('pre_option_default_role', [$this, 'jdaio_change_default_role'], 100);

        //---- Admin ----//
        remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
        add_filter('use_block_editor_for_post', '__return_false');

        //---- 在WordPress後台新增選單 ----//
        add_action('admin_menu', [$this, 'jdaio_amp_setting'], 97);
        add_action('admin_menu', [$this, 'wd_admin_menu_rename'], 98);
        //add_filter('custom_menu_order', [$this, 'custom_menu_order'], 99);
        add_filter('menu_order', [$this, 'custom_menu_order'], 100, 1);

        //add_filter('menu_order', array($this, 'jdaio_submenu_order'), 10099);

        //---- admin_bar_menu ----//
        add_action('wp_before_admin_bar_render', [$this, 'jdaio_admin_bar_render']);

        //---- Login page ----//
        add_action('login_head', [$this, 'jdaio_change_login_logo']);

        //---- disable gutenberg ----//
        add_filter('use_block_editor_for_post', '__return_false');

        //---- Profile page ----//
        add_filter('user_contactmethods', [$this, 'jdaio_profile_fields'], 100);

        //---- Meta Box ----//
        remove_action('welcome_panel', 'wp_welcome_panel');
        add_action('wp_dashboard_setup', [$this, 'jdaio_remove_dashboard_widgets']);

        //---- tool bar ----//
        //add_action( 'admin_bar_menu', [ $this, 'jdaio_toolbar' ], 99 );

        //login redirect
        add_filter('login_redirect', [$this, 'jdaio_login_redirect'], 100, 3);

        //add Social login at woocommerce register form
        //woocommerce_login_form_end | woocommerce_login_form_start
        add_action('woocommerce_login_form_end', [$this, 'jdaio_add_register_form_field'], 100);

        //change favicon
        add_action('wp_head', [$this, 'jdaio_add_wp_head']);
        add_action('admin_head', [$this, 'jdaio_add_wp_head']);

        //remove hook
        add_action('admin_init', [$this, 'jdaio_remove_filters'], 100);

        //add admin footer
        add_action('admin_footer', [$this, 'jdaio_add_admin_footer']);

        add_action('after_setup_theme', [$this, 'remove_admin_bar']);

        if (class_exists('Bogo_POMO', false)) {
            add_filter('bogo_localizable_post_types', [$this, 'my_localizable_post_types'], 10, 1);
        }

        //Disable comment

        if (!COMMENT_OPEN) {
            add_action('init', [$this, 'jdaio_disable_comments_admin_bar']);
            add_action('admin_init', [$this, 'jdaio_disable_comments_post_types_support']);
            add_filter('comments_open', [$this, 'jdaio_disable_comments_status'], 20, 2);
            add_filter('pings_open', [$this, 'jdaio_disable_comments_status'], 20, 2);
            add_filter('comments_array', [$this, 'jdaio_disable_comments_hide_existing_comments'], 10, 2);
        }
    }

    /**
     * Support custom post type with bogo.
     * @param array $ locallyizable Supported post types.
     */

    public function my_localizable_post_types($localizable)
    {
        $args = array(
            'public' => true,
            '_builtin' => false,
        );
        $custom_post_types = get_post_types($args);
        return array_merge($localizable, $custom_post_types);
    }

    //li.uk-open選單會打開
    public function jdaio_amp_setting()
    {



        switch (self::$current_user_level) {
            case 0:
                # do nothing
                break;
            case 1:
                $this->jdaio_remove_menu_page_level_1();
                break;
            case 2:
                $this->jdaio_remove_menu_page_level_2();
                break;
            default:
                $this->jdaio_remove_menu_page_level_2();
                break;
        }

        //訂單中心
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                '訂單中心',
                '訂單中心',
                'edit_shop_orders',
                'edit.php?post_type=shop_order',
                '',
                'dashicons-cart', //icon
                null
            );

            if (class_exists('WC_Order_Export_Admin', false)) {
                add_submenu_page(
                    'edit.php?post_type=shop_order',
                    '匯出訂單',
                    '匯出訂單',
                    'edit_shop_orders',
                    'admin.php?page=wc-order-export#segment=common',
                    '',
                    2
                );
            }

            if (class_exists('Zorem_Woocommerce_Advanced_Shipment_Tracking', false)) {
                add_submenu_page(
                    'edit.php?post_type=shop_order',
                    '批量匯入物流單號',
                    '批量匯入物流單號',
                    'edit_shop_orders',
                    'admin.php?page=woocommerce-advanced-shipment-tracking',
                    '',
                    2
                );
            }
        }

        if (class_exists('User_import_export_Review_Request', false)) {
            //用戶中心
            add_submenu_page(
                'users.php',
                '匯出會員',
                '匯出會員',
                'edit_shop_orders',
                'admin.php?page=wt_import_export_for_woo_basic_export',
                '',
                2
            );
        }

        //行銷中心
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                '行銷中心',
                '行銷中心',
                'edit_shop_orders',
                'edit.php?post_type=shop_coupon',
                '',
                'dashicons-admin-appearance', //icon
                null
            );
            add_submenu_page(
                'edit.php?post_type=shop_coupon',
                '折價券',
                '折價券',
                'edit_shop_orders',
                'edit.php?post_type=shop_coupon',
                '',
                2
            );

            if (class_exists('WooCommerce_Coupon_Generator', false)) {
                add_submenu_page(
                    'edit.php?post_type=shop_coupon',
                    '批量產生折價券',
                    '批量產生折價券',
                    'edit_shop_orders',
                    'admin.php?page=woocommerce_coupon_generator',
                    '',
                    3
                );
            }
            //if (class_exists('The_SEO_Framework\Core', false)) {
            add_submenu_page(
                'edit.php?post_type=shop_coupon',
                'SEO設定',
                'SEO設定',
                'read',
                'admin.php?page=theseoframework-settings',
                '',
                4
            );
            //}
        }else{
            add_menu_page(
                '行銷中心',
                '行銷中心',
                'read',
                'admin.php?page=theseoframework-settings',
                '',
                'dashicons-admin-appearance', //icon
                null
            );
        }

        //網站設定

        add_submenu_page(
            'jdaio_setting',
            '首頁設定',
            '首頁設定',
            'edit_shop_orders',
            'post.php?post=' . get_option('page_on_front') . '&action=edit',
            '',
            2
        );
        add_submenu_page(
            'jdaio_setting',
            '網站選單',
            '網站選單',
            'edit_shop_orders',
            'nav-menus.php',
            '',
            3
        );

        if (class_exists('EasyWPSMTP', false)) {
            add_submenu_page(
                'jdaio_setting',
                '系統發信設定',
                '系統發信設定',
                'edit_shop_orders',
                'options-general.php?page=swpsmtp_settings#smtp',
                '',
                4
            );
        }

        //網站外觀選項
        add_menu_page(
            '網站外觀選項',
            '網站外觀選項',
            'edit_theme_options',
            'customize.php?et_customizer_option_set=theme',
            '',
            'dashicons-admin-appearance', //icon
            null
        );
        add_submenu_page(
            'customize.php?et_customizer_option_set=theme',
            '進階設定',
            '進階設定',
            'edit_theme_options',
            'admin.php?page=et_divi_options',
            '',
            2
        );
        add_submenu_page(
            'customize.php?et_customizer_option_set=theme',
            '元件庫',
            '元件庫',
            'edit_theme_options',
            'edit.php?post_type=et_pb_layout',
            '',
            3
        );

        //網路商店設定
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                '網路商店設定',
                '網路商店設定',
                'edit_shop_orders',
                'admin.php?page=wc-settings',
                '',
                'dashicons-store', //icon
                null
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '運費設定',
                '運費設定',
                'edit_shop_orders',
                'admin.php?page=wc-settings&tab=shipping',
                '',
                2
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '付款方式設定',
                '付款方式設定',
                'edit_shop_orders',
                'admin.php?page=wc-settings&tab=checkout',
                '',
                3
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '帳號及隱私權設定',
                '帳號及隱私權設定',
                'edit_shop_orders',
                'admin.php?page=wc-settings&tab=account',
                '',
                4
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '訂單通知信內容設定',
                '訂單通知信內容設定',
                'edit_shop_orders',
                'admin.php?page=wc-settings&tab=email',
                '',
                5
            );

            if (class_exists('THWCFD', false)) {
                add_submenu_page(
                    'admin.php?page=wc-settings',
                    '自訂結帳表單',
                    '自訂結帳表單',
                    'edit_shop_orders',
                    'admin.php?page=checkout_form_designer&tab=fields',
                    '',
                    6
                );
            }

            /*add_submenu_page(
  'admin.php?page=wc-settings',
  '綠界電子發票設定',
  '綠界電子發票設定',
  'edit_shop_orders',
  'admin.php?page=wc-settings&tab=ecpayinvoice',
  '',
  7
  );*/
        }

        //聯絡表單
        /*add_menu_page(
  '聯絡表單',
  '聯絡表單',
  'read',
  '',
  '',
  'dashicons-clipboard', //icon
  null
  );*/

        //[DEV]擴充模組
        if (WP_DEBUG == true && self::$current_user_level == 0) {
            add_menu_page(
                '擴充模組',
                '擴充模組',
                'read',
                'jdaio_extention',
                [$this, 'jdaio_extention_f'],
                'dashicons-block-default', //icon
                null
            );
            //教學中心
            add_menu_page(
                '教學中心',
                '教學中心',
                'read',
                'jdaio_teach',
                [$this, 'jdaio_teach_f'],
                'dashicons-info', //icon
                null
            );
        }



        if (isset($_POST['submit_ok'])) {
            if (!empty($_POST['jdaio_simple_mode_enable'])) {
                $jdaio_simple_mode_enable = $_POST['jdaio_simple_mode_enable'];
            } else {
                $jdaio_simple_mode_enable = '';
            }
            update_user_meta(get_current_user_id(), 'jdaio_simple_mode_enable', $jdaio_simple_mode_enable);
            if ($jdaio_simple_mode_enable == 'enable') {
                $this->jdaio_remove_menu_page_simple_mode();
            }
        } else {
            if ($this->jdaio_simple_mode()) {
                $this->jdaio_remove_menu_page_simple_mode();
            }
        }

        /*global $menu;
 echo '<pre>';
 var_dump($menu);
 echo '</pre>';*/
    }

    public function jdaio_setting()
    {
        /*$metabox = new Metabox(array(
  'id' => 'metabox_id',
  'title' => 'My awesome metabox',
  'screen' => 'post', // post type
  'context' => 'advanced', // Options normal, side, advanced.
  'priority' => 'default'
  ));*/

        $defalut = new Option();

        $defalut->register();

        $defalut->addMenu(
            array(
                'page_title' => __('網站一般設定', 'plugin-name'),
                'menu_title' => __('網站一般設定', 'plugin-name'),
                'capability' => 'edit_shop_orders',
                'slug' => 'jdaio_setting',
                'icon' => 'dashicons-admin-generic',
                'position' => 10,
                'submenu' => false,
            )
        );

        $defalut->addTab(
            array(
                array(
                    'id' => 'general_section',
                    'title' => __('基礎設定', 'plugin-name'),
                    //'desc'  => __( 'These are general settings for Plugin Name', 'plugin-name' ),
                ),
                array(
                    'id' => 'tracking_section',
                    'title' => __('網站追蹤設定', 'plugin-name'),
                    //'desc'  => __( 'These are advance settings for Plugin Name', 'plugin-name' )
                ),
                array(
                    'id' => 'sociallogin_section',
                    'title' => __('社群登入', 'plugin-name'),
                    //'desc'  => __( 'These are advance settings for Plugin Name', 'plugin-name' )
                ),
                /*array(
   'id'    => 'advance_section',
   'title' => __('進階設定', 'plugin-name'),
   //'desc'  => __( 'These are advance settings for Plugin Name', 'plugin-name' )
   )*/
            )
        );
        //---------- GENERAL SECTION ----------//
        $defalut->addText(
            'general_section',
            array(
                'id' => 'blogname',
                'label' => __('網站名稱', 'plugin-name'),
                'desc' => __('Some description of my field', 'plugin-name'),
                'placeholder' => '請輸入網站名稱',
                'show_in_rest' => true,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'general_section',
            array(
                'id' => 'blogname',
                'label' => __('網站名稱', 'plugin-name'),
                //'desc'              => __( 'Some description of my field', 'plugin-name' ),
                'placeholder' => '請輸入網站名稱',
                'show_in_rest' => true,
                'size' => 'regular',
            ),
        );
        $defalut->addTextarea(
            'general_section',
            array(
                'id' => 'blogdescription',
                'label' => __('網站說明', 'plugin-name'),
                //'desc'        => __( 'Textarea description', 'plugin-name' ),
                'placeholder' => __('請說明你的網站特色', 'plugin-name'),
            ),
        );
        $defalut->addMedia(
            'general_section',
            array(
                'id' => 'jdaio_site_logo',
                'label' => __('網站 LOGO', 'plugin-name'),
                'desc' => __('建議尺寸300X300，支援JPG/PNG圖檔', 'plugin-name'),
                'type' => 'media',
                'options' => array(
                    'btn' => __('選擇圖片', 'plugin-name'),
                    //'width'     => 300,
                    //'max_width' => 300,
                ),
                'default' => 94,
            ),
        );
        $defalut->addMedia(
            'general_section',
            array(
                'id' => 'jdaio_favicon',
                'label' => __('網站小圖標(favicon)', 'plugin-name'),
                'desc' => __('建議尺寸100X100，支援JPG/PNG圖檔', 'plugin-name'),
                'type' => 'media',
                'options' => array(
                    'btn' => __('選擇圖片', 'plugin-name'),
                    'width' => 100,
                    'max_width' => 100,
                ),
                'default' => 90,
            ),
        );
        $defalut->addMedia(
            'general_section',
            array(
                'id' => 'jdaio_login_bg',
                'label' => __('登入頁背景', 'plugin-name'),
                'desc' => __('建議尺寸1980X1080，支援JPG/PNG圖檔', 'plugin-name'),
                'type' => 'media',
                'options' => array(
                    'btn' => __('選擇圖片', 'plugin-name'),
                    //'width'     => 1000,
                    //'max_width' => 1000,
                ),
                'default' => wp_get_attachment_image_url(20147, 'full'),
            ),
        );
        //---------- TRACKING SECTION ----------//
        $defalut->addText(
            'tracking_section',
            array(
                'id' => 'jdaio_fb_track',
                'label' => __('Facebook Pixel ID', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                        <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                            <a href="https://blog.recart.com/how-to-find-my-facebook-pixel-id/" target="_blank">如何取得</a>
                                        </span>
                                    </div>',
                'placeholder' => '',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'tracking_section',
            array(
                'id' => 'jdaio_ga_track',
                'label' => __('Google Analytics tracking ID', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                        <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                            <a href="https://www.whatconverts.com/help/docs/integrations/google-analytics/where-do-i-find-my-google-analytics-tracking-id/" target="_blank">如何取得</a>
                                        </span>
                                    </div>',
                'placeholder' => 'UA-9032xxxx-x',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        //---------- Socaillogin SECTION ----------//
        $defalut->addCheckboxes(
            'sociallogin_section',
            array(
                'id' => 'jdaio_sociallogin_enable',
                'label' => __('啟用社群登入', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;"><span class="material-icons-outlined uk-margin-small-right">info</span><span><a href="' . site_url() . '/my-account" target="_blank">查看登入頁面</a></span></div>',
                'options' => array(
                    '1' => '啟用',
                ),
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id' => 'jdaio_facebook_app',
                'label' => __('Facebook App ID', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/how-to-get-google-plus-client-id/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder' => '',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id' => 'jdaio_facebook_secret',
                'label' => __('Facebook App Secret', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/how-to-get-google-plus-client-id/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder' => '',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id' => 'jdaio_google_app',
                'label' => __('Google Client ID', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/how-to-get-google-plus-client-id/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder' => '',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id' => 'jdaio_google_secret',
                'label' => __('Google Client Secret', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/how-to-get-google-plus-client-id/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder' => '',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id' => 'jdaio_line_app',
                'label' => __('Line Channel ID', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/create-line-channel-for-line-login/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder' => '',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id' => 'jdaio_line_secret',
                'label' => __('Line Channel Secret', 'plugin-name'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/create-line-channel-for-line-login/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder' => '',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
    }

    public function jdaio_sync_data()
    {

        //LOGO
        $admin2020_options = get_option('admin2020_settings');
        $jdaio_site_logo = get_option('jdaio_site_logo');
        $jdaio_login_bg = get_option('jdaio_login_bg');

        //Social Login
        $the_champ_login = get_option('the_champ_login');
        $the_champ_login['enable'] = get_option('jdaio_sociallogin_enable');
        $the_champ_login['fb_key'] = get_option('jdaio_facebook_app');
        $the_champ_login['fb_secret'] = get_option('jdaio_facebook_secret');
        $the_champ_login['google_key'] = get_option('jdaio_google_app');
        $the_champ_login['google_secret'] = get_option('jdaio_google_secret');
        $the_champ_login['line_channel_id'] = get_option('jdaio_line_app');
        $the_champ_login['line_channel_secret'] = get_option('jdaio_line_secret');

        if (!empty($the_champ_login['fb_key']) && !empty($the_champ_login['fb_secret'])) {
            $providers[] = 'facebook';
        }
        if (!empty($the_champ_login['google_key']) && !empty($the_champ_login['google_secret'])) {
            $providers[] = 'google';
        }
        if (!empty($the_champ_login['line_channel_id']) && !empty($the_champ_login['line_channel_secret'])) {
            $providers[] = 'line';
        } else {
            $providers = [];
        }
        $the_champ_login['providers'] = $providers;

        //update_option('the_champ_login', $the_champ_login);

        //site logo
        $admin2020_options['modules']['admin2020_admin_bar']['light-logo'] = wp_get_attachment_image_url($jdaio_site_logo, 'large');

        $admin2020_options['modules']['admin2020_admin_login']['login-background'] = wp_get_attachment_image_url($jdaio_login_bg, 'full');

        if (isset($_POST['submit'])) {

            update_option('admin2020_settings', $admin2020_options);
            //social login
            update_option('the_champ_login', $the_champ_login);
        } else {
            if ($admin2020_options['modules']['admin2020_admin_bar']['light-logo'] == wp_get_attachment_image_url($jdaio_site_logo, 'large') && $admin2020_options['modules']['admin2020_admin_login']['login-background'] == wp_get_attachment_image_url($jdaio_login_bg, 'full')) {
                // do nothing
            } else {
                update_option('admin2020_settings', $admin2020_options);
            }
        }
    }

    public function enqueue_admin_css()
    {

        wp_enqueue_style('Jerry_Divi_AIO admin_for_editor css', plugins_url('/../../assets/css/jdaio_admin_level_' . self::$current_user_level . '.css', __FILE__));

        if ($this->jdaio_simple_mode()) {
            wp_enqueue_style('Jerry_Divi_AIO simple mode css', plugins_url('/../../assets/css/jdaio_admin_simple_mode.css', __FILE__));
        }
    }

    public function enqueue_admin_js()
    {

        switch (self::$current_user_level) {
            case 0:
                wp_enqueue_script('Jerry_Divi_AIO admin js', plugins_url('/../../assets/js/jdaio_admin_level_0.js', __FILE__));
                break;
            case 1:
                wp_enqueue_script('Jerry_Divi_AIO admin js', plugins_url('/../../assets/js/jdaio_admin_level_0.js', __FILE__));
                wp_enqueue_script('Jerry_Divi_AIO admin js', plugins_url('/../../assets/js/jdaio_admin_level_1.js', __FILE__));
                break;
            default:
                wp_enqueue_script('Jerry_Divi_AIO admin js', plugins_url('/../../assets/js/jdaio_admin_level_0.js', __FILE__));
                wp_enqueue_script('Jerry_Divi_AIO admin js', plugins_url('/../../assets/js/jdaio_admin_level_1.js', __FILE__));
                break;
        }
    }

    public function enqueue_front_css()
    {
        wp_enqueue_style('Jerry_Divi_AIO front css', plugins_url('/../../assets/css/jdaio_front.css', __FILE__));
    }

    public function enqueue_front_js()
    {
        wp_enqueue_script('Jerry_Divi_AIO front js', plugins_url('/../../assets/js/jdaio_front.js', __FILE__));
    }

    public function remove_admin_bar()
    {

        if (self::$current_user_level > 0) {
            show_admin_bar(false);
        }
    }

    public function jdaio_simple_mode()
    {
        $jdaio_simple_mode_enable = get_user_meta(get_current_user_id(), 'jdaio_simple_mode_enable', true);
        return ($jdaio_simple_mode_enable == 'enable');
    }

    public function jdaio_add_admin_footer()
    {
        /*$jdaio_simple_mode_enable = get_user_meta(get_current_user_id(), 'jdaio_simple_mode_enable', true);

  echo '<div class="jdaio_simple_mode_btn" style="margin-left:100px;">';
  var_dump($jdaio_simple_mode_enable);
  var_dump($jdaio_simple_mode_enable);
  echo '</div>';*/

        $URL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $is_checked = ($this->jdaio_simple_mode()) ? 'checked' : '';

        if (class_exists('WooCommerce', false)) {
            echo '<div class="jdaio_simple_mode_btn">極簡模式<form class="jdaio_simple_mode_form" action="' . $URL . '" method="post"><input type="checkbox" name="jdaio_simple_mode_enable" value="enable" class="jdaio_simple_mode" ' . $is_checked . ' /><input type="hidden" name="submit_ok" value="submit_ok" /></form>';
        }
    }

    public function jdaio_remove_filters()
    {
        global $wp_filter;

        /*
   * 解掉export order套件 select2.js的bug，詳情可以看
   * 位置：plugins\woo-order-export-lite\classes\class-wc-order-export-admin.php
   * add_filter( 'script_loader_src', array( $this, 'script_loader_src' ), 100, 2 );
   */
        //unset($wp_filter['script_loader_src']->callbacks[100]);

        /*
   * 移除掉我以外的所有通知(priority 2)
   * priority 10全部屏蔽  100是admin2020
   */
        if (self::$current_user_level > 0) {
            unset($wp_filter['admin_notices']->callbacks[10]);
        }

        /*debug*/
        /*echo '<pre>';
  var_dump($wp_filter['admin_notices']->callbacks[10]);
  echo '</pre>';*/
        /*debug*/
    }

    public function jdaio_add_wp_head()
    {
?>
        <?php if (!empty(get_option('jdaio_ga_track'))) : ?>
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo get_option('jdaio_ga_track'); ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());

                gtag('config', '<?php echo get_option('jdaio_ga_track'); ?>');
            </script>
        <?php endif; ?>
        <?php if (!empty(get_option('jdaio_fb_track'))) : ?>
            <!-- Facebook Pixel Code -->
            <script>
                ! function(f, b, e, v, n, t, s) {
                    if (f.fbq) return;
                    n = f.fbq = function() {
                        n.callMethod ?
                            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                    };
                    if (!f._fbq) f._fbq = n;
                    n.push = n;
                    n.loaded = !0;
                    n.version = '2.0';
                    n.queue = [];
                    t = b.createElement(e);
                    t.async = !0;
                    t.src = v;
                    s = b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t, s)
                }(window, document, 'script',
                    'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '<?php echo get_option('jdaio_ga_track'); ?>');
                fbq('track', 'PageView');
            </script>
            <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo get_option('jdaio_ga_track'); ?>&ev=PageView&noscript=1" /></noscript>
            <!-- End Facebook Pixel Code -->
        <?php endif; ?>
        <link rel="shortcut icon" href="<?php echo wp_get_attachment_image_url(get_option('jdaio_favicon'), 100); ?>">
        <script>
            let SITE_URL = "<?php echo site_url(); ?>";
        </script>
<?php
    }

    public function jdaio_add_register_form_field()
    {
        //新增欄位
        /*woocommerce_form_field(
  'country_to_visit',
  array(
  'type'        => 'text',
  'required'    => true, // just adds an "*"
  'label'       => 'Country you want to visit the most'
  ),
  ( isset($_POST['country_to_visit']) ? $_POST['country_to_visit'] : '' )
  );*/
        echo do_shortcode('[TheChamp-Login title="用社群帳號登入"]');
    }

    public function jdaio_login_redirect($redirect_to, $request, $user)
    {
        if (class_exists('WooCommerce', false)) {
            $redirect_to = admin_url() . "admin.php?page=wc-admin&path=%2Fanalytics%2Foverview";
            return $redirect_to;
        } else {
            return $redirect_to;
        }
    }

    public function jdaio_toolbar($wp_admin_bar)
    {

        /*$wp_admin_bar->add_node([
 'id'      => 'line',
 'title'   => 'Google Analytics',
 'parent'  => '',
 'href'    => esc_url( admin_url( 'admin.php?page=googlesitekit-splash' ) ),
 'group'   => false,
 'meta'    => [
 'class' => 'jdaio_toolbar_btn'
 ],
 ]);
  */
    }

    public function jdaio_remove_dashboard_widgets()
    {

        remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // Right Now
        // Remove comments metabox from dashboard
        if (!COMMENT_OPEN) {
            remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
        }
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal'); // Incoming Links
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); // Quick Press
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side'); // Recent Drafts
        remove_meta_box('dashboard_primary', 'dashboard', 'side'); // WordPress blog
        remove_meta_box('dashboard_secondary', 'dashboard', 'side'); // Other WordPress News
        remove_meta_box('themefusion-news', 'dashboard', 'normal');
        remove_meta_box('wordfence_activity_report_widget', 'dashboard', 'normal');

        // use 'dashboard-network' as the second parameter to remove widgets from a network dashboard.
    }

    public function jdaio_profile_fields($contact_methods)
    {
        // Unset fields you don’t need
        unset($contact_methods['author_email']);
        unset($contact_methods['author_google']);
        unset($contact_methods['author_twitter']);
        unset($contact_methods['author_linkedin']);
        unset($contact_methods['author_dribble']);
        unset($contact_methods['author_whatsapp']);
        unset($contact_methods['author_custom']);

        return $contact_methods;
    }

    public function jdaio_change_login_logo()
    {
        $logo_style = '<style type="text/css">';
        $logo_style .= '#backtoblog {display:none !important;}';
        $logo_style .= '#nav {margin-bottom:51px !important;}';
        $logo_style .= '</style>';
        echo $logo_style;
    }

    //修改用戶的註冊預設身分
    public function jdaio_change_default_role($default_role)
    {
        return 'customer';
        //return $default_role;
    }

    public function wd_admin_menu_rename()
    {
        global $menu; // Global to get menu array
        /*echo '<pre>';
        var_dump($menu);
        echo '</pre>';*/
        foreach ($menu as $key => $menu_array) {

            switch ($menu_array[2]) {
                case 'edit.php':
                    $menu[$key][0] = '文章中心';
                    break;
                case 'edit.php?post_type=page':
                    $menu[$key][0] = '頁面中心';
                    break;
                    /*case 'edit.php?post_type=portfolio':
    $menu[$key][0] = '作品展示';
    break;*/
                case 'edit.php?post_type=product':
                    $menu[$key][0] = '商品中心';
                    break;
                case 'users.php':
                    $menu[$key][0] = '用戶中心';
                    break;
                case 'wc-admin&path=/analytics/overview':
                    $menu[$key][0] = '數據中心';
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $menu;
    }

    public function jdaio_remove_menu_page_level_1()
    {
        //remove_submenu_page( string $menu_slug, string $submenu_slug )

        //移除主選單
        //remove_menu_page('index.php');
        remove_menu_page('upload.php');
        if (!COMMENT_OPEN) {
            remove_menu_page('edit-comments.php');
        }
        remove_menu_page('plugins.php');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
        remove_menu_page('themes.php');
        remove_menu_page('et_bloom_options');
        remove_menu_page('et_divi_options');
        remove_menu_page('theseoframework-settings');
        remove_menu_page('edit.php?post_type=project');

        //分析 - 移除下載跟稅金
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/taxes');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/downloads');
    }
    public function jdaio_remove_menu_page_level_2()
    {
        $this->jdaio_remove_menu_page_level_1();
    }
    public function jdaio_remove_menu_page_simple_mode()
    {
        remove_menu_page('index.php');
        remove_menu_page('admin.php?page=wc-settings');
        // 一般設定
        remove_submenu_page('jdaio_setting', 'nav-menus.php');
        remove_submenu_page('jdaio_setting', 'options-general.php?page=swpsmtp_settings#smtp');
        // 數據中心
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/revenue');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/orders');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/variations');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/categories');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/coupons');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/stock');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/settings');
        // 產品
        remove_submenu_page('edit.php?post_type=product', 'product_attributes');

        add_menu_page(
            '網路商店設定',
            '網路商店設定',
            'edit_shop_orders',
            'admin.php?page=wc-settings&tab=checkout',
            '',
            'dashicons-store', //icon
            null
        );
        add_submenu_page(
            'admin.php?page=wc-settings&tab=checkout',
            '運費設定',
            '運費設定',
            'edit_shop_orders',
            'admin.php?page=wc-settings&tab=shipping',
            '',
            2
        );
    }

    //調整主選單順序
    public function custom_menu_order($menu_ord)
    {
        if (!$menu_ord) {
            return true;
        }

        //--debug--//
        /*global $menu;
        echo '<pre>';
        var_dump($menu);
        echo '</pre>';*/
        //--debug--//

        if ($this->jdaio_simple_mode()) {
            return array(
                'admin.php?page=googlesitekit-splash',
                'wc-admin&path=/analytics/overview',
                'edit.php?post_type=shop_order',
                'edit.php?post_type=product',
                'edit.php',
                'edit.php?post_type=page',
                'jdaio_setting',
                'admin.php?page=wc-settings&tab=checkout',
                'users.php',
                'edit.php?post_type=shop_coupon',
                'admin.php?page=theseoframework-settings',
                //'jdaio_extention',
                //'jdaio_teach',
            );
        }

        return array(
            //'index.php',
            'admin.php?page=googlesitekit-splash',
            'wc-admin&path=/analytics/overview',
            'edit.php?post_type=shop_order',
            'edit.php?post_type=product',
            'edit.php',
            'edit.php?post_type=page',
            'customize.php?et_customizer_option_set=theme',
            'jdaio_setting',
            'admin.php?page=wc-settings',
            'users.php',
            'edit.php?post_type=shop_coupon',
            'admin.php?page=theseoframework-settings',
            //'jdaio_extention',
            //'jdaio_teach',
        );
    }

    public function jdaio_admin_bar_render()
    {
        global $wp_admin_bar;
        //debug
        /*echo '<pre>';
  var_dump($wp_admin_bar);
  echo '</pre>';*/
        if (!COMMENT_OPEN) {
            $wp_admin_bar->remove_menu('comments');
        }
        $wp_admin_bar->remove_menu('updates');
        $wp_admin_bar->remove_menu('feedback');
        $wp_admin_bar->remove_menu('support-forums');
        $wp_admin_bar->remove_menu('feedback');
        $wp_admin_bar->remove_menu('documentation');
        $wp_admin_bar->remove_menu('wporg');
        $wp_admin_bar->remove_menu('about');
        $wp_admin_bar->remove_menu('wp-logo');
        $wp_admin_bar->remove_menu('new-content');
        $wp_admin_bar->remove_menu('fb-edit');
    }

    public function jdaio_teach_f()
    {
        echo '<h2>還在吸取日月精華...</h2>';
    }
    public function jdaio_extention_f()
    {
        echo '<h2>還在吸取日月精華...</h2>';
    }

    public function jdaio_disable_comments_post_types_support()
    {
        // Disable support for comments and trackbacks in post types
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }

        // Redirect any user trying to access comments page
        global $pagenow;
        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }
    }

    // Close comments on the front-end
    public function jdaio_disable_comments_status()
    {
        return false;
    }

    // Hide existing comments
    public function jdaio_disable_comments_hide_existing_comments($comments)
    {
        $comments = array();
        return $comments;
    }

    // Remove comments links from admin bar
    public function jdaio_disable_comments_admin_bar()
    {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }
}

require_once __DIR__ . '/class-woocmmerce.php';
require_once __DIR__ . '/class-notice.php';
require_once __DIR__ . '/class-metabox.php';
require_once __DIR__ . '/class-wcmp.php';
new Admin\JDAIO\Woocommerce;
new Admin\JDAIO\Notice;
new Admin\JDAIO\MetaBox;
new Admin\JDAIO\WCMP;
