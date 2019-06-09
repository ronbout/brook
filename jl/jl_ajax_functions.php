<?php

/* 
 * jl_ajax_functions.php
 * functions that will be called by ajax
 */

function jl_job_listing () {
	// check security nonce
	if (!check_ajax_referer('jl_ajax_nonce','security', false)) {
		echo '<h2>Security error loading data.  <br>Please Refresh the page and try again.</h2>';
		die();
	}
	
	include_once(get_stylesheet_directory().'/jl/jl_get_job_listing.php');
	extract($_POST);
	
	$zip_pattern = '/^[0-9]{5}(?:-[0-9]{4})?$/';
	$ampersand_pattern = '/\s(\&)[^\s-]/';
	
	// need to build lookup table for states to differentiate between 
	// cities and convert full state names into abbreviations for api
	$states = get_state_array();
	
	// first we need to convert srch_args into a lower level array
	$args = array();
	foreach ( $srch_args as $srch_arg ) {
		// check that a value exists.  text fields can have empty strings
		if ( empty($srch_arg['value']) ){
			continue;
		}
		if ( isset($args[$srch_arg['name']]) ) {
			// we have multiple values for a single input so just concatenate 
			$args[$srch_arg['name']] .= ','.$srch_arg['value'];
			continue;
		}
		// have to check for zip and see if it is a zipcode.  
		// if not, then send it through as the city or as the city and state
		if ( $srch_arg['name'] == 'zip' && ! preg_match( $zip_pattern, $srch_arg['value'])) {
			// break into city and state if comma is present and
			$city_tmp = explode(',',strtolower($srch_arg['value']));
			if (count($city_tmp) == 1) {
				// user can now enter a full name for state so we have to use lookup table
				$city_tmp[0] = trim($city_tmp[0]);
				if ( ($state = check_state($states, $city_tmp[0]) ) ) {
					add_state($state, $args);
				} else {
					$args['city'] = $city_tmp[0];
				}
				continue;
			}
			$args['city'] = $city_tmp[0];
			// make sure that state is the 2 letter abbrev
			// if the state is misspelled, just do nothing so that
			// at least the search by city is performed
			$city_tmp[1] = trim($city_tmp[1]);
			if ( ($state = check_state($states, $city_tmp[1]) ) ) {
				// have to see if state is already present
				add_state($state, $args);
				continue;
			} else {
				continue;
			}
		}
		if ( $srch_arg['name'] == 'keywords' ) {
			// have to do some cleanup because the api's we use 
			// do not do a good job of filtering their input
			$srch_arg['value'] =  preg_replace_callback($ampersand_pattern, function($m) {
													return str_replace($m[1], '& ', $m[0]);
												}
												, $srch_arg['value']);
		}
		$args[$srch_arg['name']] = $srch_arg['value'];
	}
	//echo '<h1>';
	//print_r($args);
	//echo '</h1>';
	//$t1 = microtime();
	// have to convert detail_flag from string to boolean
	$detail_flag = $detail_flag === 'false' ? false : true;
	echo  build_jobs_html ( $args, $src, $detail_flag );
	//$t2 = microtime();
	//if (false) {
	//	error_log( ($t2-$t1) . " get_jobs_api\n", 3, 'timing.log');
	//}
	die();
}

function get_state_array() {
	// make assoc array of state full name  => state abbrev
	// then can look up either and convert when necessary
	$states = array(
				'alabama'			=> 'al',
				'alaska'			=> 'ak',
				'arizona'			=> 'az',
				'arkansas'			=> 'ar',
				'california'		=> 'ca',
				'colorado'			=> 'co',
				'connecticut'		=> 'ct',
				'delaware'			=> 'de',
				'florida'			=> 'fl',
				'georgia'			=> 'ga',
				'hawaii'			=> 'hi',
				'idaho'				=> 'id',
				'illinois'			=> 'il',
				'indiana'			=> 'in',
				'iowa'				=> 'ia',
				'kansas'			=> 'ks',
				'kentucky'			=> 'ky',
				'louisiana'			=> 'la',
				'maine'				=> 'me',
				'maryland'			=> 'md',
				'massachusetts'	 	=> 'ma',
				'michigan'			=> 'mi',
				'minnesota'			=> 'mn',
				'mississippi'		=> 'ms',
				'missouri'			=> 'mo',
				'montana'			=> 'mt',
				'nebraska'			=> 'ne',
				'nevada'			=> 'nv',
				'new hampshire'	 	=> 'nh',
				'new jersey'		=> 'nj',
				'new mexico'		=> 'nm',
				'new york'			=> 'ny',
				'north carolina'	=> 'nc',
				'north dakota'		=> 'nd',
				'ohio'				=> 'oh',
				'oklahoma'			=> 'ok',
				'oregon'			=> 'or',
				'pennsylvania'		=> 'pa',
				'rhode island'		=> 'ri',
				'south carolina'	=> 'sc',
				'south dakota'		=> 'sd',
				'tennessee'			=> 'tn',
				'texas'				=> 'tx',
				'utah'				=> 'ut',
				'vermont'			=> 'vt',
				'virginia'			=> 'va',
				'washington'		=> 'wa',
				'west virginia'		=> 'wv',
				'wisconsin'			=> 'wi',
				'wyoming'			=> 'wy'
			);
	return $states;
}

function check_state(&$states, $val) {
	// will look through the array of state for the val
	// if it finds it as a key, we need to return the 
	// 2 digit abbreviation.  if it is already the abbrev,
	// return as is.  If it is not found, return false

	if (array_search($val, $states)) {
		return $val;
	}
	if (array_key_exists($val, $states)) {
		return $states[$val];
	} else {
		return false;
	}
}

function add_state($state, &$args) {
	// state can be added in multiple fields with different names so use function
	// to check whether to create array element or append to string
	// other multiple fields are caught earlier but the state values
	if (array_key_exists('state', $args)) {
		$args['state'] .= ',' . trim($state);
	} else {
		$args['state'] = trim($state);
	}
}

add_action('wp_ajax_list_jobs','jl_job_listing');
add_action('wp_ajax_nopriv_list_jobs','jl_job_listing');