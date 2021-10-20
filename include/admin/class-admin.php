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
        add_filter('custom_menu_order', '__return_true', 10);
        add_filter('menu_order', [$this, 'jdaio_menu_reorder'], 100, 1);

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
        add_action('admin_bar_menu', [$this, 'jdaio_toolbar'], 99);

        //login redirect
        add_filter('login_redirect', [$this, 'jdaio_login_redirect'], 100, 3);

        //add Social login at woocommerce register form
        //woocommerce_login_form_end | woocommerce_login_form_start
        add_action('woocommerce_login_form_end', [$this, 'jdaio_add_register_form_field'], 100);

        //change favicon
        add_action('wp_head', [$this, 'jdaio_add_wp_head']);
        add_action('admin_head', [$this, 'jdaio_add_wp_head']);
        add_action('admin_head', [$this, 'jdaio_add_admin_head']);


        //remove hook
        add_action('admin_init', [$this, 'jdaio_remove_filters'], 100);

        //add admin footer
        add_action('admin_footer', [$this, 'jdaio_add_admin_footer']);

        add_action('init', [$this, 'remove_admin_bar']);

        add_filter('bogo_localizable_post_types', [$this, 'jdaio_bogo_support_for_custom_post_types'], 10, 1);


        //redirect when user access wp-admin/
        add_action('admin_init', [$this, 'jdaio_set_admin_redirect']);

        //Disable comment
        add_action('init', [$this, 'jdaio_disable_comments_admin_bar']);
        add_action('admin_init', [$this, 'jdaio_disable_comments_post_types_support']);
        add_filter('comments_open', [$this, 'jdaio_disable_comments_status'], 20, 2);
        add_filter('pings_open', [$this, 'jdaio_disable_comments_status'], 20, 2);
        add_filter('comments_array', [$this, 'jdaio_disable_comments_hide_existing_comments'], 10, 2);

        add_filter('add_et_builder_role_options', [$this, 'jdaio_add_role_to_et_builder_role_options'], 10, 2);

        //圖片  移除所有圖片尺寸
        add_action('init', [$this, 'jdaio_remove_all_image_sizes']);
        add_filter('big_image_size_threshold', function () {
            return 20000;
        });
        add_filter('jpeg_quality', function () {
            return 100;
        });


        //自訂後台標題
        add_filter('admin_title', [$this, 'jdaio_admin_title'], 99, 2);

        //CHATBUTTON 前端顯示
        add_action('wp_footer', [$this, 'jdaio_add_chatbutton_frontend']);
    }


    //把下列用戶也加入Theme Builder的權限管理
    public function jdaio_add_role_to_et_builder_role_options($all_role_options)
    {
        $new_role = [
            'designer',
            'shop_manager',
            'shop_manager_super',
        ];

        $all_role_options["general_capabilities"]["options"]["theme_builder"]["applicability"] = array_merge($all_role_options["general_capabilities"]["options"]["theme_builder"]["applicability"], $new_role);


        return $all_role_options;
    }

    /**
     * Support custom post type with bogo.
     * @param array $ locallyizable Supported post types.
     */
    public function jdaio_bogo_support_for_custom_post_types($localizable)
    {
        if (class_exists('Bogo_POMO', false)) {
            $args = array(
                'public' => true,
                '_builtin' => false,
            );
            $custom_post_types = get_post_types($args);
            return array_merge($localizable, $custom_post_types);
        }
    }

    //代辦：9li.uk-open選單會打開
    //自訂後台頁面
    public function jdaio_amp_setting()
    {
        switch (self::$current_user_level) {
            case 0:
                $this->jdaio_remove_menu_page_level_0();
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

        //流量中心
        if (class_exists('WP_Statistics', false)) {
            add_menu_page(
                __('Traffic', 'Jerry_Divi_AIO'),
                __('Traffic', 'Jerry_Divi_AIO'),
                'read',
                'admin.php?page=wps_overview_page',
                '',
                'dashicons-chart-line', //icon
                null
            );
        }


        //訂單中心
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                __('Oders', 'Jerry_Divi_AIO'),
                __('Oders', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'edit.php?post_type=shop_order',
                '',
                'dashicons-cart', //icon
                null
            );

            if (class_exists('WC_Order_Export_Admin', false)) {
                add_submenu_page(
                    'edit.php?post_type=shop_order',
                    __('Export Oders', 'Jerry_Divi_AIO'),
                    __('Export Oders', 'Jerry_Divi_AIO'),
                    'edit_shop_orders',
                    'admin.php?page=wc-order-export#segment=common',
                    '',
                    2
                );
            }

            if (class_exists('Zorem_Woocommerce_Advanced_Shipment_Tracking', false)) {

                add_menu_page(
                    __('Shipping', 'Jerry_Divi_AIO'),
                    __('Shipping', 'Jerry_Divi_AIO'),
                    'edit_shop_orders',
                    'admin.php?page=woocommerce-advanced-shipment-tracking',
                    '',
                    'dashicons-car', //icon
                    null
                );
            }
        }

        /*if (class_exists('User_import_export_Review_Request', false)) {
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
        }*/

        //行銷中心
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                __('Marketing', 'Jerry_Divi_AIO'),
                __('Marketing', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'edit.php?post_type=shop_coupon',
                '',
                'dashicons-megaphone', //icon
                null
            );
            add_submenu_page(
                'edit.php?post_type=shop_coupon',
                __('Coupons', 'Jerry_Divi_AIO'),
                __('Coupons', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'edit.php?post_type=shop_coupon',
                '',
                2
            );

            if (class_exists('WooCommerce_Coupon_Generator', false)) {
                add_submenu_page(
                    'edit.php?post_type=shop_coupon',
                    __('Generate Coupons', 'Jerry_Divi_AIO'),
                    __('Generate Coupons', 'Jerry_Divi_AIO'),
                    'edit_shop_orders',
                    'admin.php?page=woocommerce_coupon_generator',
                    '',
                    3
                );
            }
            //if (class_exists('The_SEO_Framework\Core', false)) {
            add_submenu_page(
                'edit.php?post_type=shop_coupon',
                __('SEO Settings', 'Jerry_Divi_AIO'),
                __('SEO Settings', 'Jerry_Divi_AIO'),
                'read',
                'admin.php?page=theseoframework-settings',
                '',
                4
            );
            //}
        } else {
            add_menu_page(
                __('Marketing', 'Jerry_Divi_AIO'),
                __('Marketing', 'Jerry_Divi_AIO'),
                'read',
                'admin.php?page=theseoframework-settings',
                '',
                'dashicons-megaphone', //icon
                null
            );
        }

        //網站設定

        add_submenu_page(
            'jdaio_setting',
            __('Homepage', 'Jerry_Divi_AIO'),
            __('Homepage', 'Jerry_Divi_AIO'),
            'edit_shop_orders',
            'post.php?post=' . get_option('page_on_front') . '&action=edit',
            '',
            2
        );
        add_submenu_page(
            'jdaio_setting',
            __('Menus', 'Jerry_Divi_AIO'),
            __('Menus', 'Jerry_Divi_AIO'),
            'edit_shop_orders',
            'nav-menus.php',
            '',
            3
        );

        if (class_exists('EasyWPSMTP', false)) {
            add_submenu_page(
                'jdaio_setting',
                __('Email Settings', 'Jerry_Divi_AIO'),
                __('Email Settings', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'options-general.php?page=swpsmtp_settings#smtp',
                '',
                4
            );
        }

        //網站外觀選項
        $theme = wp_get_theme()->Name;
        if (strpos($theme, 'Divi') !== false) {

            add_menu_page(
                __('Theme Builder', 'Jerry_Divi_AIO'),
                __('Theme Builder', 'Jerry_Divi_AIO'),
                'edit_theme_options',
                'admin.php?page=et_theme_builder',
                '',
                'dashicons-admin-appearance', //icon
                null
            );
            add_submenu_page(
                'admin.php?page=et_theme_builder',
                __('Slider Revolution', 'Jerry_Divi_AIO'),
                __('Slider Revolution', 'Jerry_Divi_AIO'),
                'edit_theme_options',
                'admin.php?page=revslider',
                '',
                3
            );
            if (class_exists('DIPL_DiviPlus', false)) {
                add_submenu_page(
                    'admin.php?page=et_theme_builder',
                    __('Testimonial', 'Jerry_Divi_AIO'),
                    __('Testimonial', 'Jerry_Divi_AIO'),
                    'edit_theme_options',
                    'edit.php?post_type=dipl-testimonial',
                    '',
                    5
                );
                add_submenu_page(
                    'admin.php?page=et_theme_builder',
                    __('Team', 'Jerry_Divi_AIO'),
                    __('Team', 'Jerry_Divi_AIO'),
                    'edit_theme_options',
                    'edit.php?post_type=dipl-team-member',
                    '',
                    4
                );
            }
            if (class_exists('DiviMegaPro', false)) {
                add_submenu_page(
                    'admin.php?page=et_theme_builder',
                    'MEGA MENU',
                    'MEGA MENU',
                    'edit_theme_options',
                    'edit.php?post_type=divi_mega_pro',
                    '',
                    5
                );
            }

            add_submenu_page(
                'admin.php?page=et_theme_builder',
                __('Library', 'Jerry_Divi_AIO'),
                __('Library', 'Jerry_Divi_AIO'),
                'edit_theme_options',
                'edit.php?post_type=et_pb_layout',
                '',
                6
            );
            /*add_submenu_page(
                'et_theme_builder',
                '基礎外觀定義',
                '基礎外觀定義',
                'edit_theme_options',
                'customize.php?et_customizer_option_set=theme',
                '',
                2
            );*/


            /*add_submenu_page(
                'et_theme_builder',
                '進階設定',
                '進階設定',
                'edit_theme_options',
                'admin.php?page=et_divi_options',
                '',
                4
            );*/
        }


        //網路商店設定
        if (class_exists('WooCommerce', false)) {
            add_menu_page(
                __('Store Setting', 'Jerry_Divi_AIO'),
                __('Store Setting', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'admin.php?page=wc-settings',
                '',
                'dashicons-store', //icon
                null
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                __('Shipping Cost', 'Jerry_Divi_AIO'),
                __('Shipping Cost', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'admin.php?page=wc-settings&tab=shipping',
                '',
                2
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                __('Payment Method', 'Jerry_Divi_AIO'),
                __('Payment Method', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'admin.php?page=wc-settings&tab=checkout',
                '',
                3
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                __('Privacy', 'Jerry_Divi_AIO'),
                __('Privacy', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'admin.php?page=wc-settings&tab=account',
                '',
                4
            );
            add_submenu_page(
                'admin.php?page=wc-settings',
                __('Email Notification', 'Jerry_Divi_AIO'),
                __('Email Notification', 'Jerry_Divi_AIO'),
                'edit_shop_orders',
                'admin.php?page=wc-settings&tab=email',
                '',
                5
            );

            if (class_exists('THWCFD', false)) {
                add_submenu_page(
                    'admin.php?page=wc-settings',
                    __('Checkout Form', 'Jerry_Divi_AIO'),
                    __('Checkout Form', 'Jerry_Divi_AIO'),
                    'edit_shop_orders',
                    'admin.php?page=checkout_form_designer&tab=fields',
                    '',
                    6
                );
            }

            if (class_exists('WC_Facebookcommerce', false)) {
                add_submenu_page(
                    'admin.php?page=wc-settings',
                    __('Connect Facebook', 'Jerry_Divi_AIO'),
                    __('Connect Facebook', 'Jerry_Divi_AIO'),
                    'edit_shop_orders',
                    'admin.php?page=wc-facebook',
                    '',
                    7
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


        if (DEV_ENV) {
            //教學中心
            add_menu_page(
                __('Tutorial', 'Jerry_Divi_AIO'),
                __('Tutorial', 'Jerry_Divi_AIO'),
                'read',
                'jdaio_teach',
                [$this, 'jdaio_teach_page'],
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
                'page_title' => __('Setting', 'Jerry_Divi_AIO'),
                'menu_title' => __('Setting', 'Jerry_Divi_AIO'),
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
                    'title' => __('基礎設定', 'Jerry_Divi_AIO'),
                    //'desc'  => __( 'These are general settings for Plugin Name', 'Jerry_Divi_AIO' ),
                ),
                array(
                    'id' => 'chatbutton_section',
                    'title' => __('聊天按鈕', 'Jerry_Divi_AIO'),
                    //'desc'  => __( 'These are advance settings for Plugin Name', 'Jerry_Divi_AIO' )
                ),
                array(
                    'id' => 'tracking_section',
                    'title' => __('網站追蹤設定', 'Jerry_Divi_AIO'),
                    //'desc'  => __( 'These are advance settings for Plugin Name', 'Jerry_Divi_AIO' )
                ),
                array(
                    'id' => 'sociallogin_section',
                    'title' => __('社群登入', 'Jerry_Divi_AIO'),
                    //'desc'  => __( 'These are advance settings for Plugin Name', 'Jerry_Divi_AIO' )
                )
            )
        );
        //---------- GENERAL SECTION ----------//
        $defalut->addText(
            'general_section',
            array(
                'id' => 'blogname',
                'label' => __('網站名稱', 'Jerry_Divi_AIO'),
                'desc' => __('Some description of my field', 'Jerry_Divi_AIO'),
                'placeholder' => '請輸入網站名稱',
                'show_in_rest' => true,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'general_section',
            array(
                'id' => 'blogname',
                'label' => __('網站名稱', 'Jerry_Divi_AIO'),
                //'desc'              => __( 'Some description of my field', 'Jerry_Divi_AIO' ),
                'placeholder' => '請輸入網站名稱',
                'show_in_rest' => true,
                'size' => 'regular',
            ),
        );
        $defalut->addTextarea(
            'general_section',
            array(
                'id' => 'blogdescription',
                'label' => __('網站說明', 'Jerry_Divi_AIO'),
                //'desc'        => __( 'Textarea description', 'Jerry_Divi_AIO' ),
                'placeholder' => __('請說明你的網站特色', 'Jerry_Divi_AIO'),
            ),
        );
        $defalut->addMedia(
            'general_section',
            array(
                'id' => 'jdaio_site_logo',
                'label' => __('網站 LOGO', 'Jerry_Divi_AIO'),
                'desc' => __('建議尺寸300X300，支援JPG/PNG圖檔', 'Jerry_Divi_AIO'),
                'type' => 'media',
                'options' => array(
                    'btn' => __('選擇圖片', 'Jerry_Divi_AIO'),
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
                'label' => __('網站小圖標(favicon)', 'Jerry_Divi_AIO'),
                'desc' => __('建議尺寸100X100，支援JPG/PNG圖檔', 'Jerry_Divi_AIO'),
                'type' => 'media',
                'options' => array(
                    'btn' => __('選擇圖片', 'Jerry_Divi_AIO'),
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
                'label' => __('登入頁背景', 'Jerry_Divi_AIO'),
                'desc' => __('建議尺寸1980X1080，支援JPG/PNG圖檔', 'Jerry_Divi_AIO'),
                'type' => 'media',
                'options' => array(
                    'btn' => __('選擇圖片', 'Jerry_Divi_AIO'),
                    //'width'     => 1000,
                    //'max_width' => 1000,
                ),
                'default' => wp_get_attachment_image_url(20147, 'full'),
            ),
        );
        //---------- ChatButton SECTION ----------//

        //檢查套件是否有開，模組化
        $messenger_activate = get_option('jdaio_chatbutton_fb_enable', '');
        if (!empty($messenger_activate)) {
            activate_plugin("facebook-messenger-customer-chat/facebook-messenger-customer-chat.php");
        } else {
            deactivate_plugins("facebook-messenger-customer-chat/facebook-messenger-customer-chat.php");
        }

        $fb_chat_plugin_activate = is_plugin_active("facebook-messenger-customer-chat/facebook-messenger-customer-chat.php");
        $checked = ($fb_chat_plugin_activate) ? 'checked' : '';
        $jdaio_chatbutton_fb_edit = ($fb_chat_plugin_activate) ? '<br><a href="https://www.facebook.com/login.php?next=https%3A%2F%2Fwww.facebook.com%2Fcustomer_chat%2Fdialog%2F%3Fdomain%3D' . site_url() .  '" target="_blank" class="button-primary">編輯 Facebook Messenger</a>' : '';
        $defalut->addCheckboxes(
            'chatbutton_section',
            array(
                'id' => 'jdaio_chatbutton_fb_enable',
                'class' => 'submit_on_change',
                'label' => __('啟用 FB 即時聊天', 'Jerry_Divi_AIO'),
                'desc' => $jdaio_chatbutton_fb_edit . '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;"><span class="material-icons-outlined uk-margin-small-right">info</span><span>用戶可在網頁直接傳送訊息</span></div>',
                'options' => array(
                    '1' => '啟用',
                ),
            ),
        );



        $defalut->addText(
            'chatbutton_section',
            array(
                'id' => 'jdaio_chatbutton_line',
                'label' => __('輸入 LINE 連結', 'Jerry_Divi_AIO'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                                    <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="https://www.pkstep.com/archives/5261" target="_blank">如何產生 LINE 連結</a>
                                    </span>
                                </div>',
                'placeholder' => '',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'chatbutton_section',
            array(
                'id' => 'jdaio_chatbutton_tg',
                'label' => __('輸入 Telegram 連結', 'Jerry_Divi_AIO'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="https://www.inside.com.tw/article/18743-Telegram-username" target="_blank">如何產生 Telegram 連結</a>
                                    </span>
            </div>',
                'placeholder' => '例如：https://t.me/telegram',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'chatbutton_section',
            array(
                'id' => 'jdaio_chatbutton_ig',
                'label' => __('輸入 Instagram 連結', 'Jerry_Divi_AIO'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="https://www.tech-girlz.com/2020/08/instagram-bio-link.html" target="_blank">如何產生 Instagram 連結</a>
                                    </span>
            </div>',
                'placeholder' => '例如：https://www.instagram.com/instagram',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'chatbutton_section',
            array(
                'id' => 'jdaio_chatbutton_whatsapp',
                'label' => __('輸入 WhatsApp 連結', 'Jerry_Divi_AIO'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                                        <a href="https://moredigital.com.hk/2018/07/23/%E7%B6%B2%E5%BA%97%E5%BF%85%E5%AD%B8-30-%E7%A7%92%E5%AE%8C%E6%88%90%E8%A8%AD%E5%AE%9A-direct-whatsapp/" target="_blank">如何產生 WhatsApp 連結</a>
                                    </span>
            </div>',
                'placeholder' => 'https://wa.me/[區號][你的手提電話號碼] ，例如：https://wa.me/886/912345678',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'chatbutton_section',
            array(
                'id' => 'jdaio_chatbutton_email',
                'label' => __('輸入 EMAIL', 'Jerry_Divi_AIO'),
                'desc' => '',
                'placeholder' => '例如：my_name@gmail.com',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        $defalut->addText(
            'chatbutton_section',
            array(
                'id' => 'jdaio_chatbutton_phone',
                'label' => __('輸入手機號碼', 'Jerry_Divi_AIO'),
                'desc' => '<div class="uk-flex a2020-notification-tag" style="border-bottom:none;">
                <span class="material-icons-outlined uk-margin-small-right">info</span><span>
                請不要輸入-等符號
                </span>
            </div>',
                'placeholder' => '例如：+886912345678',
                'show_in_rest' => false,
                'size' => 'regular',
            ),
        );
        //---------- TRACKING SECTION ----------//
        $defalut->addText(
            'tracking_section',
            array(
                'id' => 'jdaio_fb_track',
                'label' => __('Facebook Pixel ID', 'Jerry_Divi_AIO'),
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
                'label' => __('Google Analytics tracking ID', 'Jerry_Divi_AIO'),
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
                'label' => __('啟用社群登入', 'Jerry_Divi_AIO'),
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
                'label' => __('Facebook App ID', 'Jerry_Divi_AIO'),
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
                'label' => __('Facebook App Secret', 'Jerry_Divi_AIO'),
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
                'label' => __('Google Client ID', 'Jerry_Divi_AIO'),
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
                'label' => __('Google Client Secret', 'Jerry_Divi_AIO'),
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
                'label' => __('Line Channel ID', 'Jerry_Divi_AIO'),
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
                'label' => __('Line Channel Secret', 'Jerry_Divi_AIO'),
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

    public function jdaio_add_chatbutton_frontend()
    {
        $fb_chat_plugin_activate = is_plugin_active("facebook-messenger-customer-chat/facebook-messenger-customer-chat.php");
        $class = ($fb_chat_plugin_activate) ? 'jdaio_chatbutton' : 'jdaio_chatbutton_no_fb';

        $chatbutton_order = ['jdaio_chatbutton_phone', 'jdaio_chatbutton_email', 'jdaio_chatbutton_whatsapp', 'jdaio_chatbutton_ig', 'jdaio_chatbutton_tg', 'jdaio_chatbutton_line'];

        $html_btn = '';
        foreach ($chatbutton_order as $button) {
            if (!empty(get_option($button))) {
                switch ($button) {
                    case 'jdaio_chatbutton_email':
                        $prefix = 'mailto:';
                        break;
                    case 'jdaio_chatbutton_phone':
                        $prefix = 'tel://';
                        break;
                    default:
                        $prefix = '';
                        break;
                }
                $html_btn .= '<a href="' . $prefix . get_option($button) . '" class="' . $button . '" target="_blank"></a>';
            }
        }
        if (empty($html_btn)) return;

        $html = '';
        $html .= '<div class="' . $class . '">';

        $html .= '<div class="chatbutton_content">';

        /*$html .= '<i class="fas fa-arrow-from-right"></i>';
        $html .= '<i class="fas fa-arrow-from-left"></i>';*/
        $html .= '<i class="fad fa-reply-all"></i>';
        $html .= '<i class="fad fa-share-all"></i>';
        $html .= '<div class="chatbutton_content_inner">';
        $html .= '<div class="chatbutton_content_inner_scroll">';
        $html .= $html_btn;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        echo $html;
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
        wp_enqueue_style('Jerry_Divi_AIO front css', plugins_url('/../../assets/css/jdaio_front_level_' . self::$current_user_level . '.css', __FILE__), array(), '1.0.1');

        if (FA_ENABLE) {
            wp_enqueue_style('fontawesome_css', plugins_url('/../../assets/fontawesome/css/all.min.css', __FILE__));
        }
        if (FLIPSTER_ENABLE) {
            wp_enqueue_style('flipster_css', plugins_url('/../../assets/flipster/jquery.flipster.min.css', __FILE__));
        }
    }

    public function enqueue_front_js()
    {
        wp_enqueue_script('Jerry_Divi_AIO front js', plugins_url('/../../assets/js/jdaio_front.js', __FILE__));
        if (FA_ENABLE) {
            wp_enqueue_script('fontawesome_js',  plugins_url('/../../assets/fontawesome/js/all.min.js', __FILE__));
        }
        if (FLIPSTER_ENABLE) {
            wp_enqueue_script('flipster_js', plugins_url('/../../assets/flipster/jquery.flipster.min.js', __FILE__));
        }
    }

    public function remove_admin_bar()
    {
        // level 0跟1可以看到menu bar
        if (self::$current_user_level > 1) {
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

        /*$URL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $is_checked = ($this->jdaio_simple_mode()) ? 'checked' : '';

        if (class_exists('WooCommerce', false)) {
            echo '<div class="jdaio_simple_mode_btn">極簡模式<form class="jdaio_simple_mode_form" action="' . $URL . '" method="post"><input type="checkbox" name="jdaio_simple_mode_enable" value="enable" class="jdaio_simple_mode" ' . $is_checked . ' /><input type="hidden" name="submit_ok" value="submit_ok" /></form>';
        }*/
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
            unset($wp_filter['admin_title']->callbacks[10]); //WC title
            unset($wp_filter['admin_notices']->callbacks[20]); //wp-statistic
        }

        /*debug*/
        /*echo '<pre>';
  var_dump($wp_filter['admin_title']);
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

        <style>
            :root {
                --primary: <?php echo @et_get_option('accent_color', '#2ea3f2') ?>;
                --secondary: #45C5AF;
                --woocommerce: #2ea2cc;
                --wc-green: #7ad03a;
                --wc-red: #ffa4a4;
                --wc-orange: #ffba00;
                --wc-blue: #2ea2cc;
                --wc-primary: #2ea2cc;
                --wc-primary-hover: #4bb7df;
                --wc-primary-text: white;
                --wc-secondary: #ebe9eb;
                --wc-secondary-text: #515151;
                --wc-highlight: #77a464;
                --wc-highligh-text: white;
                --wc-content-bg: #fff;
                --wc-subtext: #767676;
            }
        </style>
        <?php
    }

    public function jdaio_add_admin_head()
    {

        if (!PROJECT_OPEN) :
        ?>
            <style>
                #menu-posts-project {
                    display: none;
                }
            </style>
<?php
        endif;
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
        if (class_exists('WP_Statistics', false)) {
            $redirect_to = admin_url() . 'admin.php?page=wps_overview_page';
            return $redirect_to;
        } else {
            return $redirect_to;
        }
    }

    function jdaio_set_admin_redirect()
    {
        if (class_exists('WP_Statistics', false)) {
            global $pagenow;
            if ($pagenow === 'index.php') {
                $redirect_to = admin_url() . 'admin.php?page=wps_overview_page';
                wp_redirect($redirect_to);
                exit;
            }
        }
    }

    function jdaio_toolbar(WP_Admin_Bar $admin_bar)
    {
        /**
         * https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/
         */



        $args = array(
            'id'    => 'sitetool',
            'title' => '站長工具',
            'meta'  => array(
                'class' => 'uk-background-muted uk-border-rounded '
            ),
        );
        $admin_bar->add_node($args);

        $args = array(
            'parent' => 'sitetool',
            'id'     => 'google-analytics',
            'title'  => 'Google Analytics',
            'href'   => 'https://analytics.google.com/',
            'meta'   => array(
                'target' => '_blank'
            ),
        );
        $admin_bar->add_node($args);

        $args = array(
            'parent' => 'sitetool',
            'id'     => 'google-console',
            'title'  => 'Google Console',
            'href'   => 'https://accounts.google.com/ServiceLogin?service=sitemaps&hl=zh-TW&continue=https://search.google.com/search-console?hl%3Dzh-tw%26utm_source%3Dabout-page',
            'meta'   => array(
                'target' => '_blank'
            ),
        );
        $admin_bar->add_node($args);

        if (class_exists('WooCommerce', false)) {
            $args = array(
                'parent' => 'sitetool',
                'id'     => 'ecpay',
                'title'  => '綠界科技',
                'href'   => 'https://www.ecpay.com.tw/',
                'meta'   => array(
                    'target' => '_blank'
                ),
            );
            $admin_bar->add_node($args);
        }


        /*$wp_admin_bar->add_node([
            'id'      => 'line',
            'title'   => 'Google Analytics',
            'parent'  => '',
            'href'    => esc_url( admin_url( 'admin.php?page=googlesitekit-splash' ) ),
            'group'   => false,
            'meta'    => [
            'class' => 'jdaio_toolbar_btn'
            ],
            ]);*/
    }



    public function jdaio_remove_dashboard_widgets()
    {

        remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // Right Now
        // Remove comments metabox from dashboard
        if (!COMMENTS_OPEN) {
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
        //var_dump($menu);
        foreach ($menu as $key => $menu_array) {

            switch ($menu_array[2]) {
                case 'edit.php':
                    $menu[$key][0] = __('Posts', 'Jerry_Divi_AIO');
                    break;
                case 'edit.php?post_type=page':
                    $menu[$key][0] = __('Pages', 'Jerry_Divi_AIO');
                    break;
                case 'edit.php?post_type=project':
                    $menu[$key][0] = __('Projects', 'Jerry_Divi_AIO');
                    break;
                case 'edit.php?post_type=dipl-testimonial':
                    $menu[$key][0] = __('Testimonial', 'Jerry_Divi_AIO');
                    break;
                case 'edit.php?post_type=dipl-team-member':
                    $menu[$key][0] = __('Team', 'Jerry_Divi_AIO');
                    break;
                case 'edit.php?post_type=product':
                    $menu[$key][0] = __('Products', 'Jerry_Divi_AIO');
                    break;
                case 'users.php':
                    $menu[$key][0] = __('Users', 'Jerry_Divi_AIO');
                    break;
                case 'wc-admin&path=/analytics/overview':
                    $menu[$key][0] = __('Analytics', 'Jerry_Divi_AIO');
                    break;
                case 'wps_overview_page':
                    $menu[$key][0] = __('Traffic', 'Jerry_Divi_AIO');
                    break;
                case 'loco':
                    $menu[$key][0] = __('Translate', 'Jerry_Divi_AIO');
                    break;
                case 'upload.php':
                    $menu[$key][0] = __('Uploads', 'Jerry_Divi_AIO');
                    break;


                default:
                    # code...
                    break;
            }
        }

        global $submenu;

        foreach ($submenu["users.php"] as $key => $submenu_array) {
            switch ($submenu_array[2]) {
                case 'import-export-menu-old':
                    $submenu["users.php"][$key][0] = __('Export User', 'Jerry_Divi_AIO');
                    break;

                default:
                    # code...
                    break;
            }
        }
        //var_dump($submenu["et_divi_options"]);





        /*echo '<pre>';
        var_dump($submenu["upload.php"]);
        echo '</pre>';*/
    }
    public function jdaio_remove_menu_page_level_0()
    {
        remove_menu_page('revslider');
        remove_menu_page('theseoframework-settings');
    }

    public function jdaio_remove_menu_page_level_1()
    {
        $this->jdaio_remove_menu_page_level_0();
        //remove_submenu_page( string $menu_slug, string $submenu_slug )
        //移除主選單
        remove_menu_page('index.php');

        if (!COMMENTS_OPEN) {
            remove_menu_page('edit-comments.php');
        }
        remove_menu_page('plugins.php');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
        remove_menu_page('themes.php');
        remove_menu_page('et_bloom_options');
        remove_menu_page('et_divi_options');
        remove_menu_page('theseoframework-settings');
        remove_menu_page('wps_overview_page');
        remove_menu_page('facebook-messenger-customer-chat');
        remove_menu_page('edit.php?post_type=divi_mega_pro');
        remove_menu_page('wpclever');
        remove_menu_page('edit.php?post_type=dipl-testimonial');
        remove_menu_page('edit.php?post_type=dipl-team-member');
        remove_menu_page('media-cloud');
        remove_menu_page('media-cloud-tools');
        remove_menu_page('WP-Optimize');




        //分析 - 移除下載跟稅金
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/taxes');
        remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/downloads');

        //remove_menu_page('upload.php');
        remove_submenu_page('upload.php', 'upload.php');
        remove_submenu_page('upload.php', 'media-new.php');
        remove_submenu_page('upload.php', 'ewww-image-optimizer-bulk');


        //WP statistic
        /*remove_submenu_page('wps_overview_page', 'wps_overview_page');
        remove_submenu_page('wps_overview_page', 'wps_hits_page');
        remove_submenu_page('wps_overview_page', 'wps_visitors_page');
        remove_submenu_page('wps_overview_page', 'wps_referrers_page');
        remove_submenu_page('wps_overview_page', 'wps_words_page');
        remove_submenu_page('wps_overview_page', 'wps_searches_page');
        remove_submenu_page('wps_overview_page', 'wps_pages_page');
        remove_submenu_page('wps_overview_page', 'wps_categories_page');
        remove_submenu_page('wps_overview_page', 'wps_tags_page');
        remove_submenu_page('wps_overview_page', 'wps_browser_page');
        remove_submenu_page('wps_overview_page', 'wps_platform_page');
        remove_submenu_page('wps_overview_page', 'wps_top-visitors_page');
        remove_submenu_page('wps_overview_page', 'wps_optimization_page');
        remove_submenu_page('wps_overview_page', 'wps_authors_page');
        remove_submenu_page('wps_overview_page', 'wps_settings_page');
        remove_submenu_page('wps_overview_page', 'wps_plugins_page');
        remove_submenu_page('wps_overview_page', 'wps_donate_page');
        */
    }
    public function jdaio_remove_menu_page_level_2()
    {
        $this->jdaio_remove_menu_page_level_1();
        remove_menu_page('loco');
        remove_menu_page('ags-layouts');
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
    public function jdaio_menu_reorder($menu_ord)
    {
        if (!$menu_ord) {
            return true;
        }
        global $menu;
        //--debug--//
        /*echo '<pre>';
        var_dump($menu);
        echo '</pre>';*/
        //--debug--//

        if ($this->jdaio_simple_mode()) {
            return array(
                'admin.php?page=wps_overview_page',
                'wc-admin&path=/analytics/overview',
                'edit.php?post_type=shop_order',
                'admin.php?page=woocommerce-advanced-shipment-tracking',
                'edit.php?post_type=product',
                'edit.php',
                'edit.php?post_type=page',
                'edit.php?post_type=project',
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
            'admin.php?page=wps_overview_page',
            'wc-admin&path=/analytics/overview',
            'edit.php?post_type=shop_order',
            'admin.php?page=woocommerce-advanced-shipment-tracking',
            'edit.php?post_type=product',
            'edit.php',
            'edit.php?post_type=page',
            'edit.php?post_type=project',
            'admin.php?page=et_theme_builder',
            'jdaio_setting',
            'admin.php?page=wc-settings',
            'users.php',
            'edit.php?post_type=shop_coupon',
            'admin.php?page=theseoframework-settings',
            'upload.php',
            'loco',
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
        if (!COMMENTS_OPEN) {
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

    function jdaio_remove_all_image_sizes()
    {
        foreach (get_intermediate_image_sizes() as $size) {
            remove_image_size($size);
        }
    }

    function jdaio_admin_title($admin_title, $title)
    {
        return $title;
    }





    /*public function jdaio_shipping_dtod_page(){
        include_once 'template/shipping_dtod_page.php';
    }*/

    public function jdaio_teach_page()
    {
        echo '<h2>還在吸取日月精華...</h2>';
    }


    public function jdaio_disable_comments_post_types_support()
    {

        if (!COMMENTS_OPEN) {
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
    }

    // Close comments on the front-end
    public function jdaio_disable_comments_status()
    {
        return COMMENTS_OPEN;
    }

    // Hide existing comments
    public function jdaio_disable_comments_hide_existing_comments($comments)
    {
        if (!COMMENTS_OPEN) {
            $comments = array();
            return $comments;
        }
    }

    // Remove comments links from admin bar
    public function jdaio_disable_comments_admin_bar()
    {
        if (!COMMENTS_OPEN) {
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
