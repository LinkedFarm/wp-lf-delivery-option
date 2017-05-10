<?php
namespace LinkedFarm\DeliveryOption\Admin;

function init()
{
    add_filter('manage_edit-shop_order_columns',
            'LinkedFarm\DeliveryOption\Admin\wc_order_delivery_date_column',
        20, 1);
    add_action('manage_shop_order_posts_custom_column',
            'LinkedFarm\DeliveryOption\Admin\wc_custom_column_value',
        20, 1);
    add_filter('manage_edit-shop_order_sortable_columns',
            'LinkedFarm\DeliveryOption\Admin\wc_custom_column_value_sort');
    add_filter('request',
        'LinkedFarm\DeliveryOption\Admin\wc_delivery_date_orderby');

}


/**
 * This function are used for show custom column on order page listing. woo-orders
 *
 */
function wc_order_delivery_date_column($columns)
{
    $new_columns = (is_array($columns)) ? $columns : array();
    unset($new_columns['order_actions']);
    //edit this for you column(s)
    //all of your columns will be added before the actions column
    $new_columns['delivery_date'] =
        __('Delivery Date', 'lf-delivery-option'); //Title for column heading
    $new_columns['delivery_option_value'] =
        __('Delivery Option', 'lf-delivery-option'); //Title for column heading
    $new_columns['order_actions'] = $columns['order_actions'];
    return $new_columns;
}

/**
 * This fnction used to add value on the custom column created on woo- order
 *
 */
function wc_custom_column_value($column)
{
    global $post;
    if ($column == 'delivery_date') {
        echo get_post_meta($post->ID, 'delivery_date', true);
    }else if ($column == 'delivery_option_value') {
        echo get_post_meta($post->ID, 'delivery_option_value', true);
    }
}

/**
 * Meta key for sorting the column
 * @param array $columns
 * @return array
 */
function wc_custom_column_value_sort($columns)
{
    $columns['delivery_date'] = 'delivery_date_timestamp';
    return $columns;
}

/**
 * Delivery date column orderby. This help woocommerce to understand which column need to sort on which value.
 * The delivery date is stored as a timestamp in the _lf_orddd_timestamp variable in wp_postmeta
 *
 * @param array $vars
 * @return array
 **/
function wc_delivery_date_orderby($vars)
{
    global $typenow;
    $delivery_field_label = 'delivery_date_timestamp';
    if (isset($vars['orderby'])) {
        if ($delivery_field_label == $vars['orderby']) {
            $sorting_vars = array('orderby' => 'meta_value_num');
            if (!isset($_GET['lf_delivery_option_filter']) || $_GET['lf_delivery_option_filter'] == '') {
                $sorting_vars['meta_query'] = array('relation' => 'OR',
                    array(
                        'key' => $delivery_field_label,
                        'value' => '',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key' => $delivery_field_label,
                        'compare' => 'EXISTS'
                    )
                );
            }
            $vars = array_merge($vars, $sorting_vars);
        }
    }
//    elseif (get_option('lf_orddd_enable_default_sorting_of_column') == 'checked') {
//        if ('shop_order' != $typenow) {
//            return $vars;
//        }
//        $sorting_vars = array(
//            'orderby' => 'meta_value_num',
//            'order' => 'DESC');
//        if (!isset($_GET['lf_delivery_option_filter']) || $_GET['lf_delivery_option_filter'] == '') {
//            $sorting_vars['meta_query'] = array('relation' => 'OR',
//                array(
//                    'key' => $delivery_field_label,
//                    'value' => '',
//                    'compare' => 'NOT EXISTS'
//                ),
//                array(
//                    'key' => $delivery_field_label,
//                    'compare' => 'EXISTS'
//                )
//            );
//        }
//        $vars = array_merge($vars, $sorting_vars);
//    }
    return $vars;
}