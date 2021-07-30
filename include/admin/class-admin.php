<?php

/**
 * customise admin
 */

//namespace Admin\JAIO;

defined('ABSPATH') || exit;

require __DIR__ . '/../../vendor/autoload.php';

use ODS\Metabox;
use ODS\Option;

/* ODS\Metabox Usage
* https://github.com/oberonlai/wp-metabox
* To create a metabox, first instantiate an instance of `Metabox`.  The class takes one argument, which is an associative array.  The keys to the array are similar to the arguments provided to the [add_meta_box](https://developer.wordpress.org/reference/functions/add_meta_box/) WordPress function; however, you don't provide `callback` or `callback_args`.
*/

class Custom_Admin extends Jerry_AIO
{



    public function __construct()
    {
        //---- CSS, JS ----//
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_css'], 9999);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_css'], 9999);
        add_action('admin_footer', [$this, 'enqueue_admin_js'], 9999);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_js'], 9999);

        //---- 停用 heartbeat ----//
        //add_action('init', [$this, 'jaio_stop_heartbeat'], 999);

        //---- 註冊設定 ----//
        add_action('admin_init', [$this, 'jaio_sync_data']);
        add_action('init', [$this, 'jaio_setting']);

        //---- user ----//
        add_filter('pre_option_default_role', [$this, 'jaio_change_default_role'], 999);
        add_action('user_new_form', [$this, 'jaio_dontchecknotify_register_form'], 999);

        //---- Admin ----//
        remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
        add_filter('use_block_editor_for_post', '__return_false');



        //---- 在WordPress後台新增選單 ----//
        add_action('admin_menu', [$this, 'jaio_amp_setting'], 999998);
        add_filter('custom_menu_order', [$this, 'custom_menu_order'], 1999996);
        add_filter('menu_order', [$this, 'custom_menu_order'], 1999997);
        add_action('admin_menu', [$this, 'wd_admin_menu_rename'],  999999);

        //add_filter('menu_order', array($this, 'jaio_submenu_order'), 999999);

        //---- admin_bar_menu ----//
        add_action('wp_before_admin_bar_render', [$this, 'jaio_admin_bar_render']);

        //---- Login page ----//
        add_action('login_head', [$this, 'jaio_change_login_logo']);

        //---- disable gutenberg ----//
        add_filter('use_block_editor_for_post', '__return_false');

        //---- Profile page ----//
        add_filter('user_contactmethods', [$this, 'jaio_profile_fields'], 999999);

        //---- Meta Box ----//
        add_action('wp_dashboard_setup', [$this, 'jaio_remove_dashboard_widgets']);

        //---- tool bar ----//
        //add_action( 'admin_bar_menu', [ $this, 'jaio_toolbar' ], 99 );

        //upload file
        add_action('admin_enqueue_scripts', [$this, 'jaio_enqueue_upload_script']);

        //login redirect
        add_filter('login_redirect', [$this, 'jaio_login_redirect'], 999, 3);

        //add Social login at woocommerce register form
        //woocommerce_login_form_end | woocommerce_login_form_start
        add_action('woocommerce_login_form_end', [$this, 'jaio_add_register_form_field'], 999);

        //change favicon
        add_action('wp_head', [$this, 'jaio_add_wp_head']);
        add_action('admin_head', [$this, 'jaio_add_wp_head']);

        //remove hook
        add_action('admin_init', [$this, 'jaio_remove_filters'], 9999);




        //hide user from other user
        add_action('pre_user_query', [$this, 'jaio_hide_user']);

        //add admin footer
        add_action('admin_footer', [$this, 'jaio_add_admin_footer']);



        add_action('after_setup_theme', [$this, 'remove_admin_bar']);

        add_filter('bogo_localizable_post_types', [$this, 'my_localizable_post_types'], 99999, 1);
    }

    /**
 * Support custom post type with bogo.
 *
 * @param array $ locallyizable Supported post types.
 *
@return array
 */

