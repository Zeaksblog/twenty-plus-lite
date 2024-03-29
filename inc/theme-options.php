<?php
/**
 * Twelve Plus Options Theme Options
 * Based off Twenty Eleven theme
 */

/**
 * Properly enqueue styles and scripts for our theme options page.
 *
 * This function is attached to the admin_enqueue_scripts action hook.
 *
 *
 */
function tto_admin_enqueue_scripts( $hook_suffix ) {

	wp_enqueue_style( 'tto-theme-options', get_stylesheet_directory_uri() . '/inc/theme-options.css', false, '2011-04-28' );
	wp_enqueue_script( 'tto-theme-options', get_stylesheet_directory_uri() . '/inc/js/theme-options.js', array( 'farbtastic' ), '2011-06-10' );
	wp_enqueue_style( 'farbtastic' );
}
add_action( 'admin_print_styles-appearance_page_theme_options', 'tto_admin_enqueue_scripts' );



/**
 * Register the form setting for our tto_options array.
 *
 * This function is attached to the admin_init action hook.
 *
 * This call to register_setting() registers a validation callback, tto_theme_options_validate(),
 * which is used when the option is saved, to ensure that our option values are complete, properly
 * formatted, and safe.
 *
 */
function tto_theme_options_init() {

	register_setting(
		'tto_options',       // Options group, see settings_fields() call in tto_theme_options_render_page()
		'tto_theme_options', // Database option, see tto_get_theme_options()
		'tto_theme_options_validate' // The sanitization callback, see tto_theme_options_validate()
	);

	// Register our settings field group
	add_settings_section(
		'general', // Unique identifier for the settings section
		'', // Section title (we don't want one)
		'__return_false', // Section callback (we don't want anything)
		'theme_options' // Menu slug, used to uniquely identify the page; see tto_theme_options_add_page()
	);

	// Register our individual settings fields
	add_settings_field(
		'color_scheme',  // Unique identifier for the field for this section
		__( 'Color Scheme', 'tto' ), // Setting field label
		'tto_settings_field_color_scheme', // Function that renders the settings field
		'theme_options', // Menu slug, used to uniquely identify the page; see tto_theme_options_add_page()
		'general' // Settings section. Same as the first argument in the add_settings_section() above
	);

	add_settings_field( 'link_color', __( 'Link Color',     'tto' ), 'tto_settings_field_link_color', 'theme_options', 'general' );
	add_settings_field( 'layout',     __( 'Choose Layout', 'tto' ), 'tto_settings_field_layout',     'theme_options', 'general' );
	// Post Layout Switche
	add_settings_field( 'post_switch', __( 'Select Post Layout', 'tto' ), 'tto_settings_field_post_switch', 'theme_options', 'general' );

}
add_action( 'admin_init', 'tto_theme_options_init' );

/**
 * Change the capability required to save the 'tto_options' options group.
 *
 * @see tto_theme_options_init() First parameter to register_setting() is the name of the options group.
 * @see tto_theme_options_add_page() The edit_theme_options capability is used for viewing the page.
 *
 * By default, the options groups for all registered settings require the manage_options capability.
 * This filter is required to change our theme options page to edit_theme_options instead.
 * By default, only administrators have either of these capabilities, but the desire here is
 * to allow for finer-grained control for roles and users.
 *
 * @param string $capability The capability used for the page, which is manage_options by default.
 * @return string The capability to actually use.
 */
function tto_option_page_capability( $capability ) {
	return 'edit_theme_options';
}
add_filter( 'option_page_capability_tto_options', 'tto_option_page_capability' );

/**
 * Add our theme options page to the admin menu, including some help documentation.
 *
 * This function is attached to the admin_menu action hook.
 *
 */
