<?php
//echo "<pre>"; print_r($cltvo_fbk_options); echo "</pre>";
?>

<h2>El Cultivo | Post to Facebook status</h2>

<?php if( isset($GLOBALS['cltvo_fbk']) ):?>

	<?php if( isset($GLOBALS['cltvo_fbk']->userToken ) && !isset($GLOBALS['cltvo_fbk']->pagId) ):?>

		<p>Escoge la página que quieres administrar:</p>
		<form method="post" >
			<select name="pagina_por_administrar">
			<?php foreach( $GLOBALS['cltvo_fbk']->get_accounts() as $account ): ?>
				<option value="<?php echo $account['id'];?>"><?php echo $account['name'];?></option>
			<?php endforeach; ?>
			</select>
			<input type="submit" value="submit">
		</form>
	<?php endif;?>

	<?php if( isset($GLOBALS['cltvo_fbk']->pageToken) ):?>
		<p>
			<?php $page = $GLOBALS['cltvo_fbk']->get_page()?>
			Administrando correctamente: <br />
			<strong><?php echo $page['name'];?></strong> | id: <?php echo $page['id'];?>
		</p>

	<?php endif;?>
<?php else:?>
	<form method="post">
		<input name="cltvo-wp2fbk_appId" type="text" placeholder="App ID" />
		<input name="cltvo-wp2fbk_appSecret" type="text" placeholder="App Secret" />
		<input val="Guardar" type="submit">
	</form>
<?php endif;?>