<?php

/* Job Listings functions
 * 
 * 
 */

include_once(get_stylesheet_directory().'/jl/jl_form_functions.php');

include_once(get_stylesheet_directory().'/jl/jl_ajax_functions.php');

include_once(get_stylesheet_directory().'/jl/jl_xml_functions.php');

// set the timezone to Central
date_default_timezone_set('America/Chicago');

// add code to include a new sitemap to the sitemap index through the Yoast plugin
// this sitemap will be auto-generated every day with all currently available jobs
// it will list all job detail url's in the sitemap

function add_sitemap_jobs(){
	$url = home_url().'/jobs-sitemap.xml';
	$sitemap_custom_items = '
	<sitemap>
		<loc>'.$url.'</loc>
		<lastmod>'.date('Y-m-d').'</lastmod>
	</sitemap>';

	return $sitemap_custom_items;
}

add_filter( 'wpseo_sitemap_index', 'add_sitemap_jobs' );

// need to set up the cron job that will create the jobs-sitemap.xml above
add_action('jl_daily_event', 'jl_build_sitemap');

function jl_sitemap_activation() {
	// build start time for 12:01am
	$start_time = strtotime(date('Y-m-d 06:25'));
	
	if ( !wp_next_scheduled( 'jl_daily_event' ) ) {
		wp_schedule_event( $start_time, 'daily', 'jl_daily_event');
	}
}
add_action('wp', 'jl_sitemap_activation');

function jl_build_sitemap() {
	// do something every hour
	
	include_once(get_stylesheet_directory().'/jl/jl_build_sitemap.php');
	
}