function tto_theme_options_add_page() {
	$theme_page = add_theme_page(
		__( 'Theme Options', 'tto' ),   // Name of page
		__( 'Theme Options', 'tto' ),   // Label in menu
		'edit_theme_options',                    // Capability required
		'theme_options',                         // Menu slug, used to uniquely identify the page
		'tto_theme_options_render_page' // Function that renders the options page
	);

	if ( ! $theme_page )
		return;

	add_action( "load-$theme_page", 'tto_theme_options_help' );
}
add_action( 'admin_menu', 'tto_theme_options_add_page' );

function tto_theme_options_help() {

	$help = '<p>' . __( 'Some themes provide customization options that are grouped together on a Theme Options screen. If you change themes, options may change or disappear, as they are theme-specific. Your current theme, Twelve Plus Options, provides the following Theme Options:', 'tto' ) . '</p>' .
			'<ol>' .
				'<li>' . __( '<strong>Color Scheme</strong>: You can choose a color palette of "Light" (light background with dark text) or "Dark" (dark background with light text) for your site.', 'tto' ) . '</li>' .
				'<li>' . __( '<strong>Link Color</strong>: You can choose the color used for text links on your site. You can enter the HTML color or hex code, or you can choose visually by clicking the "Select a Color" button to pick from a color wheel.', 'tto' ) . '</li>' .
				'<li>' . __( '<strong>Default Layout</strong>: You can choose if you want your site&#8217;s default layout to have a sidebar on the left, the right, or left and right.', 'tto' ) . '</li>' .
			'</ol>' .
			'<p>' . __( 'Remember to click "Save Changes" to save any changes you have made to the theme options.', 'tto' ) . '</p>';

	$sidebar = '<p><strong>' . __( 'For more information:', 'tto' ) . '</strong></p>' .
		'<p>' . __( '<a href="http://codex.wordpress.org/Appearance_Theme_Options_Screen" target="_blank">Documentation on Theme Options</a>', 'tto' ) . '</p>' .
		'<p>' . __( '<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>', 'tto' ) . '</p>';

	$screen = get_current_screen();

	if ( method_exists( $screen, 'add_help_tab' ) ) {
		// WordPress 3.3
		$screen->add_help_tab( array(
			'title' => __( 'Overview', 'tto' ),
			'id' => 'theme-options-help',
			'content' => $help,
			)
		);

		$screen->set_help_sidebar( $sidebar );
	} else {
		// WordPress 3.2
		add_contextual_help( $screen, $help . $sidebar );
	}
}

/**
 * Returns an array of color schemes registered for Twelve Plus Options.
 *
 */
function tto_color_schemes() {
	$color_scheme_options = array(
		'light' => array(
			'value' => 'light',
			'label' => __( 'Light', 'tto' ),
			'thumbnail' => get_stylesheet_directory_uri() . '/inc/images/light.png',
			'default_link_color' => '#1b8be0',
		),
		'dark' => array(
			'value' => 'dark',
			'label' => __( 'Dark', 'tto' ),
			'thumbnail' => get_stylesheet_directory_uri() . '/inc/images/dark.png',
			'default_link_color' => '#1FA7E4',
		),
	);

	return apply_filters( 'tto_color_schemes', $color_scheme_options );
}

/**
 * Returns an array of layout options registered for Twelve Plus Options.
 *
 */
function tto_layouts() {
	$layout_options = array(
		'content-sidebar' => array(
			'value' => 'content-sidebar',
			'label' => __( 'Right Sidebar', 'tto' ),
			'thumbnail' => get_stylesheet_directory_uri() . '/inc/images/content-sidebar.png',
		),
		'sidebar-content' => array(
			'value' => 'sidebar-content',
			'label' => __( 'Left Sidebar', 'tto' ),
			'thumbnail' => get_stylesheet_directory_uri() . '/inc/images/sidebar-content.png',
		),
	);

	return apply_filters( 'tto_layouts', $layout_options );
}

/**
 * Returns the default options for Twelve Plus Options.
 *
 */