function my_localizable_post_types($localizable)
{
    $args = array(
        'public'    => true,
        '_builtin'  => false
    );
    $custom_post_types = get_post_types($args);
    return array_merge($localizable, $custom_post_types);
}



    function jaio_amp_setting($menu_ord)
    {
        //訂單中心
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                '訂單中心',
                '訂單中心',
                'manage_options',
                'edit.php?post_type=shop_order',
                '',
                'dashicons-cart', //icon
                null
            );


            add_submenu_page(
                'edit.php?post_type=shop_order',
                '匯出訂單',
                '匯出訂單',
                'manage_options',
                'admin.php?page=wc-order-export#segment=common',
                '',
                2
            );

            add_submenu_page(
                'edit.php?post_type=shop_order',
                '批量匯入物流單號',
                '批量匯入物流單號',
                'manage_options',
                'admin.php?page=woocommerce-advanced-shipment-tracking',
                '',
                2
            );
        }

        //用戶中心
        add_submenu_page(
            'users.php',
            '匯出會員',
            '匯出會員',
            'manage_options',
            'admin.php?page=wt_import_export_for_woo_basic_export',
            '',
            2
        );

        //行銷中心
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                '行銷中心',
                '行銷中心',
                'manage_options',
                'admin.php?page=theseoframework-settings',
                '',
                'dashicons-admin-appearance', //icon
                null
            );
            add_submenu_page(
                'admin.php?page=theseoframework-settings',
                '折價券',
                '折價券',
                'manage_options',
                'edit.php?post_type=shop_coupon',
                '',
                2
            );
            add_submenu_page(
                'admin.php?page=theseoframework-settings',
                '批量產生折價券',
                '批量產生折價券',
                'manage_options',
                'admin.php?page=woocommerce_coupon_generator',
                '',
                3
            );
        }


        //網站設定http://localhost/avada_wp_tp/wp-admin/
        /*add_menu_page(
            '設定',
            '設定',
            'manage_options',
            'jaio_setting',
            [$this, 'jaio_setting_f'],
            'dashicons-admin-appearance', //icon
            null
        );*/
        add_submenu_page(
            'jaio_setting',
            '首頁設定',
            '首頁設定',
            'manage_options',
            'post.php?post=' . get_option('page_on_front') . '&action=edit',
            '',
            2
        );
        add_submenu_page(
            'jaio_setting',
            '網站外觀設計',
            '網站外觀設計',
            'manage_options',
            'themes.php?page=avada_options',
            '',
            3
        );
        add_submenu_page(
            'jaio_setting',
            '網站全局外框設計',
            '網站全局外框設計',
            'manage_options',
            'admin.php?page=avada-layouts',
            '',
            4
        );
        add_submenu_page(
            'jaio_setting',
            '輪播設定',
            '輪播設定',
            'manage_options',
            'edit-tags.php?taxonomy=slide-page&post_type=slide',
            '',
            5
        );
        add_submenu_page(
            'jaio_setting',
            '輪播圖片設定',
            '輪播圖片設定',
            'manage_options',
            'edit.php?post_type=slide',
            '',
            6
        );
        add_submenu_page(
            'jaio_setting',
            '網站選單',
            '網站選單',
            'manage_options',
            'nav-menus.php',
            '',
            7
        );
        add_submenu_page(
            'jaio_setting',
            '系統發信設定',
            '系統發信設定',
            'manage_options',
            'options-general.php?page=swpsmtp_settings#smtp',
            '',
            8
        );

        //網路商店設定
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                '網路商店設定',
                '網路商店設定',
                'manage_options',
                'admin.php?page=wc-settings',
                '',
                'dashicons-store', //icon
                null
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '運費設定',
                '運費設定',
                'manage_options',
                'admin.php?page=wc-settings&tab=shipping',
                '',
                2
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '付款方式設定',
                '付款方式設定',
                'manage_options',
                'admin.php?page=wc-settings&tab=checkout',
                '',
                3
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '帳號及隱私權設定',
                '帳號及隱私權設定',
                'manage_options',
                'admin.php?page=wc-settings&tab=account',
                '',
                4
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '訂單通知信內容設定',
                '訂單通知信內容設定',
                'manage_options',
                'admin.php?page=wc-settings&tab=email',
                '',
                5
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '自訂結帳表單',
                '自訂結帳表單',
                'manage_options',
                'admin.php?page=checkout_form_designer&tab=fields',
                '',
                6
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                '綠界電子發票設定',
                '綠界電子發票設定',
                'manage_options',
                'admin.php?page=wc-settings&tab=ecpayinvoice',
                '',
                7
            );
        }

        //聯絡表單
        add_menu_page(
            '聯絡表單',
            '聯絡表單',
            'read',
            'admin.php?page=avada-forms',
            '',
            'dashicons-clipboard', //icon
            null
        );

        //教學中心
        add_menu_page(
            '教學中心',
            '教學中心',
            'read',
            'jaio_teach',
            [$this, 'jaio_teach_f'],
            'dashicons-info', //icon
            null
        );




        $user_level = $this->jaio_get_current_user_level();
        switch ($user_level) {
            case 0:
                # do nothing
                break;
            case 1:
                $this->jaio_remove_menu_page_level_1();
                break;
            case 2:
                $this->jaio_remove_menu_page_level_2();
                break;
            default:
                $this->jaio_remove_menu_page_level_2();
                break;
        }


        if (isset($_POST['submit_ok'])) {
            update_user_meta(get_current_user_id(), 'jaio_simple_mode_enable', $_POST['jaio_simple_mode_enable']);
            if ($_POST['jaio_simple_mode_enable'] == 'enable') {
                $this->jaio_remove_menu_page_simple_mode();
            }
        } else {
            if ($this->jaio_simple_mode()) {
                $this->jaio_remove_menu_page_simple_mode();
            }
        }

        //remove_menu_page('admin.php?page=caip_general');
    }

    function jaio_setting()
    {
        $metabox = new Metabox(array(
            'id' => 'metabox_id',
            'title' => 'My awesome metabox',
            'screen' => 'post', // post type
            'context' => 'advanced', // Options normal, side, advanced.
            'priority' => 'default'
        ));



        $defalut = new Option();

        $defalut->register();

        $defalut->addMenu(
            array(
                'page_title' => __('網站一般設定', 'plugin-name'),
                'menu_title' => __('網站一般設定', 'plugin-name'),
                'capability' => 'manage_options',
                'slug'       => 'jaio_setting',
                'icon'       => 'dashicons-admin-generic',
                'position'   => 10,
                'submenu'    => false,
            )
        );


        $defalut->addTab(
            array(
                array(
                    'id'    => 'general_section',
                    'title' => __('基礎設定', 'plugin-name'),
                    //'desc'  => __( 'These are general settings for Plugin Name', 'plugin-name' ),
                ),
                array(
                    'id'    => 'tracking_section',
                    'title' => __('網站追蹤設定', 'plugin-name'),
                    //'desc'  => __( 'These are advance settings for Plugin Name', 'plugin-name' )
                ),
                array(
                    'id'    => 'sociallogin_section',
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
                'id'                => 'blogname',
                'label'             => __('網站名稱', 'plugin-name'),
                'desc'              => __('Some description of my field', 'plugin-name'),
                'placeholder'       => '請輸入網站名稱',
                'show_in_rest'      => true,
                'size'              => 'regular',
            ),
        );
        $defalut->addText(
            'general_section',
            array(
                'id'                => 'blogname',
                'label'             => __('網站名稱', 'plugin-name'),
                //'desc'              => __( 'Some description of my field', 'plugin-name' ),
                'placeholder'       => '請輸入網站名稱',
                'show_in_rest'      => true,
                'size'              => 'regular',
            ),
        );
        $defalut->addTextarea(
            'general_section',
            array(
                'id'          => 'blogdescription',
                'label'       => __('網站說明', 'plugin-name'),
                //'desc'        => __( 'Textarea description', 'plugin-name' ),
                'placeholder' => __('請說明你的網站特色', 'plugin-name'),
            ),
        );
        $defalut->addMedia(
            'general_section',
            array(
                'id'      => 'jaio_site_logo',
                'label'   => __('網站 LOGO', 'plugin-name'),
                'desc'    => __('建議尺寸300X300，支援JPG/PNG圖檔', 'plugin-name'),
                'type'    => 'media',
                'options' => array(
                    'btn'       => __('選擇圖片', 'plugin-name'),
                    //'width'     => 300,
                    //'max_width' => 300,
                ),
                'default'  => wp_get_attachment_image_url(13620, 'large')
            ),
        );
        $defalut->addMedia(
            'general_section',
            array(
                'id'      => 'jaio_favicon',
                'label'   => __('網站小圖標(favicon)', 'plugin-name'),
                'desc'    => __('建議尺寸100X100，支援JPG/PNG圖檔', 'plugin-name'),
                'type'    => 'media',
                'options' => array(
                    'btn'       => __('選擇圖片', 'plugin-name'),
                    'width'     => 100,
                    'max_width' => 100,
                ),
                'default'  => wp_get_attachment_image_url(13620, 100)
            ),
        );
        $defalut->addMedia(
            'general_section',
            array(
                'id'      => 'jaio_login_bg',
                'label'   => __('登入頁背景', 'plugin-name'),
                'desc'    => __('建議尺寸1980X1080，支援JPG/PNG圖檔', 'plugin-name'),
                'type'    => 'media',
                'options' => array(
                    'btn'       => __('選擇圖片', 'plugin-name'),
                    //'width'     => 1000,
                    //'max_width' => 1000,
                ),
                'default'  => wp_get_attachment_image_url(20147, 'full')
            ),
        );
        //---------- TRACKING SECTION ----------//
        $defalut->addText(
            'tracking_section',
            array(
                'id'                => 'jaio_fb_track',
                'label'             => __('Facebook Pixel ID', 'plugin-name'),
                'desc'              => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                        <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                            <a href="https://blog.recart.com/how-to-find-my-facebook-pixel-id/" target="_blank">如何取得</a>
                                        </span>
                                    </div>',
                'placeholder'       => '',
                'show_in_rest'      => false,
                'size'              => 'regular',
            ),
        );
        $defalut->addText(
            'tracking_section',
            array(
                'id'                => 'jaio_ga_track',
                'label'             => __('Google Analytics tracking ID', 'plugin-name'),
                'desc'              => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                        <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                            <a href="https://www.whatconverts.com/help/docs/integrations/google-analytics/where-do-i-find-my-google-analytics-tracking-id/" target="_blank">如何取得</a>
                                        </span>
                                    </div>',
                'placeholder'       => 'UA-9032xxxx-x',
                'show_in_rest'      => false,
                'size'              => 'regular',
            ),
        );
        //---------- Socaillogin SECTION ----------//
        $defalut->addCheckboxes(
            'sociallogin_section',
            array(
                'id'      => 'jaio_sociallogin_enable',
                'label'   => __('啟用社群登入', 'plugin-name'),
                'desc'    => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;"><span class="material-icons-outlined uk-margin-small-right">info</span><span><a href="' . site_url() . '/my-account" target="_blank">查看登入頁面</a></span></div>',
                'options' => array(
                    '1' => '啟用',
                )
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id'                => 'jaio_facebook_app',
                'label'             => __('Facebook App ID', 'plugin-name'),
                'desc'              => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/how-to-get-google-plus-client-id/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder'       => '',
                'show_in_rest'      => false,
                'size'              => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id'                => 'jaio_facebook_secret',
                'label'             => __('Facebook App Secret', 'plugin-name'),
                'desc'              => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/how-to-get-google-plus-client-id/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder'       => '',
                'show_in_rest'      => false,
                'size'              => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id'                => 'jaio_google_app',
                'label'             => __('Google Client ID', 'plugin-name'),
                'desc'              => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/how-to-get-google-plus-client-id/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder'       => '',
                'show_in_rest'      => false,
                'size'              => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id'                => 'jaio_google_secret',
                'label'             => __('Google Client Secret', 'plugin-name'),
                'desc'              => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/how-to-get-google-plus-client-id/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder'       => '',
                'show_in_rest'      => false,
                'size'              => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id'                => 'jaio_line_app',
                'label'             => __('Line Channel ID', 'plugin-name'),
                'desc'              => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/create-line-channel-for-line-login/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder'       => '',
                'show_in_rest'      => false,
                'size'              => 'regular',
            ),
        );
        $defalut->addText(
            'sociallogin_section',
            array(
                'id'                => 'jaio_line_secret',
                'label'             => __('Line Channel Secret', 'plugin-name'),
                'desc'              => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="http://support.heateor.com/create-line-channel-for-line-login/" target="_blank">參考教學</a>
                                    </span>
                                </div>',
                'placeholder'       => '',
                'show_in_rest'      => false,
                'size'              => 'regular',
            ),
        );
    }

    function jaio_sync_data()
    {

        //LOGO
        $admin2020_options = get_option('admin2020_settings');
        $jaio_site_logo = (empty(get_option('jaio_site_logo'))) ? '13620' : get_option('jaio_site_logo');
        $jaio_login_bg = (empty(get_option('jaio_login_bg'))) ? '20147' : get_option('jaio_login_bg');

        //Social Login
        $the_champ_login = get_option('the_champ_login');
        $the_champ_login['enable'] = get_option('jaio_sociallogin_enable');
        $the_champ_login['fb_key'] = get_option('jaio_facebook_app');
        $the_champ_login['fb_secret'] = get_option('jaio_facebook_secret');
        $the_champ_login['google_key'] = get_option('jaio_google_app');
        $the_champ_login['google_secret'] = get_option('jaio_google_secret');
        $the_champ_login['line_channel_id'] = get_option('jaio_line_app');
        $the_champ_login['line_channel_secret'] = get_option('jaio_line_secret');

        if (!empty($the_champ_login['fb_key']) && !empty($the_champ_login['fb_secret'])) {
            $providers[] = 'facebook';
        }
        if (!empty($the_champ_login['google_key']) && !empty($the_champ_login['google_secret'])) {
            $providers[] = 'google';
        }
        if (!empty($the_champ_login['line_channel_id']) && !empty($the_champ_login['line_channel_secret'])) {
            $providers[] = 'line';
        }else{
            $providers = [];
        }
        $the_champ_login['providers'] = $providers;


        //update_option('the_champ_login', $the_champ_login);




        if (isset($_POST['submit'])) {
            //site logo
            $admin2020_options['modules']['admin2020_admin_bar']['light-logo'] =  wp_get_attachment_image_url($jaio_site_logo, 'large');

            $admin2020_options['modules']['admin2020_admin_login']['login-background'] = wp_get_attachment_image_url($jaio_login_bg, 'full');

            update_option('admin2020_settings', $admin2020_options);

            //social login
            update_option('the_champ_login', $the_champ_login);
        } else {
            if ($admin2020_options['modules']['admin2020_admin_bar']['light-logo'] ==  wp_get_attachment_image_url($jaio_site_logo, 'large') && $admin2020_options['modules']['admin2020_admin_login']['login-background'] == wp_get_attachment_image_url($jaio_login_bg, 'full')) {
                // do nothing
            } else {
                update_option('admin2020_settings', $admin2020_options);
            }
        }
    }


    function enqueue_admin_css()
    {
        $user_level = $this->jaio_get_current_user_level();
        wp_enqueue_style('Jerry_AIO admin_for_editor css', plugins_url('/../../assets/css/jaio_admin_level_' . $user_level . '.css', __FILE__));


        if ($this->jaio_simple_mode()) {
            wp_enqueue_style('Jerry_AIO simple mode css', plugins_url('/../../assets/css/jaio_admin_simple_mode.css', __FILE__));
        }
    }

    function enqueue_admin_js()
    {
        $user_level = $this->jaio_get_current_user_level();
        switch ($user_level) {
            case 0:
                wp_enqueue_script('Jerry_AIO admin js', plugins_url('/../../assets/js/jaio_admin_level_0.js', __FILE__));
                break;
            case 1:
                wp_enqueue_script('Jerry_AIO admin js', plugins_url('/../../assets/js/jaio_admin_level_0.js', __FILE__));
                wp_enqueue_script('Jerry_AIO admin js', plugins_url('/../../assets/js/jaio_admin_level_1.js', __FILE__));
                break;
            default:
                wp_enqueue_script('Jerry_AIO admin js', plugins_url('/../../assets/js/jaio_admin_level_0.js', __FILE__));
                wp_enqueue_script('Jerry_AIO admin js', plugins_url('/../../assets/js/jaio_admin_level_1.js', __FILE__));
                break;
        }
    }

    function enqueue_front_css()
    {
        wp_enqueue_style('Jerry_AIO front css', plugins_url('/../../assets/css/jaio_front.css', __FILE__));
    }

    function enqueue_front_js()
    {
        wp_enqueue_script('Jerry_AIO front js', plugins_url('/../../assets/js/jaio_front.js', __FILE__));
    }

    function remove_admin_bar()
    {
        $user_level = $this->jaio_get_current_user_level();
        if ($user_level > 0) {
            show_admin_bar(false);
        }

    }

    function jaio_simple_mode()
    {
        $jaio_simple_mode_enable = get_user_meta(get_current_user_id(), 'jaio_simple_mode_enable', true);
        return ($jaio_simple_mode_enable == 'enable');
    }


    function jaio_add_admin_footer()
    {
        /*$jaio_simple_mode_enable = get_user_meta(get_current_user_id(), 'jaio_simple_mode_enable', true);

        echo '<div class="jaio_simple_mode_btn" style="margin-left:100px;">';
        var_dump($jaio_simple_mode_enable);
        var_dump($jaio_simple_mode_enable);
        echo '</div>';*/

        $URL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $is_checked = ($this->jaio_simple_mode()) ? 'checked' : '';

        if (class_exists('WooCommerce', false)) {
            echo '<div class="jaio_simple_mode_btn">極簡模式<form class="jaio_simple_mode_form" action="' . $URL . '" method="post"><input type="checkbox" name="jaio_simple_mode_enable" value="enable" class="jaio_simple_mode" ' . $is_checked . ' /><input type="hidden" name="submit_ok" value="submit_ok" /></form>';
        }
    }


    function jaio_remove_filters()
    {
        global $wp_filter;
        $user_level = $this->jaio_get_current_user_level();

        /*
         * 解掉export order套件 select2.js的bug，詳情可以看
         * 位置：plugins\woo-order-export-lite\classes\class-wc-order-export-admin.php
         * add_filter( 'script_loader_src', array( $this, 'script_loader_src' ), 999, 2 );
        */
        unset($wp_filter['script_loader_src']->callbacks[999]);

        /*
         * 移除掉我以外的所有通知(priority 2)
         * priority 10全部屏蔽  999是admin2020
        */
        if ($user_level > 0) {
            unset($wp_filter['admin_notices']->callbacks[10]);
        }

        /*debug*/
        /*echo '<pre>';
        var_dump($wp_filter['admin_notices']->callbacks[10]);
        echo '</pre>';*/
        /*debug*/
    }

    function jaio_add_wp_head()
    {
?>
        <?php if (!empty(get_option('jaio_ga_track'))) : ?>
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo get_option('jaio_ga_track'); ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());

                gtag('config', '<?php echo get_option('jaio_ga_track'); ?>');
            </script>
        <?php endif; ?>
        <?php if (!empty(get_option('jaio_fb_track'))) : ?>
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
                fbq('init', '<?php echo get_option('jaio_ga_track'); ?>');
                fbq('track', 'PageView');
            </script>
            <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo get_option('jaio_ga_track'); ?>&ev=PageView&noscript=1" /></noscript>
            <!-- End Facebook Pixel Code -->
        <?php endif; ?>
        <link rel="shortcut icon" href="<?php echo wp_get_attachment_image_url(get_option('jaio_favicon'), 100); ?>">
        <script>
            let SITE_URL = "<?php echo site_url(); ?>";
        </script>
<?php
    }

    function jaio_add_register_form_field()
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

    function jaio_login_redirect($redirect_to, $request, $user)
    {
        if (class_exists('WooCommerce', false)) {
            $redirect_to = admin_url() . "admin.php?page=wc-admin&path=%2Fanalytics%2Foverview";
            return $redirect_to;
        } else {
            return $redirect_to;
        }
    }



    function jaio_toolbar($wp_admin_bar)
    {
        /*$test = $wp_admin_bar->get_nodes();
        echo '<pre>';
        var_dump($test);
        echo '</pre>';*/
        /*$wp_admin_bar->add_node([
            'id'      => 'seo',
            'title'   => 'SEO工具',
            'parent'  => '',
            'href'    => esc_url( admin_url( 'admin.php?page=theseoframework-settings' ) ),
            'group'   => false,
            'meta'    => [
                'class' => 'jaio_toolbar_btn'
            ],
        ]);
        $wp_admin_bar->add_node([
            'id'      => 'line',
            'title'   => 'Google Analytics',
            'parent'  => '',
            'href'    => esc_url( admin_url( 'admin.php?page=googlesitekit-splash' ) ),
            'group'   => false,
            'meta'    => [
                'class' => 'jaio_toolbar_btn'
            ],
        ]);
        $wp_admin_bar->add_menu([
            'id'      => 'clear-avada-cache',
            'title'   => '重置AVADA緩存',
            'parent'  => 'cache',
            'href'    => esc_url( admin_url( 'themes.php?page=avada_options' ) ),
            //'group'   => false,
            'meta'    => [
                'onclick' => 'fusionResetCaches(event);',
            ],
        ]);*/
    }

    function jaio_remove_dashboard_widgets()
    {
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // Right Now
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Recent Comments
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // Incoming Links
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');  // Quick Press
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');  // Recent Drafts
        remove_meta_box('dashboard_primary', 'dashboard', 'side');   // WordPress blog
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');   // Other WordPress News
        remove_meta_box('themefusion-news', 'dashboard', 'normal');
        remove_meta_box('wordfence_activity_report_widget', 'dashboard', 'normal');

        // use 'dashboard-network' as the second parameter to remove widgets from a network dashboard.
    }

    function jaio_profile_fields($contact_methods)
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

    function jaio_change_login_logo()
    {
        $logo_style = '<style type="text/css">';
        $logo_style .= '#backtoblog {display:none !important;}';
        $logo_style .= '#nav {margin-bottom:51px !important;}';
        $logo_style .= '</style>';
        echo $logo_style;
    }

    //預設註冊後不寄送信件
    function jaio_dontchecknotify_register_form()
    {
        echo '<scr' . 'ipt>jQuery(document).ready(function($) {
            $("#send_user_notification").removeAttr("checked");
        } ); </scr' . 'ipt>';
    }

    //修改用戶的註冊預設身分
    function jaio_change_default_role($default_role)
    {
        return 'customer';
        return $default_role;
    }

    function wd_admin_menu_rename()
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
                /*case 'edit.php?post_type=avada_portfolio':
                    $menu[$key][0] = '作品展示';
                    break;*/
                case 'edit.php?post_type=product':
                    $menu[$key][0] = '商品中心';
                    break;
                case 'users.php':
                    $menu[$key][0] = '用戶中心';
                    break;

                default:
                    # code...
                    break;
            }
        }
    }

    function jaio_remove_menu_page_level_1()
    {
        //remove_submenu_page( string $menu_slug, string $submenu_slug )

        //移除主選單
        //remove_menu_page('index.php');
        remove_menu_page('upload.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('edit.php?post_type=smart-custom-fields');
        //remove_menu_page('edit.php?post_type=avada_portfolio');
        remove_menu_page('edit.php?post_type=avada_faq');
        remove_menu_page('plugins.php');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
        remove_menu_page('theseoframework-settings');
        remove_menu_page('avada_sliders');
        remove_menu_page('themes.php');
        //移除weMail integrate
        remove_submenu_page('wemail', 'admin.php?page=wemail#/integrations');
        //分析 - 移除下載跟稅金
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/taxes');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/downloads');
    }
    function jaio_remove_menu_page_level_2()
    {
        $this->jaio_remove_menu_page_level_1();
        remove_submenu_page('jaio_setting', 'themes.php?page=avada_options');
        remove_submenu_page('jaio_setting', 'admin.php?page=avada-layouts');
    }
    function jaio_remove_menu_page_simple_mode()
    {
        remove_menu_page('index.php');
        remove_menu_page('admin.php?page=wc-settings');
        remove_menu_page('wemail');
        remove_menu_page('admin.php?page=theseoframework-settings');
        // 一般設定
        remove_submenu_page('jaio_setting', 'themes.php?page=avada_options');
        remove_submenu_page('jaio_setting', 'admin.php?page=avada-layouts');
        remove_submenu_page('jaio_setting', 'edit-tags.php?taxonomy=slide-page&post_type=slide');
        remove_submenu_page('jaio_setting', 'edit.php?post_type=slide');
        remove_submenu_page('jaio_setting', 'nav-menus.php');
        remove_submenu_page('jaio_setting', 'options-general.php?page=swpsmtp_settings#smtp');
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
            'manage_options',
            'admin.php?page=wc-settings&tab=checkout',
            '',
            'dashicons-store', //icon
            null
        );
        add_submenu_page(
            'admin.php?page=wc-settings&tab=checkout',
            '運費設定',
            '運費設定',
            'manage_options',
            'admin.php?page=wc-settings&tab=shipping',
            '',
            2
        );
    }

    //調整主選單順序
    function custom_menu_order($menu_ord)
    {
        if (!$menu_ord) return true;
        //--debug--//
        /*global $submenu;
        echo '<pre>';
        var_dump($submenu);
        echo '</pre>';*/
        //--debug--//

        if ($this->jaio_simple_mode()) {
            return array(
                'wc-admin&path=/analytics/overview',
                'edit.php?post_type=shop_order',
                'edit.php?post_type=product',
                'edit.php',
                'edit.php?post_type=page',
                'jaio_setting',
                'admin.php?page=wc-settings&tab=checkout',
                'users.php',
                'admin.php?page=avada-forms',
                'edit.php?post_type=shop_coupon',
                'admin.php?page=theseoframework-settings',
                'jaio_teach',
            );
        }

        return array(
            'index.php',
            'wc-admin&path=/analytics/overview',
            'edit.php?post_type=shop_order',
            'edit.php?post_type=product',
            'edit.php',
            'edit.php?post_type=page',
            'edit.php?post_type=avada_portfolio',
            'jaio_setting',
            'admin.php?page=wc-settings',
            'users.php',
            'admin.php?page=avada-forms',
            'edit.php?post_type=shop_coupon',
            'admin.php?page=theseoframework-settings',
            'wemail',
            'googlesitekit-dashboard',
            'jaio_teach',
        );
    }

    function jaio_admin_bar_render()
    {
        global $wp_admin_bar;
        //debug
        /*echo '<pre>';
        var_dump($wp_admin_bar);
        echo '</pre>';*/
        $wp_admin_bar->remove_menu('comments');
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



    // 印出所有的field
    function jaio_input_upload_file_login_logo()
    {
        echo "<input id='jaio_site_logo' class='jaio_upload_input' type='text' name='jaio_site_logo' value='" . get_option('jaio_site_logo') . "' />";
        echo "<input id='jaio_site_logo_btn' type='button' class='button jaio_upload_btn' value='上傳' />";
        echo "<p><img class='jaio_thumbnail' src='" . get_option('jaio_site_logo') . "' /></p>";
    }




    /* TEST */
    /* Add the media upload script */
    function jaio_enqueue_upload_script()
    {
        //Enqueue media.
        wp_enqueue_media();
        // Enqueue custom js file.
        wp_register_script('jaio_upload', plugin_dir_url(__FILE__) . '/../../assets/js/jaio_upload.js', array('jquery'));
        wp_enqueue_script('jaio_upload');
    }


    function jaio_teach_f()
    {
        echo '<h2>還在吸取日月精華...</h2>';
    }



    // 通过get_option()来显示存在数据库中的信息。
    // 以上填写的信息都存在了数据库中的wp_options表里面。


}

require_once(__DIR__ . '/class-woocmmerce.php');
require_once(__DIR__ . '/class-notice.php');
require_once(__DIR__ . '/class-metabox.php');
new Admin\JAIO\Woocommerce;
new Admin\JAIO\Notice;
new Admin\JAIO\MetaBox;
