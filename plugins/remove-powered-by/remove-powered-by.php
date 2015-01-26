<?php

class GP_Remove_Powered_By extends GP_Plugin {
	public $id = 'remove-powered-by';

	public $errors  = array();
	public $notices = array();

	private $key;

	public function __construct() {
		if( ! gp_const_get('GP_REMOVE_POWERED_BY') ) { 
			return; 
		}

		parent::__construct();

		add_action( 'pre_tmpl_load', array( $this, 'load_script' ), 10, 2 );
		add_action( 'gp_footer', array( $this, 'do_footer' ), 1, 0 );
	}

	public function load_script( $template, $args ) {

		$url = gp_url_public_root();

		if ( is_ssl() ) {
			$url = gp_url_ssl( $url );
		}

		wp_enqueue_script( 'remove-powered-by', $url . 'plugins/remove-powered-by/remove-powered-by.js', array( 'jquery' ) );

	}

	public function do_footer() {
		echo '<span style="display: none;">--GP_RPB_MARKER--</span>&nbsp;';
	}
}

GP::$plugins->gp_remove_powered_by = new GP_Remove_Powered_By;