function tto_get_default_theme_options() {
	$default_theme_options = array(
		'color_scheme' => 'light',
		'link_color'   => tto_get_default_link_color( 'light' ),
		'theme_layout' => 'content-sidebar',
		'post_switch' => '0'
	);

	if ( is_rtl() )
 		$default_theme_options['theme_layout'] = 'sidebar-content';

	return apply_filters( 'tto_default_theme_options', $default_theme_options );
}

/**
 * Returns the default link color for Twelve Plus Options, based on color scheme.
 *
 *
 * @param $string $color_scheme Color scheme. Defaults to the active color scheme.
 * @return $string Color.
*/
function tto_get_default_link_color( $color_scheme = null ) {
	if ( null === $color_scheme ) {
		$options = tto_get_theme_options();
		$color_scheme = $options['color_scheme'];
	}

	$color_schemes = tto_color_schemes();
	if ( ! isset( $color_schemes[ $color_scheme ] ) )
		return false;

	return $color_schemes[ $color_scheme ]['default_link_color'];
}

/**
 * Returns the options array for Twelve Plus Options.
 *
 */
function tto_get_theme_options() {
	return get_option( 'tto_theme_options', tto_get_default_theme_options() );
}

/**
 * Renders the Color Scheme setting field.
 *
 */
function tto_settings_field_color_scheme() {
	$options = tto_get_theme_options();

	foreach ( tto_color_schemes() as $scheme ) {
	?>
	<div class="layout image-radio-option color-scheme">
	<label class="description">
		<input type="radio" name="tto_theme_options[color_scheme]" value="<?php echo esc_attr( $scheme['value'] ); ?>" <?php checked( $options['color_scheme'], $scheme['value'] ); ?> />
		<input type="hidden" id="default-color-<?php echo esc_attr( $scheme['value'] ); ?>" value="<?php echo esc_attr( $scheme['default_link_color'] ); ?>" />
		<span>
			<img src="<?php echo esc_url( $scheme['thumbnail'] ); ?>" width="136" height="122" alt="" />
			<?php echo $scheme['label']; ?>
		</span>
	</label>
	</div>
	<?php
	}
}

/**
 * Renders the Link Color setting field.
 *
 */
function tto_settings_field_link_color() {
	$options = tto_get_theme_options();
	?>
	<input type="text" name="tto_theme_options[link_color]" id="link-color" value="<?php echo esc_attr( $options['link_color'] ); ?>" />
	<a href="#" class="pickcolor hide-if-no-js" id="link-color-example"></a>
	<input type="button" class="pickcolor button hide-if-no-js" value="<?php esc_attr_e( 'Select a Color', 'tto' ); ?>" />
	<div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
	<br />
	<span><?php printf( __( 'Default color: %s', 'tto' ), '<span id="default-color">' . tto_get_default_link_color( $options['color_scheme'] ) . '</span>' ); ?></span>
	<?php
}
/* Excerpts and Full Posts switch */
function tto_settings_field_post_switch() {
	$settings = tto_get_theme_options();
	?>

	<select name="tto_theme_options[post_switch]" style="width: 100px;">
		<option value="1" <?php if ($settings['post_switch'] =='1'){echo 'selected';} ?>>Excerpts</option>
		<option value="0" <?php if ($settings['post_switch'] =='0'){echo 'selected';} ?>>Full Posts</option>
	</select> <i>Select to show excerpts with small thumbnail or full posts</li>
	
	
	<tbody>		
		<div class="options-box note">
				Like this theme? Upgrade to the Pro version <a href="http://wp.me/pIhT9-24G" title="Twelve Plus Theme - Pro Version" target="_blank">Twelve Plus Pro</a>. The Pro version features Nivo Slider, 3 Column Layouts, Custom CSS area a Grid Style page template and more!
		</div>
	</tbody>
	
	
	<?php
}

/**
 * Renders the Layout setting field.
 *
 */
