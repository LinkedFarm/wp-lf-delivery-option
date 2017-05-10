<?php
namespace LinkedFarm\DeliveryOption\Meta;

//add_action( 'rest_api_init', function () {
//    register_rest_route( 'mp/v1', '/producer/(?P<id>\d+)/meta', array(
//        'methods' => 'GET',
//        'callback' => 'producer_meta',
//    ) );
//} );
//
//function producer_meta( $data ) {
//    $posts = get_posts( array(
//        'author' => $data['id'],
//    ) );
//
//    if ( empty( $posts ) ) {
//        return null;
//    }
//
//    return $posts[0]->post_title;
//}

function init() {
    add_action('cmb2_init', 'LinkedFarm\DeliveryOption\Meta\lf_metabox_producer');

}

function lf_metabox_producer()
{
    $prefix = 'producer_';

    $cmb = new_cmb2_box(array(
        'id' => $prefix . 'producer_opt',
        'title' => __('Producer Options', 'linkedfarm'),
        'object_types' => array('producer'),
        'context' => 'normal',
        'priority' => 'high',
    ));



    $cmb->add_field(array(
        'name' => __('Available Days', 'linkedfarm'),
        'id' => $prefix . 'days_available',
        'type' => 'multicheck',
        'default' => 'Fri',
        'options' => array(
            'Mon' => __('Mon', 'linkedfarm'),
            'Tue' => __('Tue', 'linkedfarm'),
            'Wed' => __('Wed', 'linkedfarm'),
            'Thu' => __('Thu', 'linkedfarm'),
            'Fri' => __('Fri', 'linkedfarm'),
            'Sat' => __('Sat', 'linkedfarm'),
            'Sun' => __('Sun', 'linkedfarm')
        ),
    ));

    $opts_opts = array();
    $opts = get_option('lf_delivery_option_number_of_options');
    for ($j = 0; $j < $opts; $j++) {
        $opt = 'lf_delivery_option_opt_value' . $j;
        $opt_val = get_option($opt);
        if (!$opt_val)
            continue;
        $opts_opts[$j] = __($opt_val, 'lf-delivery-option');
    }

    $cmb->add_field(array(
        'name' => __('Available Options', 'linkedfarm'),
        'id' => $prefix . 'opts_available',
        'type' => 'multicheck',
        'default' => '0',
        'options' => $opts_opts,
    ));

}



?>