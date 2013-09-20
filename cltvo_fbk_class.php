<?php 
/** 
 * CLTVO_fbk
 * 
 * Hace autenticación inicial para poder saber sacar likes de FBK
 * apartir de su ID
 */ 

class CLTVO_fbk{

	/**
     * @var $facebook - Facebook obj del API
     * @var $pagId - ID de la página por administrar
     * @var $accounts - Todas las cuentas adminstrables (aquí están los tokens)
     * @var $likes - Aquí se guarda un array con los likes
     */

	private $facebook;
	public  $pagId;
	public  $userToken;
	public  $pageToken;
	public  $pageTokenExpires;
	public  $accounts;

	/**
	 * Al crear una nueva instacia de CLTVO_fbk se crea una nueva instacia de facebook
     * @param string/int $appId - ID de la aplicación de FBK
     * @param string $secret - secret code de la aplicación de Fbk
     */

	public function __construct($appId=false, $secret=false){

		$fbk_php_file = home_url('/wp-content/plugins/cltvo-wp2fbk/fbk-api/facebook.php');
		include_once cltvo_wpURL_2_path($fbk_php_file);

		if(!$appId)
			$appId = get_option('cltvo_fbk_appId');

		if(!$secret)
			$secret = get_option('cltvo_fbk_secret');

		if($appId && $secret){
			$this->facebook = new Facebook(array(
				'appId' => $appId,
				'secret' => $secret
			));
		}else{
			return false;
		}
	}

	/**
	 * Sets the ID of the Facebook page to administrate
     * @param string/int $pagId - Facebook page ID
     */

	public function set_pagId($pagId){
		$this->pagId = $pagId;
	}

	/**
	 * Sets the token of the page we want to admin
     * @param string/int $pageToken - Facebook page token
     */

	public function set_pageToken($pageToken){
		$this->pageToken = $pageToken;
	}

	/**
	 * valida que haya un usuario logeado en Fbk
	 * y que le haya dado a la app los permisos necesarios
	 * @param string $permissions_string - string de los permisos requeridos separados por comas
	*/

	public function validate_user($permissions_string){
		$this->user = $this->facebook->getUser();
		$perm_arr = explode(',', $permissions_string);

		if($this->user){
			$this->facebook->setExtendedAccessToken();
			$this->userToken = $this->facebook->getAccessToken();

			$permissions_list = $this->facebook->api(
				'/me/permissions',
				'GET',
				array(
					'access_token' => $this->userToken
				)
			);

			foreach($perm_arr as $perm) {
				if( !isset($permissions_list['data'][0][$perm]) || $permissions_list['data'][0][$perm] != 1 ) {
					$login_url_params = array(
						'scope' => $perm,
						'fbconnect' =>  1,
						'display'   =>  "page",
						'next' => admin_url('/admin.php?page=cltvo-wp2fbk/wp2fbk-page.php')
					);
					$login_url = $this->facebook->getLoginUrl($login_url_params);
					header("Location: {$login_url}");
					exit();
				}
			}
		}else{
			//si no hay usuario, redirígelo 
			$login_url_params = array(
				'scope' => $permissions_string,
				'fbconnect' =>  1,
				'redirect_uri' => admin_url('/admin.php?page=cltvo-wp2fbk/wp2fbk-page.php')
			);
			$login_url = $this->facebook->getLoginUrl($login_url_params);

			header("Location: {$login_url}");
			exit();
		}
	}

	/**
	 * Requiere haber setado el ID de la página
	 * Obtiene las páginas administradas por el usuario
	 * y que le haya dado a la app los permisos necesarios
	 * @param string $permissions_string - string de los permisos requeridos separados por comas
	*/
	public function get_page_token($returnExpires = false){
		if( !isset($this->accounts) ){
			$this->get_accounts();
		}

		foreach($this->accounts as $account){
			if( $account['id'] == $this->pagId ){

				$access_token_debug = $this->facebook->api('/debug_token', 'GET', array(
					'input_token' => $account['access_token'],
					'access_token' => $account['access_token']
				));
				$this->pageToken = $account['access_token'];
				$this->pageTokenExpires = $access_token_debug['data']['expires_at'];
			}
		}
		if($returnExpires){
			$token = array($this->pageToken, $this->pageTokenExpires);
		}else{
			$token = $this->pageToken;
		}
		return $token;
	}

	/**
	 * Requiere haber validado el usuario
	 * Setea y regresa el un array 0 con las 
	 * cuentas que el usuario puede administrar
	 * @return array of arrays $this->accounts - por cada account un array con name, id, token, etc
	*/

	public function get_accounts(){
		if( isset($this->accounts) ){
			return $this->accounts;
		}else{
			$accounts = $this->facebook->api(
				'/me/accounts',
				'GET',
				array(
					'access_token' => $this->userToken
				)
			);
			$this->accounts = $accounts['data'];
			return $this->accounts;
		}
	}

	public function get_page(){
		if( !isset($this->pagId) )
			$this->set_pagId( get_option('cltvo_fbk_pagId') );

		if( !isset($this->pageToken) )
			$this->set_pageToken( get_option('cltvo_fbk_pageToken') );

		$page = $this->facebook->api(
			'/' . $this->pagId,
			'GET',
			array('access_token'=>$this->pageToken)
		);
		return $page;
	}

	/**
     * @param string/int $post_id - Facebook post ID
     * @return array - Facebook likes array
     */
	public function get_likes($post_id){
		if( !isset($this->pageToken) )
			$this->set_pageToken( get_option('cltvo_fbk_pageToken') );

		$likes = $this->facebook->api(
			'/' . $post_id . '/likes',
			'GET',
			array('access_token'=>$this->pageToken)
		);
		return $likes;
	}

	/**
     * @param string/int $post_id - Facebook post ID
     * @return int - Number of likes
     */
	public function count_likes($id){
		if( !isset($this->pageToken) )
			$this->set_pageToken( get_option('cltvo_fbk_pageToken') );

		$likes_arr = $this->get_likes($id);
		$likes_count = count($likes_arr['data']);

		return $likes_count;
	}
}
?>