function tto_settings_field_layout() {
	$options = tto_get_theme_options();
	foreach ( tto_layouts() as $layout ) {
		?>
		<div class="layout image-radio-option theme-layout">
		<label class="description">
			<input type="radio" name="tto_theme_options[theme_layout]" value="<?php echo esc_attr( $layout['value'] ); ?>" <?php checked( $options['theme_layout'], $layout['value'] ); ?> />
			<span>
				<img src="<?php echo esc_url( $layout['thumbnail'] ); ?>" width="116" height="104" alt="" />
				<?php echo $layout['label']; ?>
			</span>
		</label>
		</div>
		<?php
	}
}

/**
 * Returns the options array for Twelve Plus Options.
 *
 */
function tto_theme_options_render_page() {
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<?php $theme_name = function_exists( 'wp_get_theme' ) ? wp_get_theme() : get_current_theme(); ?>
		<h2><?php printf( __( '%s Theme Options', 'tto' ), $theme_name ); ?></h2>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php
				settings_fields( 'tto_options' );
				do_settings_sections( 'theme_options' );
			?>

			
			<?php	
				submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Sanitize and validate form input. Accepts an array, return a sanitized array.
 *
 * @see tto_theme_options_init()
 * @todo set up Reset Options action
 *
 */
function tto_theme_options_validate( $input ) {
	$output = $defaults = tto_get_default_theme_options();

	// Color scheme must be in our array of color scheme options
	if ( isset( $input['color_scheme'] ) && array_key_exists( $input['color_scheme'], tto_color_schemes() ) )
		$output['color_scheme'] = $input['color_scheme'];

	// Our defaults for the link color may have changed, based on the color scheme.
	$output['link_color'] = $defaults['link_color'] = tto_get_default_link_color( $output['color_scheme'] );

	// Link color must be 3 or 6 hexadecimal characters
	if ( isset( $input['link_color'] ) && preg_match( '/^#?([a-f0-9]{3}){1,2}$/i', $input['link_color'] ) )
		$output['link_color'] = '#' . strtolower( ltrim( $input['link_color'], '#' ) );

	// Theme layout must be in our array of theme layout options
	if ( isset( $input['theme_layout'] ) && array_key_exists( $input['theme_layout'], tto_layouts() ) )
		$output['theme_layout'] = $input['theme_layout'];
	// Posts switch
	if (isset($input['post_switch'])) {
		$output['post_switch'] = $input['post_switch'];
	}
	return apply_filters( 'tto_theme_options_validate', $output, $input, $defaults );
}

/**
 * Enqueue the styles for the current color scheme.
 * upped the priority to make sure it verrides the parent
 */
function tto_enqueue_color_scheme() {
	$options = tto_get_theme_options();
	$color_scheme = $options['color_scheme'];

	if ( 'dark' == $color_scheme )
		wp_enqueue_style( 'dark', get_stylesheet_directory_uri() . '/colors/dark.css', array(), null );

	do_action( 'tto_enqueue_color_scheme', $color_scheme );
}
add_action( 'wp_enqueue_scripts', 'tto_enqueue_color_scheme', 11 );

/**
 * Add a style block to the theme for the current link color.
 *
 * This function is attached to the wp_head action hook.
 *
 */
function tto_print_link_color_style() {
	$options = tto_get_theme_options();
	$link_color = $options['link_color'];

	$default_options = tto_get_default_theme_options();

	// Don't do anything if the current link color is the default.
	if ( $default_options['link_color'] == $link_color )
		return;
?>
	<style>
		/* Link color */
		a:hover, a,
		.comments-link a, .entry-meta a,
		span.bbp-admin-links a,
		.site-header h1 a:hover, .site-header h2 a:hover,	
		.widget-area .widget li a:hover, .widget-area .widget a:hover,
		.entry-title a:focus,
		.entry-header .entry-title a,
		.comments-link a:hover,
		.entry-meta a:hover,
		a.comment-reply-link,
		.comments-area article header cite a:hover,
		.edit-link a,
		.template-front-page .widget-area .widget li a:hover,
		li.bypostauthor cite span,
		footer[role="contentinfo"] a:hover,
		.format-status .entry-header header a,
		.bbp-reply-content #subscription-toggle a:hover,
		.bbp-reply-content #favorite-toggle a:hover,
		.bbp-admin-links a:hover {
			color: <?php echo $link_color; ?>;
		}
		.entry-content a, .entry-content a:active { color: <?php echo $link_color; ?>; }
	</style>
<?php
}
add_action( 'wp_head', 'tto_print_link_color_style' );

/**
 * Adds Twelve Plus Options layout classes to the array of body classes.
 *
 */
function tto_layout_classes( $existing_classes ) {
	$options = tto_get_theme_options();
	$current_layout = $options['theme_layout'];

	if ( in_array( $current_layout, array( 'content-sidebar', 'sidebar-content' ) ) )
		$classes = array( 'two-column' );
	else
		$classes = array( 'three-column' );

	if ( 'content-sidebar' == $current_layout )
		$classes[] = 'right-sidebar';

	elseif ( 'sidebar-content' == $current_layout )
		$classes[] = 'left-sidebar';

	else
		$classes[] = $current_layout;

	$classes = apply_filters( 'tto_layout_classes', $classes, $current_layout );

	return array_merge( $existing_classes, $classes );
}
add_filter( 'body_class', 'tto_layout_classes' );

/**
 * Implements Twelve Plus Options theme options into Theme Customizer
 *
 * @param $wp_customize Theme Customizer object
 * @return void
 *
 */
function tto_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$options  = tto_get_theme_options();
	$defaults = tto_get_default_theme_options();

	$wp_customize->add_setting( 'tto_theme_options[color_scheme]', array(
		'default'    => $defaults['color_scheme'],
		'type'       => 'option',
		'capability' => 'edit_theme_options',
	) );

	$schemes = tto_color_schemes();
	$choices = array();
	foreach ( $schemes as $scheme ) {
		$choices[ $scheme['value'] ] = $scheme['label'];
	}

	$wp_customize->add_control( 'tto_color_scheme', array(
		'label'    => __( 'Color Scheme', 'tto' ),
		'section'  => 'colors',
		'settings' => 'tto_theme_options[color_scheme]',
		'type'     => 'radio',
		'choices'  => $choices,
		'priority' => 5,
	) );

	// Link Color (added to Color Scheme section in Theme Customizer)
	$wp_customize->add_setting( 'tto_theme_options[link_color]', array(
		'default'           => tto_get_default_link_color( $options['color_scheme'] ),
		'type'              => 'option',
		'sanitize_callback' => 'sanitize_hex_color',
		'capability'        => 'edit_theme_options',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'link_color', array(
		'label'    => __( 'Link Color', 'tto' ),
		'section'  => 'colors',
		'settings' => 'tto_theme_options[link_color]',
	) ) );

	// Default Layout
	$wp_customize->add_section( 'tto_layout', array(
		'title'    => __( 'Layout', 'tto' ),
		'priority' => 50,
	) );

	$wp_customize->add_setting( 'tto_theme_options[theme_layout]', array(
		'type'              => 'option',
		'default'           => $defaults['theme_layout'],
		'sanitize_callback' => 'sanitize_key',
	) );

	$layouts = tto_layouts();
	$choices = array();
	foreach ( $layouts as $layout ) {
		$choices[$layout['value']] = $layout['label'];
	}

	$wp_customize->add_control( 'tto_theme_options[theme_layout]', array(
		'section'    => 'tto_layout',
		'type'       => 'radio',
		'choices'    => $choices,
	) );
}
add_action( 'customize_register', 'tto_customize_register' );

/**
 * Bind JS handlers to make Theme Customizer preview reload changes asynchronously.
 * Used with blogname and blogdescription.
 *
 */
function tto_customize_preview_js() {
	wp_enqueue_script( 'tto-customizer', get_stylesheet_directory_uri() . '/inc/js/theme-customizer.js', array( 'customize-preview' ), '20120523', true );
}
add_action( 'customize_preview_init', 'tto_customize_preview_js' );