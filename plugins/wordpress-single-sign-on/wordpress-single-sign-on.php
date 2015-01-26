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

		add_filter( 'gp_logout_link', array( $this, 'wpsso_logout_link_filter' ), 10, 1 );
		add_filter( 'gp_login_link', array( $this, 'wpsso_login_link_filter' ), 10, 1 );
	}

	public function wpsso_logout_link_filter( $default ) {
		return '';
	}

	public function wpsso_login_link_filter( $default ) {
		return '';
	}

}

GP::$plugins->gp_wordpress_single_sign_on = new  GP_WordPress_Single_Sign_On;