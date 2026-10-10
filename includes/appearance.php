<?php
/** Supported appearance overrides, validation, and administrator controls. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function plugin_template_clerk_variable_fields() {
	return array(
		'colorPrimary'           => array( 'label' => __( 'Primary color', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#6c47ff' ),
		'colorPrimaryForeground' => array( 'label' => __( 'Text on primary color', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#ffffff' ),
		'colorBackground'        => array( 'label' => __( 'Card background', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#ffffff' ),
		'colorForeground'        => array( 'label' => __( 'Text color', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#212126' ),
		'colorMutedForeground'   => array( 'label' => __( 'Secondary text color', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#747686' ),
		'colorInput'             => array( 'label' => __( 'Input background', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#ffffff' ),
		'colorInputForeground'   => array( 'label' => __( 'Input text color', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#212126' ),
		'colorBorder'            => array( 'label' => __( 'Border color', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#d9d9de' ),
		'colorDanger'            => array( 'label' => __( 'Error color', 'visitor-components-for-clerk' ), 'type' => 'color', 'example' => '#b42318' ),
		'fontFamily'             => array( 'label' => __( 'Font family', 'visitor-components-for-clerk' ), 'type' => 'font', 'example' => 'Arial, sans-serif' ),
		'fontSize'               => array( 'label' => __( 'Font size', 'visitor-components-for-clerk' ), 'type' => 'length', 'example' => '0.875rem' ),
		'borderRadius'           => array( 'label' => __( 'Border radius', 'visitor-components-for-clerk' ), 'type' => 'length', 'example' => '0.375rem' ),
		'spacing'                => array( 'label' => __( 'Spacing', 'visitor-components-for-clerk' ), 'type' => 'length', 'example' => '1rem' ),
	);
}

/** Return a valid override, an empty inherited value, or null for invalid input. */
function plugin_template_clerk_validate_variable( $name, $value ) {
	$fields = plugin_template_clerk_variable_fields();
	if ( ! isset( $fields[ $name ] ) || ! is_string( $value ) || strlen( $value ) > 256 ) {
		return null;
	}
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	if ( 'color' === $fields[ $name ]['type'] ) {
		return sanitize_hex_color( $value );
	}
	if ( 'font' === $fields[ $name ]['type'] ) {
		$family = '(?:[\p{L}_][\p{L}\p{N}_ -]*|"[\p{L}\p{N}_ -]+"|\'[\p{L}\p{N}_ -]+\')';
		return preg_match( '/^' . $family . '(?:\s*,\s*' . $family . ')*$/uD', $value ) ? $value : null;
	}
	if ( '0' === $value && 'fontSize' !== $name ) {
		return $value;
	}
	if ( ! preg_match( '/^(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em)$/D', $value ) || ( 'fontSize' === $name && (float) $value <= 0 ) ) {
		return null;
	}
	return $value;
}

/** Export only supported, valid, non-empty overrides, even for corrupt saved data. */
function plugin_template_clerk_variables() {
	$saved = get_option( 'plugin_template_clerk_variables', array() );
	$variables = array();
	if ( ! is_array( $saved ) ) {
		return $variables;
	}
	foreach ( plugin_template_clerk_variable_fields() as $name => $field ) {
		$value = plugin_template_clerk_validate_variable( $name, $saved[ $name ] ?? '' );
		if ( null !== $value && '' !== $value ) {
			$variables[ $name ] = $value;
		}
	}
	return $variables;
}

function plugin_template_clerk_sanitize_variables( $input ) {
	$previous = plugin_template_clerk_variables();
	if ( ! current_user_can( 'manage_options' ) ) {
		return $previous;
	}
	// options.php has already checked the Settings API nonce before sanitization.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Settings API options.php verifies the nonce before these callbacks.
	if ( isset( $_POST['plugin_template_clerk_reset_variables'] ) && '1' === $_POST['plugin_template_clerk_reset_variables'] ) {
		return array();
	}
	if ( ! is_array( $input ) ) {
		add_settings_error( 'plugin_template_clerk_variables', 'invalid_variables', __( 'Appearance settings could not be saved. The previous overrides were preserved.', 'visitor-components-for-clerk' ) );
		return $previous;
	}
	$variables = array();
	foreach ( plugin_template_clerk_variable_fields() as $name => $field ) {
		$value = plugin_template_clerk_validate_variable( $name, $input[ $name ] ?? '' );
		if ( null === $value ) {
			if ( isset( $previous[ $name ] ) ) {
				$variables[ $name ] = $previous[ $name ];
			}
			/* translators: %s: appearance setting label. */
			add_settings_error( 'plugin_template_clerk_variables', 'invalid_' . $name, sprintf( __( 'Invalid value for %s. Its previous override was preserved.', 'visitor-components-for-clerk' ), $field['label'] ) );
		} elseif ( '' !== $value ) {
			$variables[ $name ] = $value;
		}
	}
	return $variables;
}

