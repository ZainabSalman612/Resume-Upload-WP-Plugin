<?php
/**
 * Fired during plugin activation.
 */

class SRD_Activator {

	public static function activate() {
		self::create_database_table();
		self::create_upload_directory();
		
		// Flush rewrite rules for the custom endpoint.
		// Since the rules might not be added yet by the init hook during activation,
		// we'll rely on the public class adding them. But we call flush here as a safeguard.
		flush_rewrite_rules();
	}

	private static function create_database_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'srd_resume_drops';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			file_name varchar(255) NOT NULL,
			file_path varchar(255) NOT NULL,
			upload_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	private static function create_upload_directory() {
		$upload_dir = wp_upload_dir();
		$custom_dir = $upload_dir['basedir'] . '/resume-submissions';

		if ( ! file_exists( $custom_dir ) ) {
			wp_mkdir_p( $custom_dir );
		}

		// Create an index.php to prevent directory listing
		$index_file = $custom_dir . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
		}

		// Create .htaccess to prevent direct script execution and access if possible
		$htaccess_file = $custom_dir . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$htaccess_content = "<FilesMatch \"\.(?i:php|php[0-9]|phtml|pl|py|jsp|asp|htm|shtml|sh|cgi)$\">\n" .
			                    "    Order Allow,Deny\n" .
			                    "    Deny from all\n" .
			                    "</FilesMatch>\n";
			file_put_contents( $htaccess_file, $htaccess_content );
		}
	}
}
