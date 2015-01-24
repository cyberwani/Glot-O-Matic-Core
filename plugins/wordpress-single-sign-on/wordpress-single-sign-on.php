<?php

class GP_WordPress_Single_Sign_On extends GP_Plugin {
	public $id = 'wordpress-single-sign-on';

	public $errors  = array();
	public $notices = array();

	public function __construct() {

		if( ! gp_const_get('GP_WORDPRESS_SINGLE_SIGN_ON') ) { 
			return; 
		}
	
		parent::__construct();

		add_filter( 'gp_auth_cookie_malformed', array( $this, 'auth_cookie_malfored_filter' ), 10, 2 );
		add_filter( 'gp_auth_cookie_parse', array( $this, 'auth_cookie_parse_filter' ), 10, 2 );
		add_filter( 'gp_user_auth_key_string', array( $this, 'auth_cookie_key_string_filter' ), 10, 2 );
		add_filter( 'gp_user_auth_hash_function', array( $this, 'auth_cookie_hash_function_filter' ), 10, 2 );
		add_filter( 'gp_user_auth_hash_string', array( $this, 'auth_cookie_hash_string_filter' ), 10, 2 );
		add_filter( 'gp_logout_link', array( $this, 'logout_link_filter' ), 10, 1 );
		add_filter( 'gp_login_link', array( $this, 'login_link_filter' ), 10, 1 );
		add_filter( 'gp_auth_clear_cookies', array( $this, 'auth_clear_cookies_filter' ), 10, 1 );
	}

	public function auth_cookie_malfored_filter( $default, $element_count ) {
		if ( $element_count < 3 || $element_count > 4 ) {
			return true;
		}
		
		return $default;
	}

	public function auth_cookie_parse_filter( $default, $cookie_elements ) {
		if ( count( $cookie_elements == 4 ) ) {
			$auth_args = array();
			
			$auth_args['username'] = $cookie_elements[0];
			$auth_args['expiration'] = $cookie_elements[1];
			$auth_args['token'] = $cookie_elements[2];
			$auth_args['hmac'] = $cookie_elements[3];

			return $auth_args;
		}
	
		return $default;
	}

	public function auth_cookie_key_string_filter( $default, $auth_args ) {
		if( array_key_exists( 'token', $auth_args ) ) {
			return $auth_args['username'] . '|' . $auth_args['pass_frag'] . '|' . $auth_args['expiration'] . '|' . $auth_args['token'];
		}
		
		return $default;
	}

	public function auth_cookie_hash_function_filter( $default, $auth_args ) {
		if( array_key_exists( 'token', $auth_args ) ) {
			return function_exists( 'hash' ) ? 'sha256' : 'sha1';
		}
		
		return $default;
	}

	public function auth_cookie_hash_string_filter( $default, $auth_args ) {
		
		if( array_key_exists( 'token', $auth_args ) ) {
			return $auth_args['username'] . '|' . $auth_args['expiration'] . '|' . $auth_args['token'];
		}
		
		return $default;
	}

	public function logout_link_filter( $default ) {
		return '';
	}

	public function login_link_filter( $default ) {
		return '';
	}

	public function auth_clear_cookies_filter( $default ) {
		return null;
	}
	
}

GP::$plugins->gp_wordpress_single_sign_on = new  GP_WordPress_Single_Sign_On;