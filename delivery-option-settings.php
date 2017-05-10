<?php

namespace LinkedFarm\DeliveryOption\Settings;

include_once('common.php');


function init()
{
    add_action('admin_menu', 'LinkedFarm\DeliveryOption\Settings\delivery_option_menu');
    add_action('admin_init', 'LinkedFarm\DeliveryOption\Settings\delivery_option_main_settings');
    add_action('admin_init', 'LinkedFarm\DeliveryOption\Settings\delivery_option_ui_settings');

}

function delivery_option_menu()
{
    add_submenu_page(
        'lf_core_page',
        'Delivery Option Settings',
        'Delivery Option Settings',
        'manage_woocommerce',
        'lf_delivery_option_page',
        'LinkedFarm\DeliveryOption\Settings\lf_delivery_option_page_html');
}

function lf_delivery_option_page_html()
{
    // check user capabilities
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    $tab = $active_main = $active_ui = '';
    if (isset($_GET['tab'])) {
        $tab = $_GET['tab'];
    } else {
        $tab = "main";
    }

    if ($tab == 'main' || $tab == '') {
        $active_main = "nav-tab-active";
    }

    if ($tab == 'ui') {
        $active_ui = "nav-tab-active";
    }

    ?>
    <h2>LF Delivery Option Settings</h2>
    <?php
    settings_errors();
    ?>
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="admin.php?page=lf_delivery_option_page&tab=main"
           class="nav-tab <?php echo $active_main; ?>"><?php _e('Main Settings', 'lf-delivery-option'); ?> </a>
        <a href="admin.php?page=lf_delivery_option_page&tab=ui"
           class="nav-tab <?php echo $active_ui; ?>"> <?php _e('UI Settings', 'lf-delivery-option'); ?> </a>
    </h2>
    <?php
    if ($tab == 'main' || $tab == '') {
        print('<div id="content">
                    <form method="post" action="options.php">');
        settings_fields("lf_delivery_option_main_section");
        do_settings_sections("lf_delivery_option_main_page");
        submit_button(__('Save Settings', 'lf-delivery-option'), 'primary', 'save_main', true);
        print('</form>
                </div>');
    } elseif ($tab == 'ui') {
        print('<div id="content">
                    <form method="post" action="options.php">');
        settings_fields("lf_delivery_option_ui_section");
        do_settings_sections("lf_delivery_option_ui_page");
        submit_button(__('Save Settings', 'lf-delivery-option'), 'primary', 'save_ui', true);
        print('</form>
                </div>');
    }
}

function delivery_option_ui_settings()
{

    add_settings_section(
        'lf_delivery_option_ui_section',
        __('Delivery Option UI Setings', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\ui_section_cb',
        'lf_delivery_option_ui_page'
    );



    //TODO make it discoverable
    add_settings_field(
        'lf_delivery_option_language_selected',
        __('Calendar Language:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\calendar_language_cb',
        'lf_delivery_option_ui_page',
        'lf_delivery_option_ui_section',
        array(__('Choose a Language.', 'lf-delivery-option'))
    );

    register_setting(
        'lf_delivery_option_ui_section',
        'lf_delivery_option_language_selected'
    );

    add_settings_field(
        'lf_delivery_option_delivery_date_format',
        __('Date Format:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\date_formats_cb',
        'lf_delivery_option_ui_page',
        'lf_delivery_option_ui_section',
        array('<br>' . __('The format in which the Delivery Date appears to the customers on the checkout page once the date is selected.', 'lf-delivery-option'))
    );

    register_setting(
        'lf_delivery_option_ui_section',
        'lf_delivery_option_delivery_date_format'
    );

    add_settings_field(
        'lf_delivery_option_start_of_week',
        __('First Day of Week:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\first_day_of_week_cb',
        'lf_delivery_option_ui_page',
        'lf_delivery_option_ui_section',
        array(__('Choose the first day of week displayed on the Delivery Date calendar.', 'lf-delivery-option'))
    );

    register_setting(
        'lf_delivery_option_ui_section',
        'lf_delivery_option_start_of_week'
    );


    add_settings_field(
        'lf_delivery_option_field_note',
        __('Field Note Text:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\field_note_cb',
        'lf_delivery_option_ui_page',
        'lf_delivery_option_ui_section',
        array('<br>' . __('Choose the note to be displayed below the delivery date field on checkout page.', 'lf-delivery-option'))
    );

    register_setting(
        'lf_delivery_option_ui_section',
        'lf_delivery_option_field_note'
    );


    add_settings_field(
        'lf_delivery_option_number_of_months',
        __('Number of Months:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\number_of_months_cb',
        'lf_delivery_option_ui_page',
        'lf_delivery_option_ui_section',
        array(__('The number of months to be shown on the calendar.', 'lf-delivery-option'))
    );

    register_setting(
        'lf_delivery_option_ui_section',
        'lf_delivery_option_number_of_months'
    );

    add_settings_field(
        'lf_delivery_option_fields_on_checkout_page',
        __('Field placement on the Checkout page:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\pos_in_checkout_page_cb',
        'lf_delivery_option_ui_page',
        'lf_delivery_option_ui_section',
        array(__('</br>The Delivery Date field will be displayed in the selected section.</br><i>Note: WooCommerce automatically hides the Shipping section fields for Virtual products.</i>', 'lf-delivery-option'))
    );

    register_setting(
        'lf_delivery_option_ui_section',
        'lf_delivery_option_fields_on_checkout_page'
    );

    add_settings_field(
        'lf_delivery_option_calendar_theme_name',
        __('Theme:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\calendar_theme_cb',
        'lf_delivery_option_ui_page',
        'lf_delivery_option_ui_section',
        array(__('Select the theme for the calendar which blends with the design of your website.', 'lf-delivery-option'))
    );

    register_setting(
        'lf_delivery_option_ui_section',
        'lf_delivery_option_calendar_theme'
    );

    register_setting(
        'lf_delivery_option_ui_section',
        'lf_delivery_option_calendar_theme_name'
    );

}

function ui_section_cb()
{
    // echo "<p>UI Settings</p>";
}

function calendar_language_cb($args) {
    $calendar_langs = json_decode(constant('LinkedFarm\DeliveryOption\Common\CalendarLangs'));
    $language_selected = get_option('lf_delivery_option_language_selected');
    if ($language_selected == "") {
        $language_selected = "en-GB";
    }

    echo '<select id="lf_delivery_option_language_selected" name="lf_delivery_option_language_selected">';

    foreach ($calendar_langs as $key => $value) {
        $sel = "";
        if ($key == $language_selected) {
            $sel = "selected";
        }
        echo "<option value='$key' $sel>$value</option>";
    }

    echo '</select>';

    $html = '<label for="lf_delivery_option_language_selected"> ' . $args[0] . '</label>';
    echo $html;
}

function date_formats_cb($args) {

    $date_formats = json_decode(constant('LinkedFarm\DeliveryOption\Common\DateFormats'));
    echo '<select name="lf_delivery_option_delivery_date_format" id="lf_delivery_option_delivery_date_format" size="1">';

    foreach ($date_formats as $k => $format) {
        printf("<option %s value='%s'>%s</option>\n",
            selected($k, get_option('lf_delivery_option_delivery_date_format'), false),
            esc_attr($k),
            date($format)
        );
    }
    echo '</select>';

    $html = '<label for="lf_delivery_option_delivery_date_format">' . $args[0] . '</label>';
    echo $html;
}

function first_day_of_week_cb($args) {
    $day_selected = get_option('lf_delivery_option_start_of_week');
    if ($day_selected == "") {
        $day_selected = 0;
    }

    echo '<select id="lf_delivery_option_start_of_week" name="lf_delivery_option_start_of_week">';

    for ($i = 1; $i < 8; $i++) {
        $day_name = constant('LinkedFarm\DeliveryOption\Common\DAY' . $i);
        $sel = "";
        if ($i == $day_selected) {
            $sel = " selected ";
        }
        echo "<option value='$i' $sel>$day_name</option>";
    }
    echo '</select>';

    $html = '<label for="lf_delivery_option_start_of_week"> ' . $args[0] . '</label>';
    echo $html;
}

function field_note_cb($args) {
    echo '<textarea rows="2" cols="90" name="lf_delivery_option_field_note" id="lf_delivery_option_field_note">' . stripslashes(get_option('lf_delivery_option_field_note')) . '</textarea>';

    $html = '<label for="lf_delivery_option_field_note"> ' . $args[0] . '</label>';
    echo $html;
}

function number_of_months_cb($args) {
    echo '<select name="lf_delivery_option_number_of_months" id="lf_delivery_option_number_of_months" size="1">';

    for ($i = 1; $i < 3; $i++) {

        printf("<option %s value='%s'>%s</option>\n",
            selected($i, get_option('lf_delivery_option_number_of_months'), false),
            esc_attr($i),
            $i
        );
    }
    echo '</select>';

   echo '<label for="lf_delivery_option_number_of_months">' . $args[0] . '</label>';

}

function pos_in_checkout_page_cb($args) {
    $lf_orddd_date_in_billing = 'checked';
    $lf_orddd_date_in_shipping = $lf_orddd_date_before_order_notes = $lf_orddd_date_after_order_notes = '';
    if (get_option('lf_delivery_option_fields_on_checkout_page') == "billing_section") {
        $lf_orddd_date_in_billing = 'checked';
        $lf_orddd_date_in_shipping = '';
        $lf_orddd_date_before_order_notes = '';
        $lf_orddd_date_after_order_notes = '';
    } else if (get_option('lf_delivery_option_fields_on_checkout_page') == "shipping_section") {
        $lf_orddd_date_in_shipping = 'checked';
        $lf_orddd_date_in_billing = '';
        $lf_orddd_date_before_order_notes = '';
        $lf_orddd_date_after_order_notes = '';
    } else if (get_option('lf_delivery_option_fields_on_checkout_page') == "before_order_notes") {
        $lf_orddd_date_before_order_notes = 'checked';
        $lf_orddd_date_in_billing = '';
        $lf_orddd_date_in_shipping = '';
        $lf_orddd_date_after_order_notes = '';
    } else if (get_option('lf_delivery_option_fields_on_checkout_page') == "after_order_notes") {
        $lf_orddd_date_after_order_notes = 'checked';
        $lf_orddd_date_in_billing = '';
        $lf_orddd_date_in_shipping = '';
        $lf_orddd_date_before_order_notes = '';
    }

    echo '<input type="radio" name="lf_delivery_option_fields_on_checkout_page" id="lf_delivery_option_fields_on_checkout_page" value="billing_section" ' . $lf_orddd_date_in_billing . '>' . __('In Billing Section', 'order-delivery-date') . '&nbsp;&nbsp;
                <input type="radio" name="lf_delivery_option_fields_on_checkout_page" id="lf_delivery_option_fields_on_checkout_page" value="shipping_section" ' . $lf_orddd_date_in_shipping . '>' . __('In Shipping Section', 'order-delivery-date') . '&nbsp;&nbsp;
                <input type="radio" name="lf_delivery_option_fields_on_checkout_page" id="lf_delivery_option_fields_on_checkout_page" value="before_order_notes" ' . $lf_orddd_date_before_order_notes . '>' . __('Before Order Notes', 'order-delivery-date') . '&nbsp;&nbsp;
		        <input type="radio" name="lf_delivery_option_fields_on_checkout_page" id="lf_delivery_option_fields_on_checkout_page" value="after_order_notes" ' . $lf_orddd_date_after_order_notes . '>' . __('After Order Notes', 'order-delivery-date');

    echo '<label for="lf_delivery_option_fields_on_checkout_page"> ' . $args[0] . '</label>';
}

function calendar_theme_cb($args)
{
    $language_selected = get_option('lf_delivery_option_language_selected');
    if ($language_selected == "") {
        $language_selected = "en-GB";
    }

    echo '<input type="hidden" name="lf_delivery_option_calendar_theme" id="lf_delivery_option_calendar_theme" value="' . get_option('lf_delivery_option_calendar_theme') . '">';
    echo  '<input type="hidden" name="lf_delivery_option_calendar_theme_name" id="lf_delivery_option_calendar_theme_name" value="' . get_option('lf_delivery_option_calendar_theme_name') . '">';
//    echo '<hr/>' . get_option('lf_delivery_option_calendar_theme') . '<hr/>';
//    echo '<hr/>' . get_option('lf_delivery_option_calendar_theme_name') . '<hr/>';
//    echo '<hr/>' . get_option('lf_orddd_calendar_theme') . '<hr/>';
//    echo '<hr/>' . get_option('lf_orddd_calendar_theme_name') . '<hr/>';
    echo '<script>
                jQuery( document ).ready( function( ) {
                    var calendar_themes = ' . constant('LinkedFarm\DeliveryOption\Common\CalendarThemes') . '
                    jQuery( "#lfswitcher" ).themeswitcher( {
                        onclose: function( ) {
                            var cookie_name = this.cookiename;
                            jQuery( "input#lf_delivery_option_calendar_theme" ).val( jQuery.cookie( cookie_name ) );
                            jQuery.each( calendar_themes, function( key, value ) {
                                if( jQuery.cookie( cookie_name ) == key ) {
                                    jQuery( "input#lf_delivery_option_calendar_theme_name" ).val( value );
                                }
                            });
                            jQuery( "<link/>", {
                                rel: "stylesheet",
                                type: "text/css",
                                href: "' . plugins_url("/css/datepicker.css", __FILE__) . '"
                            }).appendTo("head");
                        },
                        imgpath: "' . plugins_url() . '/lf-delivery-option/images/",
                        loadTheme: "' . get_option('lf_delivery_option_calendar_theme_name') . '",

                    });
                });
                jQuery( function() {
                    jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "" ] );
                    jQuery( "#lfdatepicker" ).datepicker( jQuery.datepicker.regional[ "' . $language_selected . '" ] );
                    jQuery( "#localisation_select" ).change(function() {
                        jQuery( "#lfdatepicker" ).datepicker( "option", jQuery.datepicker.regional[ jQuery( this ).val() ] );
                        });
                    });
            </script>
            <div id="lfswitcher"></div>
            <br><strong>' . __('Preview theme:', 'lf-delivery-option') . '</strong><br>
            <div id="lfdatepicker" style="width:300px"></div>';

    $html = '<label for="lf_delivery_option_calendar_theme_name"> ' . $args[0] . '</label>';
    echo $html;
}

function delivery_option_main_settings()
{
    add_settings_section(
        'lf_delivery_option_main_section',
        __('Delivery Option Main Setings', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\main_section_cb',
        'lf_delivery_option_main_page'
    );


    //todo number of delivery options
    add_settings_field(
        'lf_delivery_option_number_of_options',
        __('Number of delivery options:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\num_of_options_cb',
        'lf_delivery_option_main_page',
        'lf_delivery_option_main_section',
        array(__('Number of available delivery or pickup options.', 'lf-delivery-option'))
    );


    register_setting(
        'lf_delivery_option_main_section',
        'lf_delivery_option_number_of_options'
    );


    add_settings_field(
        'lf_delivery_option_opt_values',
        __('Number of options to choose:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\opt_value_cb',
        'lf_delivery_option_main_page',
        'lf_delivery_option_main_section',
        array(__('Info of available delivery or pickup options.', 'lf-delivery-option'))
    );

    $opts = get_option('lf_delivery_option_number_of_options');
    for ($i = 0; $i < $opts; $i++) {
        register_setting(
            'lf_delivery_option_main_section',
            'lf_delivery_option_opt_value' . $i);
    }

    ///
    add_settings_field(
        'lf_delivery_option_available_days',
        __('Available Days:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\available_days_cb',
        'lf_delivery_option_main_page',
        'lf_delivery_option_main_section',
        array('&nbsp;' . __('Select available days for delivery.', 'lf-delivery-option'))
    );

    $opts = get_option('lf_delivery_option_number_of_options');
    for ($j = 0; $j < $opts; $j++) {
        for ($i = 1; $i < 8; $i++) {
            register_setting(
                'lf_delivery_option_main_section',
                'lf_delivery_option_available_day' . "$j-$i");
        }
    }


    add_settings_field(
        'lf_delivery_option_prep_time',
        __('Minimum Delivery time (in hours):', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\prep_time_cb',
        'lf_delivery_option_main_page',
        'lf_delivery_option_main_section',
        array(__('Minimum number of hours required to prepare for delivery.', 'lf-delivery-option'))
    );

    register_setting(
        'lf_delivery_option_main_section',
        'lf_delivery_option_prep_time'
    );

    add_settings_field(
        'lf_delivery_option_number_of_dates',
        __('Number of dates to choose:', 'lf-delivery-option'),
        'LinkedFarm\DeliveryOption\Settings\num_of_dates_cb',
        'lf_delivery_option_main_page',
        'lf_delivery_option_main_section',
        array(__('Number of dates available for delivery.', 'lf-delivery-option'))
    );


    register_setting(
        'lf_delivery_option_main_section',
        'lf_delivery_option_number_of_dates'
    );

}

function main_section_cb()
{
    // echo "<p>Main Settings</p>";
}

function opt_value_cb($args)
{
    echo '<fieldset class="orddd-lite-days-fieldset"><legend><b>'
        . __('Delivery Options:', 'lf-delivery-option') . '</b></legend>';
    echo '<table>';

    $opts = get_option('lf_delivery_option_number_of_options');
    for ($i = 0; $i < $opts; $i++) {
        $opt = 'lf_delivery_option_opt_value' . $i;
        $opt_val = get_option($opt);
        echo '<tr>
        <td class="lf_orddd_fieldset_padding"><label class="ord_label" for="' . $opt_val . '">' .
            __('Option', 'lf-delivery-option') . " $i" . '</label></td>
        <td class="lf_orddd_fieldset_padding"><input type="text" name="' . $opt . '" id="' . $opt .
            '" value="' . $opt_val . '"/></td>';
    }

    printf('</table>
            </fieldset>');

    echo '<label for="lf_delivery_option_opt_values"> ' . $args[0] . '</label>';
}

function num_of_options_cb($args)
{
    echo '<input type="text"
    name="lf_delivery_option_number_of_options"
    id="lf_delivery_option_number_of_options"
    value="' . get_option('lf_delivery_option_number_of_options') . '"/>';
    echo '<label for="lf_delivery_option_number_of_options"> ' . $args[0] . '</label>';
}

function prep_time_cb($args)
{
    echo '<input type="text" name="lf_delivery_option_prep_time" id="lf_delivery_option_prep_time" value="' .
        get_option('lf_delivery_option_prep_time') . '"/>';
    echo '<label for="lf_delivery_option_prep_time"> ' . $args[0] . '</label>';
}

function num_of_dates_cb($args)
{
    echo '<input type="text" name="lf_delivery_option_number_of_dates" id="lf_delivery_option_number_of_dates" value="' .
        get_option('lf_delivery_option_number_of_dates') . '"/>';
    echo '<label for="lf_delivery_option_number_of_dates"> ' . $args[0] . '</label>';
}

function available_days_cb($args)
{
    $opts = get_option('lf_delivery_option_number_of_options');
    for ($j = 0; $j < $opts; $j++) {
        $opt = 'lf_delivery_option_opt_value' . $j;
        $opt_val = get_option($opt);
        if(!$opt_val) continue;
        echo '<fieldset class="fieldset"><legend><b>'
            . __($opt_val, 'lf-delivery-option') . ': </b></legend>';
        echo '<table>';

        for ($i = 1; $i < 8; $i++) {
            $opt = 'lf_delivery_option_available_day' . "$j-$i";
            $day_name = constant('LinkedFarm\DeliveryOption\Common\DAY' . $i);
            echo('<tr>
        	       <td class="lf_orddd_fieldset_padding"><input type="checkbox" name="' . $opt . '" id="' . $opt .
                '" value="checked" ' . get_option($opt) . '/></td>
        	       <td class="lf_orddd_fieldset_padding"><label class="ord_label" for="' . $day_name . '">' .
                __($day_name, 'lf-delivery-option') . '</label></td>'
            );
        }

        printf('</table>
            </fieldset>');
    }

    echo '<label for="lf_delivery_option_available_days"> ' . $args[0] . '</label>';
}