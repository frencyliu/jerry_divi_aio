<?php

/**
 * Extension
 */

namespace Extension\JDAIO;

use Jerry_Divi_AIO;

defined('ABSPATH') || exit;

if (JDAIO_EXTENSION) {

    class JDAIO_Extension extends Jerry_Divi_AIO
    {
        public function __construct()
        {

            add_action('admin_menu', [$this, 'jdaio_amp_setting'], 15);
        }

        /**
         * Generate an activation URL for a plugin like the ones found in WordPress plugin administration screen.
         *
         * @param  string $plugin A plugin-folder/plugin-main-file.php path (e.g. "my-plugin/my-plugin.php")
         *
         * @return string         The plugin activation url
         */

        /*
        function generatePluginActivationLinkUrl($plugin)
        {
            // the plugin might be located in the plugin folder directly

            if (strpos($plugin, '/')) {
                $plugin = str_replace('/', '%2F', $plugin);
            }

            $activateUrl = sprintf(admin_url('plugins.php?action=activate&plugin=%s&plugin_status=all&paged=1&s'), $plugin);

            // change the plugin request to the plugin to pass the nonce check
            $_REQUEST['plugin'] = $plugin;
            $activateUrl = wp_nonce_url($activateUrl, 'activate-plugin_' . $plugin);

            return $activateUrl;
        }
        function generatePluginDeactivationLinkUrl($plugin)
        {
            if (strpos($plugin, '/')) {
                $plugin = str_replace('/', '%2F', $plugin);
            }

            $deactivateUrl = sprintf(admin_url('plugins.php?action=deactivate&plugin=%s&plugin_status=all&paged=1&s'), $plugin);

            // change the plugin request to the plugin to pass the nonce check
            $_REQUEST['plugin'] = $plugin;
            $deactivateUrl = wp_nonce_url($deactivateUrl, 'deactivate-plugin_' . $plugin);

            return $deactivateUrl;
        }
        */

        function newUrl($plugin)
        {
            if (strpos($plugin, '/')) {
                $plugin = str_replace('/', '%2F', $plugin);
            }

            return $plugin;
        }

        public function jdaio_amp_setting()
        {
            //[DEV]擴充模組  //GPDR
            if (JDAIO_EXTENSION) {
                add_menu_page(
                    '擴充模組',
                    '擴充模組',
                    'read',
                    'jdaio_extention',
                    [$this, 'jdaio_extension_page'],
                    'dashicons-block-default', //icon
                    null
                );
            }
        }

        public function jdaio_extension_page()
        {
            $plugin[] = @$_GET['plugin'];
            $slug = @$_GET['slug'];

            switch ($slug) {
                case 'ecpay-payment-for-woocommerce':
                    array_push(
                        $plugin,
                        "ecpay_shipping/ECPay-shipping-integration.php",
                        "ecpay_invoice/woocommerce-ecpayinvoice.php"
                    );
                    break;

                default:
                    # code...
                    break;
            }
            $action = @$_GET['action'];
            switch ($action) {
                case 'activate':
                    activate_plugins($plugin);
                    break;
                case 'deactivate':
                    deactivate_plugins($plugin);
                    break;

                default:
                    # code...
                    break;
            }

            $json_path = __DIR__ . '\extensions.json';
            $JsonParser = file_get_contents($json_path);
            $Extensions =  (array) json_decode($JsonParser, true);
?>
            <div class="wpclever_settings_page wrap">
                <h1>擴充模組</h1>
                <div class="wp-list-table widefat plugin-install-network">
                    <?php foreach ($Extensions as $key => $Extension) :
                        $is_activate = is_plugin_active($Extension['path']);
                        if ($is_activate) {
                            $link = admin_url('admin.php?page=jdaio_extention&slug=' . $Extension['slug'] . '&action=deactivate&plugin=' . $this->newUrl($Extension['path']));
                            $class = 'deactivate-now';
                        } else {
                            $link = admin_url('admin.php?page=jdaio_extention&slug=' . $Extension['slug'] . '&action=activate&plugin=' . $this->newUrl($Extension['path']));
                            $class = 'activate-now';
                        }
                    ?>
                        <div class="plugin-card">
                            <div class="plugin-card-top">
                                <img src="<?php echo $Extension['thumbnail_url']; ?>" class="plugin-icon plugin-card-img" alt="">


                                <div class="name column-name">
                                    <h3><?php echo $Extension['name']; ?></h3>
                                    <p style="margin:0px;"><a href="<?php echo $Extension['website']; ?>" class="">
                                            前往官網 <span class="dashicons dashicons-external"></span></a></p>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li>
                                            <a href="<?php echo $link; ?>" class="button <?php echo $class; ?>">
                                                <?php echo $class; ?> </a>
                                        </li>

                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p><?php echo $Extension['description']; ?></p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="">
                                    <div class="star-rating">
                                        <span class="">推薦指數：</span>
                                        <?php


                                        $star_half = substr($Extension['recommend_rate'], stripos($Extension['recommend_rate'], ".") + 1, strlen($Extension['recommend_rate']));;

                                        $star_full_num = floor($Extension['recommend_rate']);
                                        $star_empty_num = floor(5 - $Extension['recommend_rate']);

                                        for ($i = 0; $i < $star_full_num; $i++) {
                                            echo '<div class="star star-full" aria-hidden="true"></div>';
                                        }
                                        if ($star_half >= 3 && $star_half <= 7) {
                                            echo '<div class="star star-half" aria-hidden="true"></div>';
                                        }
                                        for ($j = 0; $j < $star_empty_num; $j++) {
                                            echo '<div class="star star-empty" aria-hidden="true"></div>';
                                        }
                                        ?>
                                    </div>
                                    <div class="commend">
                                        <p style="margin: 10px 0px 0px 0px;"><?php echo $Extension['admin_commend']; ?>&nbsp;</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
<?php

        }
    }


    new JDAIO_Extension();
}