function plugin_template_clerk_appearance_fields() {
	$variables = plugin_template_clerk_variables();
	?>
	<details class="clerk-settings-section" aria-labelledby="clerk-appearance-title">
	<summary><h2 id="clerk-appearance-title"><?php esc_html_e( 'Appearance', 'visitor-components-for-clerk' ); ?></h2></summary>
	<p><?php esc_html_e( 'Override the selected theme for all Clerk components and dialogs. Leave fields blank to inherit theme defaults. Fonts must already be available on your site; this setting does not load fonts.', 'visitor-components-for-clerk' ); ?></p>
	<?php foreach ( array( 'colors' => __( 'Colors', 'visitor-components-for-clerk' ), 'sizing' => __( 'Typography and sizing', 'visitor-components-for-clerk' ) ) as $group => $title ) : ?>
		<details class="clerk-settings-group" <?php echo 'colors' === $group ? 'open' : ''; ?>><summary><strong><?php echo esc_html( $title ); ?></strong></summary>
		<table class="form-table" role="presentation">
		<?php foreach ( plugin_template_clerk_variable_fields() as $name => $field ) : ?>
			<?php if ( ( 'colors' === $group ) !== ( 'color' === $field['type'] ) ) { continue; } ?>
			<tr><th scope="row"><label for="clerk-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
				<td><input id="clerk-<?php echo esc_attr( $name ); ?>" name="plugin_template_clerk_variables[<?php echo esc_attr( $name ); ?>]" type="text" class="<?php echo 'color' === $field['type'] ? 'clerk-color-field' : 'regular-text'; ?>" value="<?php echo esc_attr( $variables[ $name ] ?? '' ); ?>" placeholder="<?php echo esc_attr( $field['example'] ); ?>" aria-describedby="clerk-help-<?php echo esc_attr( $name ); ?>" />
				<p id="clerk-help-<?php echo esc_attr( $name ); ?>" class="description">
					<?php if ( 'color' === $field['type'] ) : ?>
						<?php esc_html_e( 'Choose a color or enter a 3- or 6-digit hex value. Clear to inherit.', 'visitor-components-for-clerk' ); ?>
					<?php elseif ( 'font' === $field['type'] ) : ?>
						<?php esc_html_e( 'Enter a font family or comma-separated fallback list. Blank inherits.', 'visitor-components-for-clerk' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Use px, rem, or em. Font size must be positive; radius and spacing also accept 0. Blank inherits.', 'visitor-components-for-clerk' ); ?>
					<?php endif; ?>
					<code><?php echo esc_html( $name ); ?></code>
				</p></td>
			</tr>
		<?php endforeach; ?>
		</table></details>
	<?php endforeach; ?>
	<p><button type="submit" class="button" name="plugin_template_clerk_reset_variables" value="1"><?php esc_html_e( 'Reset appearance overrides', 'visitor-components-for-clerk' ); ?></button></p>
	</details>
	<?php
}

function plugin_template_clerk_option_fields() {
	return array(
		'socialButtonsPlacement' => array( 'label' => __( 'Social button placement', 'visitor-components-for-clerk' ), 'type' => 'select', 'choices' => array( 'top' => __( 'Top', 'visitor-components-for-clerk' ), 'bottom' => __( 'Bottom', 'visitor-components-for-clerk' ) ) ),
		'socialButtonsVariant'   => array( 'label' => __( 'Social button style', 'visitor-components-for-clerk' ), 'type' => 'select', 'choices' => array( 'auto' => __( 'Automatic', 'visitor-components-for-clerk' ), 'blockButton' => __( 'Full-width buttons', 'visitor-components-for-clerk' ), 'iconButton' => __( 'Icon buttons', 'visitor-components-for-clerk' ) ) ),
		'logoPlacement'          => array( 'label' => __( 'Logo placement', 'visitor-components-for-clerk' ), 'type' => 'select', 'choices' => array( 'inside' => __( 'Inside card', 'visitor-components-for-clerk' ), 'outside' => __( 'Outside card', 'visitor-components-for-clerk' ) ) ),
		'elevation'              => array( 'label' => __( 'Card elevation', 'visitor-components-for-clerk' ), 'type' => 'select', 'choices' => array( 'raised' => __( 'Raised', 'visitor-components-for-clerk' ), 'flush' => __( 'Flush', 'visitor-components-for-clerk' ) ) ),
		'animations'             => array( 'label' => __( 'Animations', 'visitor-components-for-clerk' ), 'type' => 'boolean' ),
		'autoFocus'              => array( 'label' => __( 'Automatic input focus', 'visitor-components-for-clerk' ), 'type' => 'boolean' ),
		'shimmer'                => array( 'label' => __( 'Avatar loading shimmer', 'visitor-components-for-clerk' ), 'type' => 'boolean' ),
		'showOptionalFields'     => array( 'label' => __( 'Show optional fields', 'visitor-components-for-clerk' ), 'type' => 'boolean' ),
		'helpPageUrl'            => array( 'label' => __( 'Help page URL', 'visitor-components-for-clerk' ), 'type' => 'url' ),
		'termsPageUrl'           => array( 'label' => __( 'Terms page URL', 'visitor-components-for-clerk' ), 'type' => 'url' ),
		'privacyPageUrl'         => array( 'label' => __( 'Privacy page URL', 'visitor-components-for-clerk' ), 'type' => 'url' ),
		'logoImageUrl'           => array( 'label' => __( 'Logo image URL', 'visitor-components-for-clerk' ), 'type' => 'url' ),
		'logoLinkUrl'            => array( 'label' => __( 'Logo link URL', 'visitor-components-for-clerk' ), 'type' => 'url' ),
	);
}

/** Return an override (including false), an empty inherited value, or null. */
function plugin_template_clerk_validate_option( $name, $value ) {
	$fields = plugin_template_clerk_option_fields();
	if ( ! isset( $fields[ $name ] ) ) {
		return null;
	}
	$type = $fields[ $name ]['type'];
	if ( 'boolean' === $type && is_bool( $value ) ) {
		return $value;
	}
	if ( ! is_string( $value ) || strlen( $value ) > 2048 ) {
		return null;
	}
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	if ( 'boolean' === $type ) {
		return '1' === $value ? true : ( '0' === $value ? false : null );
	}
	if ( 'select' === $type ) {
		return isset( $fields[ $name ]['choices'][ $value ] ) ? $value : null;
	}
	return plugin_template_clerk_validate_url( $value );
}

function plugin_template_clerk_options() {
	$saved = get_option( 'plugin_template_clerk_options', array() );
	$options = array();
	if ( ! is_array( $saved ) ) {
		return $options;
	}
	foreach ( plugin_template_clerk_option_fields() as $name => $field ) {
		$value = plugin_template_clerk_validate_option( $name, $saved[ $name ] ?? '' );
		if ( null !== $value && '' !== $value ) {
			$options[ $name ] = $value;
		}
	}
	return $options;
}

function plugin_template_clerk_sanitize_options( $input ) {
	$previous = plugin_template_clerk_options();
	if ( ! current_user_can( 'manage_options' ) ) {
		return $previous;
	}
	// options.php has already checked the Settings API nonce before sanitization.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Settings API options.php verifies the nonce before these callbacks.
	if ( isset( $_POST['plugin_template_clerk_reset_options'] ) && '1' === $_POST['plugin_template_clerk_reset_options'] ) {
		return array();
	}
	if ( ! is_array( $input ) ) {
		add_settings_error( 'plugin_template_clerk_options', 'invalid_options', __( 'Appearance options could not be saved. The previous overrides were preserved.', 'visitor-components-for-clerk' ) );
		return $previous;
	}
	$options = array();
	foreach ( plugin_template_clerk_option_fields() as $name => $field ) {
		$value = plugin_template_clerk_validate_option( $name, array_key_exists( $name, $input ) ? $input[ $name ] : '' );
		if ( null === $value ) {
			if ( array_key_exists( $name, $previous ) ) {
				$options[ $name ] = $previous[ $name ];
			}
			/* translators: %s: appearance option label. */
			add_settings_error( 'plugin_template_clerk_options', 'invalid_' . $name, sprintf( __( 'Invalid value for %s. Its previous override was preserved.', 'visitor-components-for-clerk' ), $field['label'] ) );
		} elseif ( '' !== $value ) {
			$options[ $name ] = $value;
		}
	}
	return $options;
}

function plugin_template_clerk_options_fields( $advanced = false ) {
	$options = plugin_template_clerk_options();
	?>
	<?php if ( $advanced ) : ?>
	<details class="clerk-settings-section clerk-settings-advanced"><summary><h2><?php esc_html_e( 'Advanced', 'visitor-components-for-clerk' ); ?></h2></summary>
	<p><?php esc_html_e( 'Fine-tune component behavior. Inherit default follows Clerk’s defaults. These controls do not change WordPress login or permissions.', 'visitor-components-for-clerk' ); ?></p>
	<?php else : ?>
	<details class="clerk-settings-section" aria-labelledby="clerk-layout-title">
	<summary><h2 id="clerk-layout-title"><?php esc_html_e( 'Layout and links', 'visitor-components-for-clerk' ); ?></h2></summary>
	<p><?php esc_html_e( 'Customize how Clerk components fit your site. Inherit default or blank fields use Clerk’s theme and Dashboard settings. Flush elevation does not affect profile panels, popovers, or modals.', 'visitor-components-for-clerk' ); ?></p>
	<?php endif; ?>
	<?php $groups = $advanced ? array( 'behavior' => __( 'Behavior', 'visitor-components-for-clerk' ) ) : array( 'layout' => __( 'Component layout', 'visitor-components-for-clerk' ), 'links' => __( 'Links and logo', 'visitor-components-for-clerk' ) ); ?>
	<?php foreach ( $groups as $group => $title ) : ?>
		<?php if ( $advanced ) : ?><h3><?php echo esc_html( $title ); ?></h3><?php endif; ?>
		<?php if ( ! $advanced ) : ?><details class="clerk-settings-group" <?php echo 'layout' === $group ? 'open' : ''; ?>><summary><strong><?php echo esc_html( $title ); ?></strong></summary><?php endif; ?>
		<table class="form-table" role="presentation">
		<?php foreach ( plugin_template_clerk_option_fields() as $name => $field ) : ?>
			<?php
			$type_group = 'url' === $field['type'] ? 'links' : ( 'boolean' === $field['type'] ? 'behavior' : 'layout' );
			if ( $group !== $type_group ) { continue; }
			$value = $options[ $name ] ?? '';
			if ( is_bool( $value ) ) { $value = $value ? '1' : '0'; }
			?>
			<tr><th scope="row"><label for="clerk-option-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th><td>
			<?php if ( 'url' === $field['type'] ) : ?>
				<input id="clerk-option-<?php echo esc_attr( $name ); ?>" name="plugin_template_clerk_options[<?php echo esc_attr( $name ); ?>]" type="url" class="regular-text" value="<?php echo esc_attr( $value ); ?>" placeholder="https://example.com/" aria-describedby="clerk-option-help-<?php echo esc_attr( $name ); ?>" />
				<p class="description" id="clerk-option-help-<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Full HTTP or HTTPS URL without embedded credentials. Blank inherits.', 'visitor-components-for-clerk' ); ?></p>
			<?php else : ?>
				<select id="clerk-option-<?php echo esc_attr( $name ); ?>" name="plugin_template_clerk_options[<?php echo esc_attr( $name ); ?>]">
					<option value="" <?php selected( $value, '' ); ?>><?php esc_html_e( 'Inherit default', 'visitor-components-for-clerk' ); ?></option>
					<?php $choices = 'boolean' === $field['type'] ? array( '1' => __( 'Enabled', 'visitor-components-for-clerk' ), '0' => __( 'Disabled', 'visitor-components-for-clerk' ) ) : $field['choices']; ?>
					<?php foreach ( $choices as $choice => $label ) : ?>
						<option value="<?php echo esc_attr( $choice ); ?>" <?php selected( $value, (string) $choice ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			</td></tr>
		<?php endforeach; ?>
		</table><?php if ( ! $advanced ) : ?></details><?php endif; ?>
	<?php endforeach; ?>
	<?php if ( $advanced ) : ?>
	<?php plugin_template_clerk_provider_fields(); ?>
	<p class="description"><?php esc_html_e( 'Reset appearance options clears both layout/link overrides and advanced behavior overrides. Appearance variables, theme, and publishable key are preserved.', 'visitor-components-for-clerk' ); ?></p>
	<p><button type="submit" class="button" name="plugin_template_clerk_reset_options" value="1"><?php esc_html_e( 'Reset appearance options', 'visitor-components-for-clerk' ); ?></button></p>
	<?php endif; ?>
	</details>
	<?php
}

function plugin_template_clerk_appearance_assets( $hook ) {
	if ( 'settings_page_plugin-template-clerk' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'plugin-template-clerk-admin', plugins_url( 'assets/admin.css', dirname( __DIR__ ) . '/plugin-template.php' ), array( 'wp-color-picker' ), filemtime( dirname( __DIR__ ) . '/assets/admin.css' ) );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".clerk-color-field").wpColorPicker();});' );
}
add_action( 'admin_enqueue_scripts', 'plugin_template_clerk_appearance_assets' );
