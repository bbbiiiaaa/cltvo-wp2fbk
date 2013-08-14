<?php
/*
Plugin Name: EL CULTIVO - Wp2Fbk
Plugin URI: http://elcultivo.mx
Description: Las publicaciones de WP se publican una página de Fbk
Version: 0.2
Author: El Cultivo
Author URI: http://elcultivo.mx
*/

add_theme_support( 'post-thumbnails' );

add_action( 'admin_menu', 'wp2Fbk_registrar_pagina' );
add_action( 'admin_enqueue_scripts', 'wp2Fbk_registrar_js' );
add_action( 'save_post', 'cltvo_fbk_save_post', 10, 2 );

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

function cltvo_fbk_save_post($id, $post){

	// Permisos
	if( !current_user_can('edit_post', $id) ) return;

	// Vs Autosave
	if( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE ) return;
	if( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
	if( wp_is_post_revision($id) OR wp_is_post_autosave($id) ) return;

	if( $post->post_status != 'publish') return;

	//FBK!!!
	include_once 'fbk-api/facebook.php';

	$pagId = cltvo_fbk_option('pagId');


	$facebook = new Facebook(array(
		'appId' => cltvo_fbk_option('appId'),
		'secret' => cltvo_fbk_option('secret'),
		'cookie' => true
	));

	$facebook->setExtendedAccessToken();
	$access_token = $facebook->getAccessToken();

	$accounts = $facebook->api(
	   '/me/accounts',
	   'GET',
	   array(
	      'access_token' => $access_token
	   )
	);
	$accounts = $accounts['data'];
	foreach($accounts as $account){
		if( $account['id'] == $pagId ){
			$facebook->setFileUploadSupport(true);
			
			$args = array(
				'access_token' => $account['access_token'],
				'message' => $post->post_content . "\n" . get_permalink( $id ),
				'image' => '@' . cltvo_img_root( wp_get_attachment_url(get_post_thumbnail_id( $id )) )
			);
			$new_fbk_post = $facebook->api(
				'/me/photos',
				'POST',
				$args
			);
		}
	}

}

function cltvo_fbk_option( $option ) {
	$options = get_option( 'cltvo_fbk_options' );
	if ( isset( $options[$option] ) )
		return $options[$option];
	else
		return false;
}

function cltvo_img_root( $img_url ){
	$path = get_theme_root();
	$path = str_replace('wp-content/themes', '', $path);
	$path = str_replace(home_url('/'), $path, $img_url);
	return $path;
}
?>