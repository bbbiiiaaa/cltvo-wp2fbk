<?php
/*
Plugin Name: EL CULTIVO - Wp2Fbk
Plugin URI: http://elcultivo.mx
Description: Las publicaciones de WP se publican una página de Fbk (cada attachment es un post de Fbk).
Version: 1.0
Author: El Cultivo
Author URI: http://elcultivo.mx
*/

//Plugin admin pag:
//Activar: Configura los parámetros en la página de plugin
//Guarda en la base el AppId, Secret, PagId, Token y expiración

//New post:
//Si no tiene token no te deja autenticar
//Publica imágenes en Fbk de todos las imágenes adjuntas
//Guarda el id del post fbk en el meta del post de WP

//Front-end:
//Muestra los likes guardados en el meta
//Al dar like te pide hacer login vía JS
//Si logeado, postea un like en Fbk, y actualiza el meta con el total real de likes

$cltvo_fbk_class_file = home_url('/wp-content/plugins/cltvo-wp2fbk/cltvo_fbk_class.php');
include_once cltvo_wpURL_2_path($cltvo_fbk_class_file);

add_theme_support( 'post-thumbnails' );

add_action( 'admin_menu', 'wp2Fbk_registrar_pagina' );
add_action( 'save_post', 'cltvo_fbk_save_post', 10, 2 );
add_action( 'add_meta_boxes', 'wp2Fbk_metaboxes' );
add_action( 'load-admin_page_myplugin-custom-page', 'myplugin_custom_page_redirect' );
add_action('admin_init', 'cltvo_wp2fbk_preprocess_pages');
add_action( 'wp_enqueue_scripts', 'wp2fbk_js' );

function wp2fbk_js(){
	wp_register_script( 'wp2fbk_functions_js', BLOGURL.'/wp-content/plugins/cltvo-wp2fbk/js/wp2fbk-functions.js', array('jquery'), false, true );
	wp_localize_script( 'wp2fbk_functions_js', 'wp2fbk_vars', wp2fbk_vars() );

	wp_enqueue_script('wp2fbk_functions_js');
}

function wp2fbk_vars(){
	$php2js_vars = array(
		'site_url'     => home_url('/'),
		'template_url' => get_bloginfo('template_url'),
		'appid' => get_option('cltvo_fbk_appId')
	);
	return $php2js_vars;

}

//Imprime la página para configurar los comentarios
function wp2Fbk_registrar_pagina(){
    add_menu_page( 'Cltvo Fbk Admin', 'Facebook', 'publish_posts', 'cltvo-wp2fbk/wp2fbk-page.php');
}

//METABOX
function wp2Fbk_metaboxes(){
	add_meta_box(
		'post2fbk_mb',
		'Facebook',
		'post2fbk_mb',
		'crdmn_proyecto_pt',
		'side'
	);
	add_meta_box(
		'post2fbk_mb',
		'Facebook',
		'post2fbk_mb',
		'post',
		'side'
	);
}
function post2fbk_mb($obj){
	$appId = get_option('cltvo_fbk_appId');
	$secret = get_option('cltvo_fbk_secret');
	$token = get_option('cltvo_fbk_pageToken');

	if( $fid = get_post_meta($obj->ID, 'fbk_post_id', true) ){
		echo "Publicado con el FID: $fid";
	}elseif( $appId && $secret && $token ){
		echo '<p><input type="checkbox" name="post2fbk_in" > Publicar en Facebook</p>';
	}else{
		echo 'Para publicar en Facebook, primero tienes que <a href="';
		echo admin_url('/admin.php?page=cltvo-wp2fbk/wp2fbk-page.php');
		echo '">autoriazar la aplicación.</a>';
	}
}

