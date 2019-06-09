<?php
/* 
 * Must do some pre-processing to determine if there is a query string and 
 * whether the page is accessed by search engine crawler
 */
$debug_string = '';

$crawler_strings = array('bot.htm', 'bot.php', 'spider', 'facebookexternalhit', 'Facebot', 'google', 'LinkedInBot', 'Google');

function check_crawler() {
	global $crawler_strings;
	
	// this will check the user agent to see if a likely crawler
	$crawler = false;
	for ($i = 0; $i < count($crawler_strings); $i++) {
		if (strpos($_SERVER['HTTP_USER_AGENT'], $crawler_strings[$i]) !== false) {
			$crawler = true;
			break;
		}
	}		

	/***** for now, we will also test the GET variable...TESTING ONLY  ******/
	$crawler = isset($_GET['crawler']) ? true: $crawler;
	
	return $crawler;
}

function check_query() {
	// checks and returns the query string if present
	return isset($_SERVER['QUERY_STRING']) ? urldecode($_SERVER['QUERY_STRING']) : '';
}

$crawler_flag = check_crawler();
$query_flag = ($q_string = check_query()) ? true : false;
$detail_flag = false;

$search_section_class = '';
$detail_section_class = 'hidden';
$job_detail = '';
$meta_string = '';
$og_array = array();

$social_url_placeholder = 'urlplaceholder';
$social_title_placeholder = 'titleplaceholder';
$canonical_url = 'http://'. $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];

// check if the src is from somewhere other than BSLC.  if so, switch so that the
// canonical url is the src=BSLC version
if (isset($_GET['src']) && $_GET['src'] != 'BSLC') {
	$canonical_url = str_replace('src='.$_GET['src'], 'src=BSLC', $canonical_url);
}

if ($query_flag) {
	$q_vars = array_change_key_case($_GET);
	$detail_flag = isset($q_vars['j']) && !empty($q_vars['j']) && isset($q_vars['display']) && 
			strtolower($q_vars['display']) == 'details' ? true : false;
}
/*
ob_start();
echo '<span id="hidden-debug" class="hidden">';
echo '<br>*crawler flag: ' . $crawler_flag."*\n";
echo '<br>*detail flag: ' . $detail_flag."*\n";
echo '<br>*query flag: ' . $query_flag."*\n";
echo '<br>*query string' . $_SERVER['QUERY_STRING']."*\n";
print_r($_GET);

echo '</span>';
$debug_string = ob_get_contents();
ob_end_clean();

 */

if ($detail_flag && $crawler_flag) {
	// this code only runs when a search engine crawler tries to access a job detail page
	// we are rendering server side so that the crawler can see the info easily
	$query_flag = false;
	// change class variables to reverse hidden section
	$search_section_class = 'hidden';
	$detail_section_class = '';
	
	// have to build job detail page to display
	include_once(get_stylesheet_directory().'/jl/jl_get_job_listing.php');
	
	// build search args for api call
	$srch_args = array('pagenumber' => '1',
						'recordperpage' => '1',
						'id' => $q_vars['j']
		);
	
	if( ! $jobs_xml = get_jobs_api( $srch_args )) {
		
		// return 500 server error
		status_header( 500 );
		nocache_headers();
		echo 'Error loading jobs data.';
		die();
	}
	
	// take xml and convert to array for only requested page
	if (! is_array($jobs = load_jobs_array($jobs_xml, 1, 1)) ) {
		// we have an error string in jobs instead of the array
		// we have nothing to show search engine and it 
		// is due to Job no longer existing
		//  RETURN 404 ERROR CODE
		if ( strpos(strtolower(strip_tags($jobs)), 'no results') === false ) {
			status_header( 500 );
			echo '<h2>500 Internal Server Error</h2>';
		} else {
			status_header( 404 );
			echo '<h2>404 Error</h2>';
		}
		nocache_headers();
		echo $jobs;
		die();
	}
	
	// pull out only the jobs detail portion (note that jobs[0] 
	// contains the job count, so we only send jobs[1]
	ob_start();
	get_listings_detail_html( $jobs[1] );

	$job_detail .= ob_get_contents();
	ob_end_clean();
	
	// now we need to set up meta data for the search engine crawlers to read
	$meta_string = get_crawler_meta($jobs[1]);
		
	add_action('genesis_meta', 'add_meta_tags_job');
	
	function add_meta_tags_job() {
		global $meta_string;
		echo $meta_string;
	}
	
	// we have a number of filters to run to adjust the Yoast SEO plugin
	
	// turn off the canonical url, which will completely screw up
	// search engine crawler's ability to see the detail page
	add_filter( 'wpseo_canonical', 'change_canonical' );
	
	function change_canonical() {
		global $canonical_url;
		return $canonical_url;
	}
	// set the page title through the Yoast filter
	$title_page = $jobs[1]['JobTitle'] . ' - Brookdale Careers';
	function assignPageTitle(){
		global $title_page;
		return $title_page;
	}
	add_filter('wpseo_title', 'assignPageTitle');
}

