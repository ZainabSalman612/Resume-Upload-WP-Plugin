<?php
/**
 * Handles the admin panel interactions.
 */

class SRD_Admin {

	public function init() {
		// Admin Menu
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		
		// Register Settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		
		// Enqueue Admin CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// Action Hooks for Download and Delete
		add_action( 'admin_post_srd_download_resume', array( $this, 'handle_download' ) );
		add_action( 'admin_post_srd_delete_resume', array( $this, 'handle_delete' ) );
	}

	public function register_settings() {
		register_setting( 'srd_settings_group', 'srd_notification_email', 'sanitize_email' );
	}

	public function enqueue_styles( $hook ) {
		if ( 'toplevel_page_srd-resume-drops' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'srd-admin-style',
			SRD_PLUGIN_URL . 'admin/css/srd-admin.css',
			array(),
			SRD_VERSION,
			'all'
		);
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			'Resume Drops',
			'Resume Drops',
			'manage_options',
			'srd-resume-drops',
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-media-document',
			30
		);

		add_submenu_page(
			'srd-resume-drops',
			'All Resumes',
			'All Resumes',
			'manage_options',
			'srd-resume-drops',
			array( $this, 'display_plugin_admin_page' )
		);

		add_submenu_page(
			'srd-resume-drops',
			'Settings',
			'Settings',
			'manage_options',
			'srd-settings',
			array( $this, 'display_settings_page' )
		);
	}

	public function display_settings_page() {
		?>
		<div class="wrap">
			<h1>Resume Drops Settings</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'srd_settings_group' ); ?>
				<?php do_settings_sections( 'srd_settings_group' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Notification Email</th>
						<td>
							<input type="email" name="srd_notification_email" value="<?php echo esc_attr( get_option('srd_notification_email') ); ?>" class="regular-text" />
							<p class="description">Enter the email address where you want to receive new resume submissions. Leave blank to disable email notifications.</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	public function display_plugin_admin_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'srd_resume_drops';
		$results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY upload_date DESC" );

		?>
		<div class="wrap srd-admin-wrap">
			<h1 class="wp-heading-inline">Resume Drops</h1>
			<hr class="wp-header-end">

			<?php
			if ( isset( $_GET['srd_msg'] ) && $_GET['srd_msg'] == 'deleted' ) {
				echo '<div class="notice notice-success is-dismissible"><p>Resume deleted successfully.</p></div>';
			}
			if ( isset( $_GET['srd_error'] ) ) {
				$err = sanitize_text_field( $_GET['srd_error'] );
				echo '<div class="notice notice-error is-dismissible"><p>Error: ' . esc_html( $err ) . '</p></div>';
			}
			?>

			<table class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-primary">File Name</th>
						<th scope="col" class="manage-column">Upload Date</th>
						<th scope="col" class="manage-column">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $results ) ) : ?>
						<?php foreach ( $results as $row ) : ?>
							<tr>
								<td class="column-primary" data-colname="File Name">
									<strong><?php echo esc_html( $row->file_name ); ?></strong>
									<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
								</td>
								<td data-colname="Upload Date">
									<?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row->upload_date ) ) ); ?>
								</td>
								<td data-colname="Actions">
									<?php
									$download_url = wp_nonce_url( admin_url( 'admin-post.php?action=srd_download_resume&id=' . $row->id ), 'srd_download_' . $row->id );
									$delete_url = wp_nonce_url( admin_url( 'admin-post.php?action=srd_delete_resume&id=' . $row->id ), 'srd_delete_' . $row->id );
									?>
									<a href="<?php echo esc_url( $download_url ); ?>" class="button button-primary srd-btn-action">Download</a>
									<a href="<?php echo esc_url( $delete_url ); ?>" class="button srd-btn-delete" onclick="return confirm('Are you sure you want to permanently delete this resume?');">Delete</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="3">No resumes have been submitted yet.</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function handle_download() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized user' );
		}

		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		if ( ! $id || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'srd_download_' . $id ) ) {
			wp_die( 'Security check failed' );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'srd_resume_drops';
		$record = $wpdb->get_row( $wpdb->prepare( "SELECT file_name, file_path FROM $table_name WHERE id = %d", $id ) );

		if ( $record && file_exists( $record->file_path ) ) {
			// Force Download
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . basename( $record->file_name ) . '"' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			header( 'Content-Length: ' . filesize( $record->file_path ) );
			flush(); // Flush system output buffer
			readfile( $record->file_path );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=srd-resume-drops&srd_error=file_not_found' ) );
			exit;
		}
	}

	public function handle_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized user' );
		}

		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		if ( ! $id || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'srd_delete_' . $id ) ) {
			wp_die( 'Security check failed' );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'srd_resume_drops';
		$record = $wpdb->get_row( $wpdb->prepare( "SELECT file_path FROM $table_name WHERE id = %d", $id ) );

		if ( $record ) {
			// Remove from Server
			if ( file_exists( $record->file_path ) ) {
				@unlink( $record->file_path );
			}
			
			// Remove from DB
			$wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );

			wp_redirect( admin_url( 'admin.php?page=srd-resume-drops&srd_msg=deleted' ) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=srd-resume-drops&srd_error=record_not_found' ) );
			exit;
		}
	}
}
