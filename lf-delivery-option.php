<?php
/*
Plugin Name: Delivery Option
Plugin URI: https://linkedfarm.github.io/wp-plugin/deliveryoption
Description: 
Author: Linked.Farm
Version: 0.0.1
Text Domain: lf-delivery-option
Domain Path: /i18n/languages/
License: AGPLv3+
License URI: https://www.gnu.org/licenses/agpl-3.0.en.html

Delivery Option is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

Delivery Option is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with Delivery Option. If not, see 
https://www.gnu.org/licenses/agpl-3.0.en.html.
*/


if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
        require_once(__DIR__ . '/vendor/autoload.php');
    }

    include_once('delivery-option.php');
}

//TODO add notice

