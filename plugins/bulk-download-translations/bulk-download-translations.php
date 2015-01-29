<?php

class GP_Bulk_Download_Translations extends GP_Plugin {
	public $id = 'bulk-download-translations';

	public $errors  = array();
	public $notices = array();

	private $key;

	public function __construct() {
		if( ! gp_const_get('GP_BULK_DOWNLOAD_TRANSLATIONS') ) { 
			return; 
		}

		// We need the Zip class to do the bulk export, if it doesn't exist, don't bother enabling the plugin.
		if( ! class_exists('ZipArchive') ) {
			return;
		}
		
		parent::__construct();

		$this->add_action( 'gp_project_actions', array( 'args' => 2 ) );
		
		// We can't use the filter in the defaults route code because plugins don't load until after
		// it has already run, so instead add the routes directly to the global GP_Router object.
		GP::$router->add( "/bulk-export/(.+?)", array( $this, 'bulk_export' ), 'get' );
		GP::$router->add( "/bulk-export/(.+?)", array( $this, 'bulk_export' ), 'post' );

	}

	public function gp_project_actions( $actions, $project ) {
		$actions[] .= gp_link_get( gp_url( 'bulk-export/' . $project->name), __('Bulk Export Translations') );
		
		return $actions;
	}
	
	public function before_request() {
	}
	
	public function bulk_export( $project_path ) {
		// By default we only download the PO files, but check to see if we've been told to do the MO files as well.
		$include_mos = false;
		if( gp_const_get('GP_BULK_DOWNLOAD_TRANSLATIONS_MO') ) { 
			$include_mos = true;
		}
				
		// Get a temporary file, use bdt as the first three letters of it.
		$temp_dir = tempnam(sys_get_temp_dir(), 'bdt');
		
		// Now delete the file and recreate it as a directory.
		unlink( $temp_dir );
		mkdir( $temp_dir );
		
		// Create a project class to use to get the project object.
		$project_class = new GP_Project;
		
		// Get the project object from the project path that was passed in.
		$project_obj = $project_class->by_path( $project_path );
		
		// Get the translations sets from the project ID.
		$translation_sets = GP::$translation_set->by_project_id( $project_obj->id );
		
		// Setup an array to use to track the file names we're creating.
		$files = array();
		
		// Loop through all the sets.
		foreach( $translation_sets as $set ) {
			// Export the PO file for this translation set.
			$files[] .= $this->_export_to_file( 'po', $temp_dir, $project_obj, $locale, $set );
			
			// If we're include the MO files, do so now.
			if( $include_mos ) {
				$files[] .= $this->_export_to_file( 'mo', $temp_dir, $project_obj, $locale, $set );
			}
			
		}
		
		// Setup the zip file name to use, it's the project name + .zip.
		$zip_file = $temp_dir . '/' . $project_path . '.zip';

		// Create the new archive.
		$zip = new ZipArchive;
		if ( $zip->open($zip_file, ZipArchive::CREATE) === TRUE ) {
			// Loop through all of the files we created and add them to the zip file.
			foreach( $files as $file ) {
				// The first parameter is the full path to the local file, the second is the name as it will appear in the zip file.
				// Note this does not actually write data to the zip file.
				$zip->addFile($temp_dir . '/' . $file, $file);
			}
			
			// Close the zip file, this does the actual writing of the data.
			$zip->close();
		}

		// Since we can't delete the export files until after we close the zip, loop through the files once more
		// and delete them.
		foreach( $files as $file ) {
			unlink( $temp_dir . '/' . $file );
		}

		// Generate our headers for the file download.
		$last_modified = gmdate( 'D, d M Y H:i:s' ) . ' GMT';
		header('Content-Description: File Transfer');
		header('Pragma: public');
		header('Expires: 0');
		header('Last-Modified: ' . $last_modified);
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Disposition: attachment; filename=' . $project_path . '.zip');
		header('Content-Type: application/octet-stream');
		header('Connection: close');
		
		// Write the zip file out to the client.
		readfile( $zip_file );
		
		// Delete the zip file.
		unlink( $zip_file );
		
		// Remove the temporary directory and we're done!
		rmdir( $temp_dir );
	}
	
	private function _export_to_file( $format, $dir, $project, $locale, $set ) {
		// Get the entries we going to export.
		$entries = GP::$translation->for_export( $project, $set );
		
		// Get the slug for this locale.
		$locale_slug = $set->locale;
		
		// Get the locale object by the slug.
		$locale = GP_Locales::by_slug( $locale_slug );
		
		// Apply any filters that other plugins may have implemented.
		$export_locale = apply_filters( 'export_locale', $locale->slug, $locale );
		
		// Create the default file name.
		$filename = sprintf( '%s-%s.'.$format, str_replace( '/', '-', $project->path ), $export_locale );

		// Apply any filters that other plugins may have implemented to the filename.
		$filename = apply_filters( 'export_filename', $filename, $project->path, $set->slug, $export_locale, $format );

		// Get the format object to create the export with.
		$format = gp_array_get( GP::$formats, gp_get( 'format', $format ), null );

		// Get the contents from the formatter.
		$contents = $format->print_exported_file( $project, $locale, $set, $entries );

		// Write the contents out to the file.
		$fh = fopen( $dir . '/' . $filename, 'w' );
		fwrite( $fh, $contents );
		fclose( $fh );
		
		// Return the filename for future reference.
		return $filename;
	}
	
	public function after_request() {
	}

}

GP::$plugins->gp_bulk_download_translations = new GP_Bulk_Download_Translations;