function get_crawler_meta($job) {
	// Yoast is not setting the og:description or og:image properties, so add those in here
	$desc = $job['description'];
	$meta_data = '<meta property="description" content="' . substr( $desc, 0, min(400, strlen($desc))) . '... " />
					<meta property="og:description" content="'.substr( $desc, 0, min(400, strlen($desc))) . '... " />
					<meta property="og:image" content="'. $job['JobImageURL']. '" />';
	return $meta_data;
}

//* Enqueue Jobs Page Stylesheets and Scripts
add_action('wp_enqueue_scripts', 'jl_load_resources');

function jl_load_resources() {
    global $post;
	
	if ( $post->post_name == 'jobs' ) {
		wp_enqueue_style( 'styling', get_stylesheet_directory_uri() . '/jl/styles.css' );
		wp_enqueue_script( 'jl-js', get_stylesheet_directory_uri() . '/jl/jl.js', array( 'jquery', 'jquery-ui-core' ), false, false);
		//wp_enqueue_script( 'jl-js', get_stylesheet_directory_uri() . '/jl/jl.min.js', array( 'jquery', 'jquery-ui-core' ), false, false);
		// add code to include the google js api's for shortlinks
		//wp_enqueue_script('google-js-apis', 'https://apis.google.com/js/api.js', array(), false, true);

	}
}

add_action('wp_enqueue_scripts', 'jl_localize_script');

function jl_localize_script() {
	global $query_flag, $q_string, $social_url_placeholder, $social_title_placeholder;
	
	wp_localize_script('jl-js', 'jobListing', array(
			'security' => wp_create_nonce('jl_ajax_nonce'),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'queryFlag' => $query_flag,
			'query' => $q_string,
			'socialURLPlaceholder' => $social_url_placeholder,
			'socialTitlePlaceholder' => $social_title_placeholder,
			'indeedFlag'	=> false,
		));
}

// just for testing the crawlers that come to site

function log_ips() {
	global $crawler_strings;
	$ip 		= $_SERVER['REMOTE_ADDR'];
	// don't log ip's for my own computers
	if ($ip == '127.0.0.1' || substr($ip,0,10) == '192.168.1.') return;
	$logname 	= $_SERVER['DOCUMENT_ROOT'].'/ip_log.txt';
	//$logname = get_stylesheet_directory().'/ip_log.txt';
	$progname 	= $_SERVER['PHP_SELF'];
	
	$crawler = 0;
	for ($i = 0; $i < count($crawler_strings); $i++) {
		if (strpos($_SERVER['HTTP_USER_AGENT'], $crawler_strings[$i]) !== false) {
			$crawler = 1;
			break;
		}
	}
	
	$user_agent	= $_SERVER['HTTP_USER_AGENT'];
	
	$query 		= 'query: *'.$_SERVER['QUERY_STRING'].'*';
	$crawl_string 	= 'crawler: *'.$crawler.'*';
	$date 		= date('m-d-y  H:i:s');
	
	$logdata = $date."\t".$progname."\t".$ip."\t".$crawl_string."\t".$query."\t".$user_agent."\n";
	// in production, only record crawlers
	if ( $crawler ) {
		file_put_contents($logname, $logdata, FILE_APPEND);
	}
}

//log_ips();