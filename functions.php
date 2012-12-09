<?php
// Load up our theme options page and related code.
require( get_stylesheet_directory() . '/inc/theme-options.php' );

// Change header width
function tto_custom_header_setup() {
	$header_args = array( 
		'width' => 1000,
		'height' => 250
	 );
	add_theme_support( 'custom-header', $header_args );
}
add_action( 'after_setup_theme', 'tto_custom_header_setup' );

// Add a Theme Options link to Admin Bar
function theme_options_link()
{
  global $wp_admin_bar, $wpdb;
  if (!is_super_admin() || !is_admin_bar_showing())
	  return;
  $wp_admin_bar->add_menu(array('parent' => 'appearance', 'title' => __('Theme Options', 'Theme Options'), 'href' => home_url() . '/wp-admin/themes.php?page=theme_options'));
}
add_action('admin_bar_menu', 'theme_options_link', 1000);

// Change default thumbnail size
function tto_twentytwelve_setup() {
	set_post_thumbnail_size( 651, 9999 ); // (default featured images)Unlimited height, soft crop
	add_image_size( 'post-excerpt-thumbnail', 120, 120, true ); // Post excerpt Thumbnails
}
add_action( 'after_setup_theme', 'tto_twentytwelve_setup', 11 );

// Override content width (for photo and video embeds)
$content_width = 651;

function tto_content_width() {
	if ( is_page_template( 'page-templates/full-width.php' ) || is_attachment() || ! is_active_sidebar( 'sidebar-1' ) ) {
		global $content_width;
		$content_width = 1000;
	}
}
add_action( 'template_redirect', 'tto_content_width', 11 );

// Body class for custom page template and 3 column layouts
add_filter( 'body_class', 'tto_custom_body_class');
function tto_custom_body_class( $classes ) {
	 if( !is_page_template() )
          $classes[] = 'custom-layout';
     return $classes;
}

// Set default background color for color schemes
add_action('after_setup_theme','child_default_background_color');
	function child_default_background_color() {
	$theme_options = get_option( 'tto_theme_options');
	
	if ( 'dark' == $theme_options['color_scheme'] )
		$default_background_color = '181818';
	else
		$default_background_color = 'E6E6E6';

	add_theme_support( 'custom-background', array(
		// set default background color in child theme
		'default-color' => $default_background_color
	) );
}

// Blog posts excerpt
function get_the_post_excerpt(){
	$excerpt = get_the_content();
	$excerpt = preg_replace(" (\[.*?\])",'',$excerpt);
	$excerpt = strip_shortcodes($excerpt);
	$excerpt = strip_tags($excerpt);
	$excerpt = substr($excerpt, 0, 220); // change this to whatever you like
	$excerpt = substr($excerpt, 0, strripos($excerpt, " "));
	$excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
	$excerpt = '<p>'.$excerpt.' ... <span class="excerpt-read-more"><a href="' . get_permalink($post->ID) . '">Read more</a></span>';
return $excerpt;
}

// Register footer widgets
register_sidebar( array(
	'name' => __( 'Footer Widget One', 'tto' ),
	'id' => 'sidebar-4',
	'description' => __( 'Found at the bottom of every page (except 404s, optional homepage and full width) as the footer. Left Side.', 'tto' ),
	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	'after_widget' => '</aside>',
	'before_title' => '<h3 class="widget-title">',
	'after_title' => '</h3>',
) );

register_sidebar( array(
	'name' => __( 'Footer Widget Two', 'tto' ),
	'id' => 'sidebar-5',
	'description' => __( 'Found at the bottom of every page (except 404s, optional homepage and full width) as the footer. Center.', 'tto' ),
	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	'after_widget' => "</aside>",
	'before_title' => '<h3 class="widget-title">',
	'after_title' => '</h3>',
) );

register_sidebar( array(
	'name' => __( 'Footer Widget Three', 'tto' ),
	'id' => 'sidebar-6',
	'description' => __( 'Found at the bottom of every page (except 404s, optional homepage and full width) as the footer. Right Side.', 'tto' ),
	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	'after_widget' => "</aside>",
	'before_title' => '<h3 class="widget-title">',
	'after_title' => '</h3>',
) );

// WP-PageNavi
function twentytwelve_content_nav( $nav_id ) {
	global $wp_query;
	if ( $wp_query->max_num_pages > 1 ) : ?>
				<?php /* add wp-pagenavi support for posts */ ?>
		<?php if(function_exists('wp_pagenavi') ) : ?>
			<?php wp_pagenavi(); ?>
			<br />
		<?php else: ?>
		<nav id="<?php echo $nav_id; ?>">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'tto' ); ?></h3>
			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'tto' ) ); ?></div>
			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&larr;</span>', 'tto' ) ); ?></div>
		</nav><!-- #nav-above -->
	<?php endif; ?>
	<?php endif;
}

 // Change the meta information
function twentytwelve_entry_meta() {
	echo "<hr class=style-one>";
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentytwelve' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'twentytwelve' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'twentytwelve' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	$utility_text = '';
	if ( $tag_list ) {
		$utility_text .= __('<div class=post_cats><span>Categories:</span> %1$s</div>', 'twentytwelve' );
		$utility_text .= __('<div class=post_tags><span>Tags:</span> %2$s</div>', 'twentytwelve' );
	} elseif ( $categories_list ) {
		$utility_text .= __('<div class=post_cats><span>Categories:</span> %1$s</div>', 'twentytwelve' );
	}

	$utility_text .= __('<div class=post_date><span>Date:</span> %3$s</div>', 'twentytwelve' );
	$utility_text .= __('<div class=post_author><div><span>Author:</span> %4$s</div></div>', 'twentytwelve' );

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}