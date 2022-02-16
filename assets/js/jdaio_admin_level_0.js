jQuery(document).ready(($) => {
    let path = window.location.pathname + window.location.search;

    /**
     * 預設註冊後不寄送信件
     */
    jQuery("#send_user_notification").removeAttr("checked");

    /**
     * 批量導入物流單號  advanced_shipping_tracking
     */

    //新增上傳教學
    let tracking_shipping_html =
        '<p style="text-align:left;">填寫說明：</p><img class="w-100" src="' +
        SITE_URL +
        '/wp-content/plugins/jerry_divi_aio/assets/img/shipscv.png">';
    jQuery(".bulk_upload_documentation_ul").after(tracking_shipping_html);
    jQuery(".ast_nav_div .menu_trackship_link").text("自訂EMAIL格式").attr("target", "_blank");

    //新增店到店區塊
    let dtod_banner_html = "";
    dtod_banner_html += '<div class="wrap">';
    dtod_banner_html += '<h1 class="wp-heading-inline">請先選擇出貨方式</h1>';
    dtod_banner_html += '<h2 class="wp-heading-inline">1. 綠界超商店到店</h2>';
    dtod_banner_html +=
        '<a href="https://vendor.ecpay.com.tw/User/LogOn_Step1" target="_blank" class="page-title-action">登入綠界後台</a>';
    dtod_banner_html += '<hr class="wp-header-end">';
    dtod_banner_html += '<h2 class="wp-heading-inline">2. 一般宅配</h2>';
    dtod_banner_html += "</div>";

    jQuery(".woocommerce_page_woocommerce-advanced-shipment-tracking .ast_admin_content").prepend(dtod_banner_html);

    /**
     * 導出會員數據
     */
    let export_member_html =
        '<p>如果會員資料中有包含繁體中文，CSV檔案可能會呈現亂碼</p><p><a href="https://blog.impochun.com/excel-big5-utf8-issue/" target="_blank">解決辦法請參考這篇文章</a></p>';
    jQuery(".wt_iew_page_hd").after(export_member_html);

    /**
     * 極簡模式
     * 改變checkbox後送出表單
     */
    jQuery(".submit_on_change input, .jdaio_simple_mode_btn").on("change", function(){
        jQuery("#submit").click();
    });

    /**
     * admin 2020
     */
    jQuery("#activationpanel").remove();

    /**
     * Divi builder  點擊縮合
     */
    jQuery("#et_pb_layout > .postbox-header > h2").on("click", function(e){
        if( e.target != this ) return;
        jQuery("#et_pb_layout > .inside").slideToggle('slow');
    });

    /**
     * 自訂結帳
     */
    if (path.indexOf("checkout_form_designer") != -1) {
        jQuery(
            ".woocommerce_page_checkout_form_designer #wpbody-content > div.wrap > div.thwcfd-wrap > ul > li:nth-child(1) > a"
        ).text("訂單欄位");
        jQuery(
            ".woocommerce_page_checkout_form_designer #wpbody-content > div.wrap > div.thwcfd-wrap > ul > li:nth-child(2) > a"
        ).text("物流欄位");
        jQuery(
            ".woocommerce_page_checkout_form_designer #wpbody-content > div.wrap > div.thwcfd-wrap > ul > li:nth-child(3) > a"
        ).text("額外欄位");
        jQuery(
            "#thwcfd_field_form table.thwcfd_field_form_tab_general_placeholder.thwcfd_pp_table.thwcfd-general-info tr.form_field_default td.label"
        ).text("預設值");
    }
});
