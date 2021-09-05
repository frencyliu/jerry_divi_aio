<?php

/**
 * customise admin
 */

namespace Sync\JDAIO;

use Jerry_Divi_AIO;

defined('ABSPATH') || exit;


class Page extends Jerry_Divi_AIO
{


    public function __construct()
    {


        add_action('admin_head', [$this, 'jdaio_create_default_page']);
        add_action('admin_head', [$this, 'jdaio_hide_post']);
        add_action('init', [$this, 'jdaio_add_shortcode']);
    }

    function jdaio_create_default_page()
    {
        //auto create register page
        if (class_exists('TheChampLoginWidget', false)) {
            $register_page_exist = post_exists('Register', '', '', 'page');
            if ($register_page_exist == 0) {
                $postarr = [
                    'post_content'  => '[wc_reg_form_bbloomer]',
                    'post_title'    => 'Register',
                    'post_status'   => 'publish',
                    'post_type'     => 'page',
                ];
                //新增文章
                $r_post_id = wp_insert_post($postarr);
                //新增預設LAYOUT
                update_post_meta( $r_post_id, '_et_pb_page_layout', 'et_no_sidebar');

            }
        }
    }
    function jdaio_add_shortcode()
    {
        add_shortcode('wc_reg_form_bbloomer', [$this, 'bbloomer_separate_registration_form']);
    }


    function bbloomer_separate_registration_form()
    {
        ob_start();
        /*
         * custum register field
         * https://www.cloudways.com/blog/add-woocommerce-registration-form-fields/
         */
        if (is_admin() || is_user_logged_in()) {
            wp_redirect(site_url());
            return;
        }


        // NOTE: THE FOLLOWING <FORM></FORM> IS COPIED FROM woocommerce\templates\myaccount\form-login.php
        // IF WOOCOMMERCE RELEASES AN UPDATE TO THAT TEMPLATE, YOU MUST CHANGE THIS ACCORDINGLY

        do_action('woocommerce_before_customer_login_form');

?>

        <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>
        <h2 class="register_title">註冊</h2>

            <?php do_action('woocommerce_register_form_start'); ?>

            <?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" /><?php // @codingStandardsIgnoreLine
                                                                                                                                                                                                                                                                                ?>
                </p>

            <?php endif; ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="reg_email"><?php esc_html_e('Email address', 'woocommerce'); ?> <span class="required">*</span></label>
                <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" /><?php // @codingStandardsIgnoreLine
                                                                                                                                                                                                                                                            ?>
            </p>

            <?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="reg_password"><?php esc_html_e('Password', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
                </p>

            <?php else : ?>

                <p><?php esc_html_e('A password will be sent to your email address.', 'woocommerce'); ?></p>

            <?php endif; ?>

            <?php do_action('woocommerce_register_form'); ?>

            <p class="woocommerce-FormRow form-row">
                <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>"><?php esc_html_e('Register', 'woocommerce'); ?></button>
            </p>

            <?php do_action('woocommerce_register_form_end'); ?>

        </form>

<?php

        return ob_get_clean();
    }


    //防止客人修改註冊頁
    function jdaio_hide_post()
    {
        $register_page_exist = post_exists('Register', '', '', 'page');
        if ($register_page_exist == 0 || self::$current_user_level == 0) return;

        $css = '';
        $css .= '<style>';
        $css .= 'tr#post-' . $register_page_exist . '{';
        $css .=     'display: none !important;';
        $css .= '}';
        $css .= '</style>';

        echo $css;
    }
}

