// Notice how this gets configured before we load Font Awesome
window.FontAwesomeConfig = { autoReplaceSvg: false };

jQuery(document).ready(function () {
    //檢查是否為超商取貨

    jQuery(document.body).on("updated_checkout", function (e, data) {
        if (data !== undefined) {
            let is_ec_shipping = jQuery("#shipping_method_0_ecpay_shipping").prop("checked");
            if (is_ec_shipping === true) {
                jQuery("#billing_postcode_field").slideUp().find('input[name^="billing_"]').val("100");
                jQuery("#billing_address_1_field").slideUp().find('input[name^="billing_"]').val("超商取貨");
            } else {
                jQuery("#billing_postcode_field").slideDown();
                jQuery("#billing_address_1_field").slideDown();
            }
        }
    });

    //註冊按鈕
    let html = '<p class="woocommerce-register"><a href="/register/">註冊</a></p>';
    jQuery(".woocommerce-form-login .woocommerce-LostPassword").after(html);

    //CHATBUTTON
    let chatbutton_width = jQuery(".chatbutton_content").width();
    let left_shift = chatbutton_width - 75;
    let left_shift_unit = left_shift + "px";
    let count_btn = jQuery(".chatbutton_content_inner_scroll a").length;
    let count_width = 75 * count_btn;
    jQuery(".chatbutton_content_inner_scroll").css({ "min-width": count_width + "px" });
    jQuery(".chatbutton_content .fa-reply-all").click(function () {
        jQuery(".chatbutton_content").addClass("expand").animate({ left: "0px" }, 300);
    });
    jQuery(".chatbutton_content .fa-share-all").click(function () {
        jQuery(".chatbutton_content").removeClass("expand").animate({ left: left_shift_unit }, 300);
    });

    jQuery(".chatbutton_content .fa-arrow-from-right.expand").click(function () {
        jQuery(".chatbutton_content .fa-arrow-from-right.expand").removeClass("expand");
        jQuery(".chatbutton_content").animate({ left: left_shift_unit }, 300);
    });
});
