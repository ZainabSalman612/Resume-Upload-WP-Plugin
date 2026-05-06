<?php
/**
 * Handles the AJAX file upload logic.
 */

class SRD_Ajax {

	public function init() {
		add_action( 'wp_ajax_nopriv_srd_upload_resume', array( $this, 'handle_upload' ) );
		add_action( 'wp_ajax_srd_upload_resume', array( $this, 'handle_upload' ) );
	}

	public function handle_upload() {
		// 1. Verify Nonce
		if ( ! isset( $_POST['srd_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['srd_nonce'] ) ), 'srd_upload_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ) );
		}

		// 2. Check if file is uploaded
		if ( empty( $_FILES['resume_file'] ) || $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( array( 'message' => 'No file uploaded or upload error occurred.' ) );
		}

		$file = $_FILES['resume_file'];

		// 3. File size validation (5MB max)
		$max_size = 5 * 1024 * 1024; // 5 MB in bytes
		if ( $file['size'] > $max_size ) {
			wp_send_json_error( array( 'message' => 'File size exceeds the 5MB limit.' ) );
		}

		// 4. File type validation
		$allowed_mime_types = array(
			'application/pdf',
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);
		$file_info = wp_check_filetype( $file['name'] );
		$mime_type = $file_info['type'];

		if ( ! in_array( $mime_type, $allowed_mime_types ) ) {
			wp_send_json_error( array( 'message' => 'Invalid file type. Only PDF, DOC, and DOCX are allowed.' ) );
		}

		// 5. Setup upload directory
		$upload_dir = wp_upload_dir();
		$custom_dir = $upload_dir['basedir'] . '/resume-submissions';

		if ( ! file_exists( $custom_dir ) ) {
			wp_mkdir_p( $custom_dir );
		}

		// 6. Sanitize and prepare unique filename
		$original_filename = sanitize_file_name( wp_basename( $file['name'] ) );
		// Ensure uniqueness to prevent overwrites
		$filename = wp_unique_filename( $custom_dir, $original_filename );
		$file_path = $custom_dir . '/' . $filename;

		// 7. Move uploaded file
		if ( ! move_uploaded_file( $file['tmp_name'], $file_path ) ) {
			wp_send_json_error( array( 'message' => 'Failed to save the file to the server.' ) );
		}

		// 8. Save to Database
		global $wpdb;
		$table_name = $wpdb->prefix . 'srd_resume_drops';

		$inserted = $wpdb->insert(
			$table_name,
			array(
				'file_name' => $original_filename, // Keep original sanitized name for display
				'file_path' => $file_path,
			),
			array(
				'%s',
				'%s',
			)
		);

		if ( ! $inserted ) {
			// If DB insert fails, remove the uploaded file to keep things clean
			@unlink( $file_path );
			wp_send_json_error( array( 'message' => 'Failed to save record to the database.' ) );
		}

		// Send email notification if configured
		$notification_email = get_option( 'srd_notification_email' );
		if ( ! empty( $notification_email ) && is_email( $notification_email ) ) {
			$to          = $notification_email;
			$subject     = 'New Submission from NextHire Solutions';
			$message     = 'A new resume has been submitted. Please find the resume attached.';
			
			// Set Content-Type
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			
			$attachments = array( $file_path );
			
			// Safely set the sender name to avoid SMTP header conflicts
			$custom_name_filter = function() {
				return 'NextHire Solutions';
			};
			add_filter( 'wp_mail_from_name', $custom_name_filter );
			
			wp_mail( $to, $subject, $message, $headers, $attachments );
			
			remove_filter( 'wp_mail_from_name', $custom_name_filter );
		}

		// 9. Success Response
		wp_send_json_success( array( 'message' => 'Your resume has been submitted successfully.' ) );
	}
}
