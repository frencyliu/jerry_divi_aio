jQuery(document).ready(() => {

    //-------------------批量導入物流單號-------------------//
    let tracking_shipping_html =
        '<img class="w-100" src="' + SITE_URL + '/wp-content/plugins/jerry_aio/assets/img/shipscv.png">';
    jQuery(".bulk_upload_documentation_ul").after(tracking_shipping_html);


    //-------------------導出會員數據-------------------//
    let export_member_html =
    '<p>如果會員資料中有包含繁體中文，CSV檔案可能會呈現亂碼</p><p><a href="https://blog.impochun.com/excel-big5-utf8-issue/" target="_blank">解決辦法請參考這篇文章</a></p>';
    jQuery(".wt_iew_page_hd").after(export_member_html);

    //-------------------Avada live 開新分頁-------------------//
    jQuery("#post-body-content #fusion_toggle_front_end").attr('target', '_blank');


    //-------------------極簡模式-------------------//
    jQuery('.jaio_simple_mode_btn').on('change', ()=>{
        jQuery('.jaio_simple_mode_form').submit();
    });



    //-------------------admin 2020-------------------//
    jQuery('#activationpanel').remove();


});


