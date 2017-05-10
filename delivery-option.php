<?php

namespace LinkedFarm\DeliveryOption\Main;

require_once("delivery-option-settings.php");
require_once("delivery-option-ui.php");
require_once("meta/producer-options-meta.php");
require_once("delivery-option-wp-admin.php");


use LinkedFarm\DeliveryOption\Settings as settings;
use LinkedFarm\DeliveryOption\Ui as ui;
use LinkedFarm\DeliveryOption\Meta as meta;
use LinkedFarm\DeliveryOption\Admin as admin;

init();

function init()
{
    add_action('plugins_loaded', 'LinkedFarm\DeliveryOption\Main\load_textdomain');

    add_action('admin_enqueue_scripts', 'LinkedFarm\DeliveryOption\Main\enqueue');
    //add_action('plugins_loaded', 'linkedfarm_load_textdomain');
    add_action('init', 'LinkedFarm\DeliveryOption\Main\lf_start_session', 1);
    add_action('wp_login', 'LinkedFarm\DeliveryOption\Main\lf_end_session');

    add_filter('woocommerce_is_purchasable', 'LinkedFarm\DeliveryOption\Main\lf_filter_producer_available', 10, 2);
    //add_filter( 'woocommerce_product_is_visible', 'lf_filter_producer_available_product_visible',10, 2  );
    add_filter('woocommerce_product_query_meta_query',
        'LinkedFarm\DeliveryOption\Main\lf_filter_query_producer_available', 10, 2);
    add_action('wp_ajax_nopriv_lf_set_deliveryDate', 'LinkedFarm\DeliveryOption\Main\lf_change_ddate');
    add_action('wp_ajax_lf_set_deliveryDate', 'LinkedFarm\DeliveryOption\Main\lf_change_ddate');
    add_action('wp_ajax_nopriv_lf_set_deliveryOption', 'LinkedFarm\DeliveryOption\Main\lf_change_option');
    add_action('wp_ajax_lf_set_deliveryOption', 'LinkedFarm\DeliveryOption\Main\lf_change_option');

    add_action('woocommerce_checkout_process', 'LinkedFarm\DeliveryOption\Main\lf_validate_delivery_option');

    add_filter('woocommerce_email_order_meta_fields', 'LinkedFarm\DeliveryOption\Main\add_delivery_option_to_mail', 10, 3);


    add_action('woocommerce_checkout_update_order_meta', 'LinkedFarm\DeliveryOption\Main\update_order_meta');


    settings\init();
    ui\init();
    meta\init();
    admin\init();
}

function load_textdomain()
{
    //flush_rewrite_rules();
    load_plugin_textdomain('lf-delivery-option', false, plugin_basename(dirname(__FILE__)) . '/i18n/languages');
}

function get_nice_date() {
    $date=lf_get_delivery_date();
    $format = "d-m-Y";
    $dateobj = \DateTime::createFromFormat($format, $date);
    return $dateobj->format('d F, Y');
}

function get_timestamp() {
    $date=lf_get_delivery_date();
    $format = "d-m-Y";
    $dateobj = \DateTime::createFromFormat($format, $date);
    return $dateobj->getTimestamp();
}

function update_order_meta($order_id) {

//    die(print_r(get_post_meta($order_id), ture));

    //TODO use format set in settings
    update_post_meta($order_id, 'delivery_date', esc_attr(get_nice_date()));
    update_post_meta($order_id, 'delivery_date_timestamp', get_timestamp());
    update_post_meta($order_id, 'delivery_option', lf_get_delivery_option());
    update_post_meta($order_id, 'delivery_option_value',
        esc_attr(get_option('lf_delivery_option_opt_value'.lf_get_delivery_option())));



}

function add_delivery_option_to_mail($fields, $sent_to_admin, $order)
{
    $fields['delivery_date'] = array(
        'label' => __('Delivery Date', "lf-delivery-option"),
        'value' => get_post_meta($order->id, 'delivery_date', true),
    );
    $fields['delivery_option_value'] = array(
        'label' => __('Delivery Option', "lf-delivery-option"),
        'value' => __(get_post_meta($order->id, 'delivery_option_value', true), "lf-delivery-option"),
    );
    return $fields;
}

