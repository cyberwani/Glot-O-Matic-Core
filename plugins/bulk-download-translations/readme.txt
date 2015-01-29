Bulk Download Translations is a plugin for GlotPress that will download all the translation sets (in PO format) of a project in a zip file at once.

This plugin requires that the PHP ZipArchive Class exists in your PHP installation.

To enable this plugin, add the following line to your gp-config.php file:

	define( 'GP_BULK_DOWNLOAD_TRANSLATIONS', true );
	
If you wish to include the MO files as well, add the following line to your gp-config.php file:

	define( 'GP_BULK_DOWNLOAD_TRANSLATIONS_MO', true );
	
You can also define the temporary directory to use by adding the following line to your gp-config.php file:

	define( 'GP_BULK_DOWNLOAD_TRANSLATIONS_TEMP_DIR', 'c:/temp' );
	
Note: Do not include a trailing slash.