<?php
/**
 * Handles the frontend interactions and template routing.
 */

class SRD_Public {

	public function init() {
		// Register custom rewrite rules and query vars
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		
		// Intercept template loading to serve our custom page
		add_filter( 'template_include', array( $this, 'load_resume_drop_template' ) );
		
		// Enqueue scripts and styles only on our custom endpoint
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
	}

	public function add_rewrite_rules() {
		add_rewrite_rule( '^resume-drop/?$', 'index.php?srd_resume_drop=1', 'top' );
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'srd_resume_drop';
		return $vars;
	}

	public function load_resume_drop_template( $template ) {
		if ( get_query_var( 'srd_resume_drop' ) ) {
			$custom_template = SRD_PLUGIN_DIR . 'public/templates/resume-drop-page.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
		return $template;
	}

	public function enqueue_scripts_styles() {
		if ( get_query_var( 'srd_resume_drop' ) ) {
			// CSS
			wp_enqueue_style(
				'srd-public-style',
				SRD_PLUGIN_URL . 'public/css/srd-public.css',
				array(),
				SRD_VERSION,
				'all'
			);

			// JS
			wp_enqueue_script(
				'srd-public-script',
				SRD_PLUGIN_URL . 'public/js/srd-public.js',
				array(), // No dependencies, vanilla JS
				SRD_VERSION,
				true
			);

			// Localize script to pass AJAX URL and Nonce
			wp_localize_script(
				'srd-public-script',
				'srd_ajax_obj',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'srd_upload_nonce' )
				)
			);
		}
	}
}
