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
            //add_thickbox();
?>
            <div class="wpclever_settings_page wrap">
                <h1>擴充模組</h1>
                <div class="wp-list-table widefat plugin-install-network">
                    <div class="plugin-card woo-fly-cart" id="woo-fly-cart">
                        <div class="plugin-card-top">

                            <img src="https://login.ecpay.com.tw/Content/themes/WebStyle20190717/images/ecpay_logo.svg" class="plugin-icon" alt="">

                            <div class="name column-name">
                                <h3>綠界支付</h3>
                            </div>
                            <div class="action-links">
                                <ul class="plugin-action-buttons">
                                    <li>
                                        <a href="http://localhost/divi_tp/wp-admin/admin.php?page=wpclever-kit&amp;action=deactivate&amp;plugin=woo-fly-cart%2Fwpc-fly-cart.php&amp;_wpnonce=3ed8fa5423#woo-fly-cart" class="button deactivate-now">
                                            Deactivate </a>
                                    </li>
                                    <li>
                                        <a href="" class="">
                                            前往官網 <span class="dashicons dashicons-external"></span></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="desc column-description">
                                <p>綠界科技Ecpay是第三方支付領導品牌,提供金流、物流、電子發票、 跨境電商、資安聯防一站購足服務。<br>也支援VISA、JCB、MASTERCARD、銀聯卡等多種信用卡支付方式，還提供超商取貨付款、定期定額等支付方式</p>
                            </div>
                        </div>
                        <div class="plugin-card-bottom">
                            <div class="vers column-rating">
                                <p style="margin-bottom:10px;">推薦指數</p>
                                <div class="star-rating"><span class="screen-reader-text"></span>
                                    <div class="star star-full" aria-hidden="true"></div>
                                    <div class="star star-full" aria-hidden="true"></div>
                                    <div class="star star-full" aria-hidden="true"></div>
                                    <div class="star star-full" aria-hidden="true"></div>
                                    <div class="star star-half" aria-hidden="true"></div>
                                </div> <span class="num-ratings"></span>
                            </div>
                            <div class="column-updated">
                                <p>點評：</p><br><p>推薦使用！</p></div>

                        </div>
                    </div>
                </div>
            </div>
<?php

        }
    }


    new JDAIO_Extension();
}
