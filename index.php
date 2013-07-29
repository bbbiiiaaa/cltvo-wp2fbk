<?php
/*
Plugin Name: EL CULTIVO - Wp2Fbk
Plugin URI: http://elcultivo.mx
Description: Las publicaciones de WP se publican una página de Fbk
Version: 0.1
Author: El Cultivo
Author URI: http://elcultivo.mx
*/


add_action( 'admin_menu', 'wp2Fbk_registrar_pagina' );
add_action( 'admin_enqueue_scripts', 'wp2Fbk_registrar_js' );

//Imprime la página para configurar los comentarios
function wp2Fbk_registrar_pagina(){
    add_menu_page( 'wp2Fbk', 'wp2Fbk', 'manage_options', 'cltvo-wp2fbk/wp2fbk-page.php');
}

//Registra el functions.js y le pasa algunas variablesxml2post
function wp2Fbk_registrar_js(){
	$plugin_url = plugins_url('/', __FILE__);
	wp_register_script('wp2Fbk_functions_js', $plugin_url . 'js/wp2fbk-functions.js', array('jquery'), false, false );

	$wp2Fbk_vars = array(
		'site_url'     => home_url('/'),
		'plugin_url' => $plugin_url,
		'plugin_path' => dirname(__FILE__).'/'
	);
	wp_localize_script( 'wp2Fbk_functions_js', 'wp2Fbk_vars', $wp2Fbk_vars );
	
	wp_enqueue_script ('wp2Fbk_functions_js');
}

//Regresa el path absoluto de $file


if (!function_exists('cltvo_plugin_path')) { 
	function cltvo_plugin_path($file){
		$path = dirname(__FILE__) . '/' . $file;
		return $path;
	}
}

?>