function lf_validate_delivery_option() {

    $delivery_date = lf_get_delivery_date();
    $delivery_option = lf_get_delivery_option();

    if (!isset($delivery_option)) {
        $message = '<strong>' . __('Delivery Option', 'lf-delivery-option') . '</strong> '.__('is required', 'lf-delivery-option');
        wc_add_notice($message, $notice_type = 'error');
    }

    if (!$delivery_date) {
        $message = '<strong>' . __('Delivery Date', 'lf-delivery-option') . '</strong> '.__('is required', 'lf-delivery-option');
        wc_add_notice($message, $notice_type = 'error');
    }
}

function enqueue($hook)
{
    $version = '0.0.1';
    $calendar_langs = json_decode(constant('LinkedFarm\DeliveryOption\Common\CalendarLangs'));
    wp_dequeue_script('themeswitcher');
    wp_enqueue_script('themeswitcher-orddd', plugins_url('/js/jquery.themeswitcher.min.js', __FILE__),
        array('jquery', 'jquery-ui-sortable', 'jquery-ui-datepicker'), $version, false);

    foreach ($calendar_langs as $key => $value) {
        wp_enqueue_script($value, plugins_url("/js/i18n/jquery.ui.datepicker-$key.js", __FILE__),
            array('jquery', 'jquery-ui-datepicker'), $version, false);
    }

    wp_register_style('woocommerce_admin_styles', plugins_url() . '/woocommerce/assets/css/admin.css', array(),
        WC_VERSION);
    wp_enqueue_style('woocommerce_admin_styles');
    wp_enqueue_style('order-delivery-date', plugins_url('/css/order-delivery-date.css', __FILE__), '', $version, false);
    wp_register_style('jquery-ui-style', '//code.jquery.com/ui/1.9.2/themes/smoothness/jquery-ui.css', '', $version,
        false);
    wp_enqueue_style('jquery-ui-style');
    wp_enqueue_style('datepicker', plugins_url('/css/datepicker.css', __FILE__), '', $version, false);
}

////ORDER DATE ----------------->
function lf_is_user_logged_in()
{
    $user = wp_get_current_user();
    if (!$user)
        return false;
    return $user->exists();
}

function lf_get_delivery_option()
{
    if (!lf_is_user_logged_in() || !WC()->session) {
        if (!isset($_SESSION["delivery_option"])) {
            $_SESSION["delivery_option"] = 0;
        }
        //return $_SESSION["delivery_option"];
        return $_SESSION["delivery_option"];
    } else {
        if (!WC()->session->get("delivery_option")) {
            WC()->session->set("delivery_option", 0);
        }
        //return WC()->session->get("delivery_option");
        return WC()->session->get("delivery_option");
    }
}


function lf_get_delivery_date()
{
    $max_min_date  = get_min_date(lf_get_delivery_option());
    if (!lf_is_user_logged_in() || !WC()->session) {
        if(!$max_min_date)
            $_SESSION["delivery_date"] = null;
        return $_SESSION["delivery_date"];
    } else {
        if(!$max_min_date)
            WC()->session->set("delivery_date", null);
        return WC()->session->get("delivery_date");
    }
}

function lf_change_ddate($new_date = false)
{
    if(!$new_date) $new_date = $_POST['deliveryDate'];
    if (!lf_is_user_logged_in() || !WC()->session) {
        $_SESSION["delivery_date"] = $new_date;
    } else {
        WC()->session->set("delivery_date", $new_date);
    }
    die();
}

function lf_change_option()
{
    if (!lf_is_user_logged_in() || !WC()->session) {
        $_SESSION["delivery_option"] = $_POST['deliveryOption'];
    } else {
        WC()->session->set("delivery_option", $_POST['deliveryOption']);
    }

    $min_date = get_min_date(lf_get_delivery_option());
    lf_change_ddate($min_date);

    die();
}


//add_action( 'woocommerce_product_query', 'filter_deliveryDate' );
//
//function filter_deliveryDate( $q ){
//    //echo WC()->session->get("delivery_date")."</br>";
//    $q->set( 'post_parent', 0 );
//}

