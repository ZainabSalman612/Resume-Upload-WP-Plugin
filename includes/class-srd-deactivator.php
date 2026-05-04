<?php
/**
 * Fired during plugin deactivation.
 */

class SRD_Deactivator {

	public static function deactivate() {
		flush_rewrite_rules();
	}

}
