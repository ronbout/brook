<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Genesis Sample Theme' );
define( 'CHILD_THEME_URL', 'http://www.studiopress.com/' );
define( 'CHILD_THEME_VERSION', '2.1.2' );

//* Enqueue Google Fonts
add_action( 'wp_enqueue_scripts', 'genesis_sample_google_fonts' );
function genesis_sample_google_fonts() {
	global $post;
	
	if ( $post->post_name ==  'jobs') {
		wp_enqueue_style( 'jl-google-fonts', '//fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext', array(), CHILD_THEME_VERSION );
	} else {
		wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Lato:300,400,700', array(), CHILD_THEME_VERSION );
		wp_enqueue_style( 'prefix-font-awesome', 'http://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css', array(), '4.2.0' );
	}

}

//* Enqueue Custom Stylesheet
add_action('wp_enqueue_scripts', 'custom_style_sheet');
function custom_style_sheet() {
	
	wp_enqueue_style( 'custom-styling', get_stylesheet_directory_uri() . '/custom.css' );

}
//* Add support for structural wraps for full-width homepage
add_theme_support( 'genesis-structural-wraps', array( 'header','subnav','footer-widgets', 'footer', 'inner' ) );

//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add support for custom background
add_theme_support( 'custom-background' );

//* Add support for 3-column footer widgets
add_theme_support( 'genesis-footer-widgets', 2 );


//* Remove the header right widget area
unregister_sidebar( 'header-right' );

//* Reposition the primary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_header', 'genesis_do_nav', 12 );

// remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
// remove_action( 'genesis_footer', 'genesis_do_footer' );
// remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );


//Remove Page Titles
add_action ('get_header', 'remove_page_titles_genesis'); 
/**
 * @author    Brad Dalton
 * @example   http://wpsites.net/
 */
function remove_page_titles_genesis() {
if ( is_page() || $post->post_parent ) { 
remove_action('genesis_entry_header', 'genesis_do_post_title');
}}

//* Customize the credits
add_filter( 'genesis_footer_creds_text', 'sp_footer_creds_text' );
function sp_footer_creds_text() {
	echo '<div class="creds">';
	echo '<div class="links"><p>';
	echo '<a href="#">Privacy Policy</a> &nbsp;&nbsp; | &nbsp;&nbsp; <a href="/jobs/">Jobs</a>&nbsp;&nbsp; | &nbsp;&nbsp; <a href="/contact-2/">Contact</a> &nbsp;&nbsp; | &nbsp;&nbsp; <a href="http://www.brookdale.com">Brookdale.com</a> &nbsp;&nbsp;&nbsp;&nbsp;';
	echo '</p></div>';
	echo '<div class="copyright"><p>';
	echo 'Copyright &copy; ';
	echo date('Y');
	echo ' Brookdale, &nbsp;All Rights Reserved';
	echo '</p></div>';
	echo '<div class="social"><p>';
	echo '<a href="https://www.facebook.com/BrookdaleCareers" title="Facebook" target="_blank"><i class="fa fa-facebook fa-lg"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://twitter.com/BrookdaleJobs" title="Twitter" target="_blank"><i class="fa fa-twitter fa-lg"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.linkedin.com/company/brookdale-senior-living" title="Linkedin" target="_blank"><i class="fa fa-linkedin fa-lg"></i></a>';
	echo '</p></div>';
	echo '</div>';
}
//Featured Image Widget
genesis_register_sidebar(
    array(
        'name'=>'Featured Image (Home/Contact Pages)',
        'id' => 'featured-image-content',
        'description' => 'This is a custom sidebar for displaying a Featured Image on a Page',
        'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrap">',
        'after_widget'  => "</div></div>",
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => "</h4>"
    )
);
add_action( 'genesis_after_header', 'mp_cta_genesis' );
//Remove Blog and Archive page templates
function be_remove_genesis_page_templates( $page_templates ) {
	unset( $page_templates['page_archive.php'] );
	unset( $page_templates['page_blog.php'] );
	return $page_templates;
}
add_filter( 'theme_page_templates', 'be_remove_genesis_page_templates' );

//* Add features image on single post
add_action( 'genesis_after_header', 'feature_my_single_post_image' );

function feature_my_single_post_image() {

  if( is_singular( 'post' ) ) {

    global $post;

    $size = 'full-size';
    $default_attr = array(
      'class' => "aligncenter attachment-$size $size",
      'alt'   => $post->post_title,
      'title' => $post->post_title,
    );

    printf( '<div class="wrap leader-image unbound-div">%s</div>', genesis_get_image( array( 'size' => $size, 'attr' => $default_attr ) ) );

  }

}

//* Customize search form input box text
add_filter( 'genesis_search_text', 'sp_search_text' );
function sp_search_text( $text ) {
	return esc_attr( 'Search our blog...' );
}

function b3m_search_button_text( $text ) {

	return esc_attr( '&#xf010;' );

}
add_filter( 'genesis_search_button_text', 'b3m_search_button_text' );

//* incude job listings functions
include_once(get_stylesheet_directory().'/jl/jl_functions.php');
