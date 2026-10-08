<?php
/** Shared placement and rendering for Clerk components. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function plugin_template_clerk_components() {
	return array( 'sign-in', 'sign-up', 'user-button', 'user-profile', 'organization-switcher', 'organization-profile', 'pricing-table', 'organization-list', 'create-organization', 'waitlist', 'google-one-tap' );
}

/** Keep Clerk visitor blocks together in the WordPress inserter. */
function plugin_template_clerk_block_categories( $categories ) {
	$categories[] = array(
		'slug' => 'clerk',
		'title' => __( 'Clerk', 'desk-clerk' ),
	);
	return $categories;
}
add_filter( 'block_categories_all', 'plugin_template_clerk_block_categories' );

/** Share one configuration and asset owner across components and Show blocks. */
function plugin_template_clerk_enqueue() {
	$config = plugin_template_clerk_config();
	if ( ! $config ) {
		return new WP_Error( 'clerk_unconfigured', __( 'Clerk is not configured. Please contact the site administrator.', 'desk-clerk' ) );
	}
	if ( ! wp_script_is( 'clerk-component-view-script', 'registered' ) ) {
		return new WP_Error( 'clerk_assets', __( 'Clerk component assets are unavailable. Please contact the site administrator.', 'desk-clerk' ) );
	}
	wp_enqueue_script( 'clerk-component-view-script' );
	wp_enqueue_style( 'clerk-component-style' );
	static $configured = false;
	if ( ! $configured ) {
		wp_add_inline_script( 'clerk-component-view-script', 'window.pluginTemplateClerk = ' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
		$configured = true;
	}
	return $config;
}

/** Render generic cache-safe markup; identity is resolved only in the browser. */
function plugin_template_clerk_render( $attributes = array() ) {
	$component = $attributes['component'] ?? 'sign-in';
	if ( ! is_string( $component ) || ! in_array( $component, plugin_template_clerk_components(), true ) ) {
		return '';
	}
	$payer = $attributes['for'] ?? 'user';
	if ( 'pricing-table' === $component && ! in_array( $payer, array( 'user', 'organization' ), true ) ) {
		return '';
	}
	$config = plugin_template_clerk_enqueue();
	if ( is_wp_error( $config ) ) {
		return '<div class="clerk-component"><p role="status">' . esc_html( $config->get_error_message() ) . '</p></div>';
	}
	return sprintf(
		'<div id="%1$s" class="clerk-component" data-clerk-component="%2$s"%4$s><p role="status">%3$s</p></div>',
		esc_attr( wp_unique_id( 'clerk-' ) ),
		esc_attr( $component ),
		esc_html( $config['messages']['loading'] ),
		'pricing-table' === $component ? ' data-clerk-for="' . esc_attr( $payer ) . '"' : ''
	);
}

/** UI visibility only: nested HTML is public and never a server authorization gate. */
function plugin_template_clerk_render_show( $attributes, $content ) {
	$condition = $attributes['condition'] ?? 'signed-in';
	if ( ! in_array( $condition, array( 'signed-in', 'signed-out' ), true ) ) {
		return '';
	}
	$config = plugin_template_clerk_enqueue();
	$feedback = is_wp_error( $config ) ? $config->get_error_message() : $config['messages']['loading'];
	return '<div class="clerk-show-wrapper"><div class="clerk-show" data-clerk-show="' . esc_attr( $condition ) . '" hidden>' . $content . '</div><div data-clerk-show-feedback><p role="status">' . esc_html( $feedback ) . '</p></div></div>';
}

function plugin_template_clerk_shortcode( $attributes ) {
	return plugin_template_clerk_render( shortcode_atts( array( 'component' => 'sign-in', 'for' => 'user' ), $attributes, 'clerk' ) );
}

function plugin_template_clerk_register_components() {
	$build = dirname( __DIR__ ) . '/build/clerk';
	if ( file_exists( $build . '/block.json' ) ) {
		register_block_type( $build, array( 'render_callback' => 'plugin_template_clerk_render' ) );
		// Both shortcodes and dynamic blocks may render after the document head.
		wp_script_add_data( 'clerk-component-view-script', 'group', 1 );
		// Dedicated blocks share these registered asset handles and one renderer.
		foreach ( plugin_template_clerk_components() as $component ) {
			$component_build = dirname( __DIR__ ) . '/build/components/' . $component;
			if ( file_exists( $component_build . '/block.json' ) ) {
				register_block_type( $component_build, array(
					'render_callback' => static function ( $attributes ) use ( $component ) {
						$attributes['component'] = $component;
						return plugin_template_clerk_render( $attributes );
					},
				) );
			}
		}
	}
	$show_build = dirname( __DIR__ ) . '/build/show';
	if ( file_exists( $show_build . '/block.json' ) ) {
		register_block_type( $show_build, array( 'render_callback' => 'plugin_template_clerk_render_show' ) );
	}
	add_shortcode( 'clerk', 'plugin_template_clerk_shortcode' );
}
add_action( 'init', 'plugin_template_clerk_register_components' );

/** Shortcodes can render after wp_head; print their pending styles in the footer. */
function plugin_template_clerk_footer_styles() {
	if ( wp_style_is( 'clerk-component-style', 'enqueued' ) && ! wp_style_is( 'clerk-component-style', 'done' ) ) {
		wp_print_styles( array( 'clerk-component-style' ) );
	}
}
add_action( 'wp_footer', 'plugin_template_clerk_footer_styles', 5 );
