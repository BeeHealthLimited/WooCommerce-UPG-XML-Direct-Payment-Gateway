<?php
/*
Plugin Name: WooCommerce UPG API Payment Gateway
Plugin URI: http://www.upg.co.uk/
Description: UPG Direct Payment Gateway For Woocommerce
Version: 0.0.1
Author: Author: Darren Potter, Mark Hunter
Author URI: http://www.upg.co.uk/ https://github.com/BeeHealthLimited/WooCommerce-UPG-XML-Direct-Payment-Gateway
*/
add_action('plugins_loaded', 'woocommerce_gateway_upg_api_init', 0);

function woocommerce_gateway_upg_api_init() {
    
    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
    
    require_once('woocommerce-upg-api-gateway.php');
    
    /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_upg_api_add_gateway($methods) {
        $methods[] = 'WC_Gateway_UPG_api';
        return $methods;
    }
     add_filter('woocommerce_payment_gateways', 'woocommerce_upg_api_add_gateway' );
}