function get_min_date($option)
{
    $current_time = current_time('timestamp');
    $delivery_time_seconds = get_option('lf_delivery_option_prep_time') * 60 * 60;
    $cut_off_timestamp = $current_time + $delivery_time_seconds;
    $min_date = new \DateTime();
    $min_date->setTimestamp($cut_off_timestamp);
    $one_day = new \DateInterval('P1D');
    $curr_min_day = $min_date->format("w");
    //echo "Min: " .  $min_date->format("d-m-Y"). "</br>";
    for ($i = $curr_min_day; $i < 7 + $curr_min_day; $i++) {
        $dd = $i % 7;
        //echo "lf_orddd_weekday_" . $dd . " : " . get_option("lf_orddd_weekday_" . $dd) . "</br>";
        if (get_option("lf_delivery_option_available_day" . "$option-$dd")) {
            //echo "Cool: " . $dd . "</br>";
            break;
        } else {
            $min_date->add($one_day);
        }
    }
    if (!get_option("lf_delivery_option_available_day" . "$option-" . $min_date->format("N"))) {
       // echo "NOT Cool: " . $min_date->format("w") . "</br>";
        //no date available
        $min_date = null;
    }
    return $min_date;
}

function get_date_obj()
{
    $date =
        (lf_is_user_logged_in() && WC()->session ? WC()->session->get("delivery_date") : $_SESSION["delivery_date"]);
    $format = "d-m-Y";
    $dateobj = \DateTime::createFromFormat($format, $date);
    return $dateobj;
}

function ensureDateValid($date)
{
    //echo "<hr>".$date."<hr>";
    $min_date = get_min_date(lf_get_delivery_option());
    //echo "<hr> MIN DATE:".$min_date."<hr>";
    if (is_null($min_date)) {
        if (WC()->session) {
            WC()->session->set("delivery_date", null);
        }
        $_SESSION["delivery_date"] = null;
    } elseif (!$date || $date < $min_date) {
        $new_date = $min_date->format('d-m-Y');
         if (!lf_is_user_logged_in() || !WC()->session) {
             $_SESSION["delivery_date"] = $new_date;
         } else {
             WC()->session->set("delivery_date", $new_date);
         }
    }
}

function lf_start_session()
{
    if (!lf_is_user_logged_in() && !session_id()) {
        session_start();
    }
    ensureDateValid(get_date_obj());

}


function lf_end_session()
{
    if (isset($_SESSION)) {
        WC()->session->set("delivery_option", $_SESSION["delivery_option"]);
        WC()->session->set("delivery_date", $_SESSION["delivery_date"]);
        session_destroy();
    }
}


function get_day_of_week($date)
{
    if ($date == null)
        return "";
    $format = "d-m-Y";
    $dateobj = \DateTime::createFromFormat($format, $date);
    return $dateobj->format("D");
}

function is_product_available($product_id)
{

    $producer_id = get_post_meta($product_id, "food_product_produced_by");

    if ($producer_id != null) {
        $days_available = get_post_meta($producer_id, "producer_days_available");
        $opts_available = get_post_meta($producer_id, "producer_opts_available");
        if ($days_available != null && $days_available[0] != null && $opts_available != null &&
            $opts_available[0] != null
        ) {
            //var_dump($days_available);die();
            return in_array(get_day_of_week(lf_get_delivery_date()), $days_available[0]) &&
            in_array(lf_get_delivery_option(), $opts_available[0]);
        }
    }

    return false;
}


function lf_filter_producer_available($purchasable, $product)
{
    if (!is_product_available($product->id)) {
        return false;
    }
    return $purchasable;
}

//function lf_filter_producer_available_product_visible( $visible, $product_id) {
//    if(!is_product_available($product_id)) {
//        return false;
//    }
//    return $visible;
//}

function lf_filter_query_producer_available($meta_query, $wc_query)
{
    $args = array(
        'post_type' => array('food_actor'),
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
    $producers_list = get_posts($args);

    if ($producers_list != null && is_array($producers_list)) {
        $available_producers = array();
        $available_producers[] = -1;
        foreach ($producers_list as $producer) {
            $days_available = get_post_meta($producer->ID, "producer_days_available");
            $opts_available = get_post_meta($producer->ID, "producer_opts_available");
            if ($days_available != null && $days_available[0] != null
                && $opts_available != null && $opts_available[0] != null
            ) {
                echo get_day_of_week(lf_get_delivery_date());
                echo "<hr/>";
                echo lf_get_delivery_option();
                echo "<hr/>";
                if (in_array(get_day_of_week(lf_get_delivery_date()), $days_available[0])
                    && in_array(lf_get_delivery_option(), $opts_available[0])
                ) {
                    $available_producers[] = $producer->ID;
                }

            }

        }

        $meta_query['available'] = array(
            'key' => 'food_product_produced_by',
            'value' => $available_producers,
            'compare' => 'IN'
        );
    }
    return $meta_query;
}


////ORDER DATE <-----------------