function cltvo_wp2fbk_preprocess_pages($value){
    global $pagenow;
    $page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : false);
    if($pagenow=='admin.php' && $page=='cltvo-wp2fbk/wp2fbk-page.php'){

		//$cltvo_fbk_options = get_option('cltvo_fbk_options');

		if( isset($_POST['cltvo-wp2fbk_appId']) )
			update_option( 'cltvo_fbk_appId', $_POST['cltvo-wp2fbk_appId'] );

		if( isset($_POST['cltvo-wp2fbk_appSecret']) )
			update_option( 'cltvo_fbk_secret', $_POST['cltvo-wp2fbk_appSecret'] );

		if( isset($_POST['pagina_por_administrar']) )
			update_option( 'cltvo_fbk_pagId', $_POST['pagina_por_administrar'] );

		$appId = get_option('cltvo_fbk_appId');
		$secret = get_option('cltvo_fbk_secret');

		if( $appId && $secret ){
			$GLOBALS['cltvo_fbk'] = new CLTVO_fbk($appId, $secret);

			if( $pagId = get_option('cltvo_fbk_pagId') ){
				$GLOBALS['cltvo_fbk']->set_pagId($pagId);
				if( $pageToken = get_option('cltvo_fbk_pageToken') ){
					$GLOBALS['cltvo_fbk']->set_pageToken($pageToken);
					//aquí mostraría info.
				}else{
					//no tiene guardado pageToken
					$GLOBALS['cltvo_fbk']->validate_user('publish_stream,read_stream,manage_pages');
					update_option('cltvo_fbk_pageToken', $GLOBALS['cltvo_fbk']->get_page_token());
				}//pageToken
			}else{
				//no tiene guardado pagId
				$GLOBALS['cltvo_fbk']->validate_user('publish_stream,read_stream,manage_pages');
				//aquí mostraría las páginas disponibles
			}//pagId
		}//appID
	}
}

//Regresa el path absoluto de $file
if (!function_exists('cltvo_plugin_path')) { 
	function cltvo_plugin_path($file){
		$path = dirname(__FILE__) . '/' . $file;
		return $path;
	}
}

function cltvo_fbk_save_post($id, $post){

	// Checkbox
	if( !isset($_POST['post2fbk_in']) ) return;

	// Permisos
	if( !current_user_can('edit_post', $id) ) return;

	// Vs Autosave
	if( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE ) return;
	if( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
	if( wp_is_post_revision($id) OR wp_is_post_autosave($id) ) return;

	if( $post->post_status != 'publish') return;

	$appId = get_option('cltvo_fbk_appId');
	$secret = get_option('cltvo_fbk_secret');

	$cltvo_fbk = new CLTVO_fbk($appId, $secret);
	$args = array(
		'message' => $post->post_title ."\r\n". get_permalink($post->ID),
		'image' => '@' . cltvo_wpURL_2_path( wp_get_attachment_url(get_post_thumbnail_id($post->ID)) )
	);
	$new_fbk_post = $cltvo_fbk->post_ph_2fbk( $args );
	if($new_fbk_post){
		update_post_meta($post->ID, 'fbk_post_id', $new_fbk_post['id']);
	}

	// $img_ids = cltvo_todosIdsImgsDelPost($id);

	// if( is_array($img_ids) ){
	// 	$cltvo_fbk = new CLTVO_fbk($appId, $secret);

	// 	foreach ($img_ids as $img_id) {
	// 		$args = array(
	// 			'message' => $post->post_content . ' http://www.hildacalderon.com/category/prendas/#prendas',
	// 			'image' => '@' . cltvo_wpURL_2_path( wp_get_attachment_url($img_id) )
	// 		);

	// 		$new_fbk_post = $cltvo_fbk->post_ph_2fbk( $args );

	// 		if($new_fbk_post){
	// 			update_post_meta($img_id, 'fbk_post_id', $new_fbk_post['id']);
	// 		}
	// 	}
	// }
}

if(!function_exists('cltvo_fbk_option')){
	function cltvo_fbk_option( $option ) {
		$options = get_option( 'cltvo_fbk_options' );
		if ( isset( $options[$option] ) )
			return $options[$option];
		else
			return false;
	}
}
?>