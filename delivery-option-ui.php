<?php
namespace LinkedFarm\DeliveryOption\Ui;

require_once("delivery-option.php");

use LinkedFarm\DeliveryOption\Main as main;

function init()
{
    add_action("woocommerce_before_main_content", 'LinkedFarm\DeliveryOption\Ui\shop_loop_field');
    add_action("woocommerce_before_main_content", 'LinkedFarm\DeliveryOption\Ui\shop_loop_js');

//    add_action("woocommerce_before_cart_table", 'LinkedFarm\DeliveryOption\Ui\cart');
    //add_action("woocommerce_before_checkout_form", 'LinkedFarm\DeliveryOption\Ui\checkout');
    add_action("woocommerce_review_order_before_payment", 'LinkedFarm\DeliveryOption\Ui\checkout');

    add_action("woocommerce_before_cart_table", 'LinkedFarm\DeliveryOption\Ui\shop_loop_field');
    add_action("woocommerce_before_checkout_form", 'LinkedFarm\DeliveryOption\Ui\shop_loop_field');
    //add_action("woocommerce_review_order_before_payment", 'LinkedFarm\DeliveryOption\Ui\shop_loop_field');

    add_action("woocommerce_before_cart_table", 'LinkedFarm\DeliveryOption\Ui\shop_loop_js');
    add_action("woocommerce_before_checkout_form", 'LinkedFarm\DeliveryOption\Ui\shop_loop_js');
    //add_action("woocommerce_review_order_before_payment", 'LinkedFarm\DeliveryOption\Ui\shop_loop_js');

    }

function get_nice_date() {
    $date=main\lf_get_delivery_date();
    $format = "d-m-Y";
    $dateobj = \DateTime::createFromFormat($format, $date);
    return $dateobj->format('d F, Y');
}

function cart()
{
    $curr_opt = main\lf_get_delivery_option();
    $curr_opt = __(get_option('lf_delivery_option_opt_value'.$curr_opt), 'lf-delivery-option');
    echo '<h3 class="page_caption">'. __('Delivery Option', 'lf-delivery-option')
        . ": "
        .  $curr_opt . '<br/>';

    echo __('Delivery Date', 'lf-delivery-option')
        . ": "
        . get_nice_date() . '</h3><br/>';
}


function checkout()
{
    $curr_opt = main\lf_get_delivery_option();
    $curr_opt = __(get_option('lf_delivery_option_opt_value'.$curr_opt), 'lf-delivery-option');
    echo '<a href="#dopspinner">';
    echo '<strong>'. __('Delivery Option', 'lf-delivery-option')
        . ": "
        .  $curr_opt . '<br/>';

    echo __('Delivery Date', 'lf-delivery-option')
        . ": "
        . get_nice_date() . '</strong><br/></a>';
}

function shop_loop_js()
{
    global $wpefield_version;

    $calendar_theme = get_option('lf_delivery_option_calendar_theme');
    if ($calendar_theme == '') {
        $calendar_theme = 'base';
    }
    wp_dequeue_style('jquery-ui-style');
    wp_register_style('jquery-ui-style-orddd-lite', "//code.jquery.com/ui/1.9.2/themes/$calendar_theme/jquery-ui.css",
        '', $wpefield_version, false);
    wp_enqueue_style('jquery-ui-style-orddd-lite');
    wp_enqueue_style('datepicker', plugins_url('/css/datepicker.css', __FILE__), '', $wpefield_version, false);

    wp_dequeue_script('initialize-datepicker');
    wp_enqueue_script('initialize-datepicker-orddd', plugins_url('/js/initialize-datepicker.js', __FILE__), '',
        $wpefield_version, false);

    if (isset($_GET['lang']) && $_GET['lang'] != '' && $_GET['lang'] != null) {
        $language_selected = $_GET['lang'];
    } else {
        $language_selected = get_option('lf_delivery_option_language_selected');
        if (defined('ICL_LANGUAGE_CODE')) {
            if (constant('ICL_LANGUAGE_CODE') != '') {
                $wpml_current_language = constant('ICL_LANGUAGE_CODE');
                if (!empty($wpml_current_language)) {
                    $language_selected = $wpml_current_language;
                } else {
                    $language_selected = get_option('lf_delivery_option_language_selected');
                }
            }
        }
        if ($language_selected == "") {
            $language_selected = "en-GB";
        }

        wp_enqueue_script($language_selected,
            plugins_url("/js/i18n/jquery.ui.datepicker-$language_selected.js", __FILE__),
            array('jquery', 'jquery-ui-datepicker'), $wpefield_version, false);
    }
}

