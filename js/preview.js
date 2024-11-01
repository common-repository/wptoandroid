// Add Color Picker to all inputs that have 'color-field' class
jQuery('.app_main_color').wpColorPicker({
    change: function(event, ui) {
        var color = jQuery("#wta_wp_to_android_app_accent_color").val();
        jQuery("#fullpreviews").contents().find("#accent_id").css("background", color);

        color = jQuery("#wta_wp_to_android_app_back_color").val();
        jQuery("#fullpreviews").contents().find("body").css("background", color);

        color = jQuery("#wta_wp_to_android_app_pri_color").val();
        jQuery("#fullpreviews").contents().find("header").css("background", color);

        color = jQuery("#wta_wp_to_android_app_font_color").val();
        jQuery("#fullpreviews").contents().find("#mdl-layout-title_id").css("color", color);
    }
});

jQuery( document ).ready(function() {

    var wta_color1 = jQuery("#wta_wp_to_android_app_accent_color").val();
    console.log(wta_color1);
    jQuery("#fullpreviews").contents().find("#accent_id").css("background", wta_color1);

    var wta_color2 = jQuery("#wta_wp_to_android_app_back_color").val();
    jQuery("#fullpreviews").contents().find("body").css("background", wta_color2);

    var wta_color3 = jQuery("#wta_wp_to_android_app_pri_color").val();
    jQuery("#fullpreviews").contents().find("header").css("background", wta_color3);

    var wta_color4 = jQuery("#wta_wp_to_android_app_font_color").val();
    jQuery("#fullpreviews").contents().find("#mdl-layout-title_id").css("color", wta_color4);

});