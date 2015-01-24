<?php

class GP_User_Slug_for_Downloads extends GP_Plugin {
	public $id = 'use-slug-for-downloads';

	public $errors  = array();
	public $notices = array();

	public function __construct() {

		if( ! gp_const_get('GP_USE_SLUG_FOR_DOWNLOADS') ) { 
			return; 
		}
	
		parent::__construct();

		add_filter( 'export_filename', array( $this, 'filter_download_filename' ), 10, 5 );
	}

	public function filter_download_filename( $filename, $project_path, $translation_set_slug, $export_locale, $format_extension ) {
		if( $translation_set_slug != '' && $translation_set_slug != 'default' ) {
			$filename = $translation_set_slug . '.' . $format->extension;
		}
		
		return $filename;
	}

}

GP::$plugins->gp_use_slug_for_downloads = new  GP_User_Slug_for_Downloads;