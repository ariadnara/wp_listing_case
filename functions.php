<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'minireset','flexboxgrid','hivetheme-core-frontend','hivetheme-parent-frontend','hivetheme-parent-frontend' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION


require 'wp_ext_actions_and_shortcode.php';
require __DIR__.'/visual_model/payment_elements.php';

/**
 * Acciones añadidas
 */
//add_action('wp_head', 'wp_ext_excess_reserve');
add_action('wp_head', 'reprogram_original_element_template');
/**
 * Shortcodes
 */
 add_action('wp_body_open', 'get_shortcodes');
 
function get_shortcodes(){
    $current_post = get_post(); 

    switch($current_post->post_name){
        case 'comprar-comodin' : add_shortcode('shortcode_paypal_payment_acq_reserved', 'wp_ext_paypal_payment_acq_reserved'); break;
        case 'comprar-creditos' :  add_shortcode('shortcode_paypal_payment_value3', 'wp_ext_paypal_payment_credits'); break;
        case 'confirmar-donacion':  add_shortcode('shortcode_status_confirm_code', 'wp_ext_shortcode_confirm_code'); break;
        case 'listado-ventas':            
            add_shortcode('shortcode_table_payment', 'wp_ext_shortcode_get_table_payment'); break;
        case 'pago' : 
            add_shortcode('shortcode_paypal_value1', 'wp_ext_paypal_value_by_acquisition');
            add_shortcode('shortcode_paypal_value2', 'wp_ext_paypal_value_tax_iva');
            add_shortcode('shortcode_paypal_item1', 'wp_ext_paypal_title_val_acq');
            add_shortcode('shortcode_paypal_item2', 'wp_ext_paypal_title_tax_iva');
            add_shortcode('shortcode_paypal_payment_value1', 'wp_ext_paypal_payment_acquisition'); break;
        case 'hello-world':
        case 'starting-a-small-business': add_shortcode('shortcode_show_stadistic', 'wp_ext_show_stadistic');
        case 'home' : add_shortcode('shortcode_status_listing_user', 'wp_ext_show_txt_welcome'); break;        
        case 'descuento-de-credito': 
        case 'descuento-por-comodin-de-reserva':
        case 'pago-efectuado-correctamente': add_shortcode('shortcode_get_info_acq', 'wp_ext_show_data_acq');
                                             add_shortcode('shortcode_back_to_acq', 'wp_ext_get_url_acq');
         break;
    }
}
