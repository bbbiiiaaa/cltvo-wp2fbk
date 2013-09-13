<?php
if( isset($_POST['cltvo-wp2fbk_appId']) && isset($_POST['cltvo-wp2fbk_appSecret']) ){
	$cltvo_fbk_options = array(
		'appId' => $_POST['cltvo-wp2fbk_appId'],
		'secret'=> $_POST['cltvo-wp2fbk_appSecret']
	);
	if( update_option( 'cltvo_fbk_options', $cltvo_fbk_options ) ){
		echo "Se guardaron :)";
	}
}
?>

<h1>El Cultivo | WP2FBK</h1>
<?php if( !get_option('cltvo_fbk_options') ):?>
	<p>Primero, guardar los datos de la App:</p>
	<form method="post">
		<input name="cltvo-wp2fbk_appId" type="text" placeholder="App ID" />
		<input name="cltvo-wp2fbk_appSecret" type="text" placeholder="App Secret" />
		<input val="Guardar" type="submit">
	</form>
<?php endif;?>

<p>Pedir permisos:</p>
<ol>
	<li>En el admin, crear una nueva página llamada: "cltvo-wp2fbk"</li>
	<li>Mover "page-cltvo-wp2fbk.php" a la carpeta del theme activo</li>
	<li>Refrescar los permalinks</li>
	<li>ir a <a href="<?php bloginfo('url'); ?>/cltvo-wp2fbk" target="_blank">cltvo-wp2fbk </a>para dar permisos a Fbk de publicar</a></li>
	<li>¡listo! Para publicar posts en Fbk sólo se tiene que marcar el checkbox 
</ol>