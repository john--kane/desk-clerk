<?php
/**
 * Plugin Name: Desk Clerk
 * Description: Drop-in Clerk visitor components for WordPress blocks and shortcodes.
 * Version: 0.2.0
 * Author: John Kane
 * Requires at least: 6.3
 * Requires PHP: 8.2
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: desk-clerk
 *
 * @package PluginTemplate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the compiled block and its WordPress-managed assets.
 */
function plugin_template_register_block() {
	// Allow activation before the first build without causing a PHP error.
	if ( file_exists( __DIR__ . '/build/block.json' ) ) {
		register_block_type( __DIR__ . '/build' );
	}
}
add_action( 'init', 'plugin_template_register_block' );

require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/components.php';
require_once __DIR__ . '/includes/editor-preview.php';