function shop_loop_field()
{
    $min_ddate = main\lf_get_delivery_date();
        //(is_user_logged_in() ? WC()->session->get("delivery_date") : $_SESSION["delivery_date"]);
    //echo "date:  " . $min_ddate;

    global $lf_orddd_weekdays;
    $first_day_of_week = '1';

    if (get_option('lf_delivery_option_start_of_week') != '') {
        $first_day_of_week = get_option('lf_delivery_option_start_of_week');
    }

    $clear_button_text = "showButtonPanel: true, closeText: '" . __("Clear", "lf-delivery-option") . "',";
    if (!$min_ddate) {
        echo __("This delivery option is currently not available", "linkedfarm") . "</br>";
        echo '<div id="dopspinner" class="spinner"></div><script language="javascript">';
        echo 'jQuery( document ).ready( function(){';
    } else {
        echo '<div id="dopspinner" class="spinner"></div><script language="javascript">';
        echo '
                    jQuery( document ).ready( function(){
                        var formats = ["MM d, yy","MM d, yy"];
                        jQuery.extend( jQuery.datepicker, { afterShow: function( event ) {
    						jQuery.datepicker._getInst( event.target ).dpDiv.css( "z-index", 9999 );
                            if ( jQuery( "#lf_delivery_option_number_of_months" ).val() == "1" ) {
                                jQuery.datepicker._getInst( event.target ).dpDiv.css( "width", "300px" );
                            } else {
                                jQuery.datepicker._getInst( event.target ).dpDiv.css( "width", "40em" );
                            }
                          }
                        });
                        var parts = "' . $min_ddate . '" .split("-");
                        var ndate =  new Date(parts[2], parts[1] - 1, parts[0]);
                        jQuery( "#lfh_deliverydate" ).val( "' . $min_ddate . '" )
                        jQuery( "#lf_deliverydate" ).datepicker(
                        { dateFormat: "' . get_option('lf_delivery_option_delivery_date_format') . '",
                        firstDay: parseInt( ' . $first_day_of_week . ' ),
                        minDate:1,
                        beforeShow: avd,
                        beforeShowDay: chd, ' . $clear_button_text . '
                            onClose:function( dateStr, inst ) {
                                if ( dateStr != "" ) {
                                    var monthValue = inst.selectedMonth+1;
                                    var dayValue = inst.selectedDay;
                                    var yearValue = inst.selectedYear;
                                    var all = dayValue + "-" + monthValue + "-" + yearValue;
                                   // $.session.set("lfh_deliverydate", all);
                                    // If "Clear" gets clicked, then really clear it
                                    var event = arguments.callee.caller.caller.arguments[0];
                                    if(jQuery( "#lfh_deliverydate" ).val()!=all) {
                                    var answer =
                                    confirm("Changing the delivery date will remove unavailable items form the cart!\\nTo confirm click OK");

    if (!answer) {
    jQuery( "#lf_deliverydate" ).datepicker("setDate",ndate);
        //e.preventDefault();
    } else {


var data = {
		action: "lf_set_deliveryDate",
		deliveryDate: all
	};

    // This does the ajax request
    jQuery.ajax({
        url: "' . admin_url("admin-ajax.php") . '" ,
        type: "post",
        data: data,
        success: function(data) {
                    console.log("OK: " + data);
                    location.reload(true);

                },
        error: function(errorThrown){
                    console.log("ERR: " + errorThrown);
                    //jQuery( "#lf_deliverydate" ).datepicker("setDate",ndate);
                }
    });
    $("#dopspinner").show();

    }
    jQuery( "#lfh_deliverydate" ).val( all );
    } //close else



                                }
                                jQuery( document.body ).trigger( "post-load" );
                                jQuery( "#lf_deliverydate" ).blur();
                            }
                        }).datepicker("setDate",ndate).focus( function ( event ) {
                            jQuery(this).trigger( "blur" );
                            jQuery.datepicker.afterShow( event );
                        });

                    ';

        if (get_option('lf_delivery_option_field_note') != '') {
            $field_note_text = addslashes(__(get_option('lf_delivery_option_field_note'), 'lf-delivery-option'));
            $field_note_text = str_replace(array("\r\n", "\r", "\n"), "<br/>", $field_note_text);
            echo 'jQuery( "#lf_deliverydate" ).parent().append( "<small class=\'lf_delivery_option_field_note\'>' .
                $field_note_text . '</small>" );';
        }
    }
    echo '
    jQuery("#lf_deliveryoption").val("'.main\lf_get_delivery_option().'");
    jQuery("#lf_deliveryoption").change(function() {
    var data = {
		action: "lf_set_deliveryOption",
		deliveryOption: this.value
	};
	jQuery.ajax({
        url: "' . admin_url("admin-ajax.php") . '" ,
        type: "post",
        data: data,
        success: function(data) {
                    console.log("OK: " + data);
                    location.reload(true);
                },
        error: function(errorThrown){
                    console.log("ERR: " + errorThrown);
                }
    });
$("#dopspinner").show();

});
    ';

    echo '} );
      </script>';

    $opts_opts = array();
    $opts = get_option('lf_delivery_option_number_of_options');
    for ($j = 0; $j < $opts; $j++) {
        $opt = 'lf_delivery_option_opt_value' . $j;
        $opt_val = get_option($opt);
        if (!$opt_val)
            continue;
        $opts_opts[$j] = __($opt_val, 'lf-delivery-option');
    }

    woocommerce_form_field('lf_deliveryoption', array(
        'type' => 'select',
        'label' => __('Delivery Option', 'lf-delivery-option'),
        'placeholder' => __('Choose Option', 'lf-delivery-option'),
        'required' => true,
        'custom_attributes' => array('style' => 'cursor:text !important;'),
        'options' => $opts_opts
    ), '');

    if ($min_ddate) {
        woocommerce_form_field('lf_deliverydate', array(
            'type' => 'text',
            'label' => __('Delivery Date', 'lf-delivery-option'),
            'placeholder' => __('Choose Date', 'lf-delivery-option'),
            'required' => true,
            'custom_attributes' => array('style' => 'cursor:text !important;')
        ), '');
    }

    $alldays = array();
    $curr_opt = main\lf_get_delivery_option();

    for ($i = 1; $i < 8; $i++) {
        $opt = 'lf_delivery_option_available_day' . "$curr_opt-$i";
        $alldays[$i] = get_option($opt);
    }

    $alldayskeys = array_keys($alldays);
    $checked = "No";
    foreach ($alldayskeys as $key) {
        if ($alldays[$key] == 'checked') {
            $checked = "Yes";
        }
    }

    if ($checked == 'Yes') {
        foreach ($alldayskeys as $key) {
            print('<input type="hidden" id="day' . $key . '" value="' . $alldays[$key] . '">');
        }
    } else if ($checked == 'No') {
        foreach ($alldayskeys as $key) {
            print('<input type="hidden" id="day' . $key . '" value="checked">');
        }
    }

    $min_date = '';
    $current_time = current_time('timestamp');

    $delivery_time_seconds = get_option('lf_delivery_option_prep_time') * 60 * 60;
    $cut_off_timestamp = $current_time + $delivery_time_seconds;
    $cut_off_date = date("d-m-Y", $cut_off_timestamp);
    $min_date = date("j-n-Y", strtotime($cut_off_date));

    print('<input type="hidden" name="lf_delivery_option_prep_time" id="lf_delivery_option_prep_time" value="' .
        $min_date . '">');
    print('<input type="hidden" name="lf_delivery_option_number_of_dates" id="lf_delivery_option_number_of_dates" value="' .
        get_option('lf_delivery_option_number_of_dates') . '">');
    print('<input type="hidden" name="lf_delivery_option_number_of_months" id="lf_delivery_option_number_of_months" value="' .
        get_option('lf_delivery_option_number_of_months') . '">');

}