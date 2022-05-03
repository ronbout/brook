<?php
/**
 * 
 * A test program to see what it would take to
 * bring in TheTaste jobs through the WP API
 * and make them usable in a Brookdale-like listing.
 * 
 * 4/20/2022  Ronald Boutilier
 * 
 *  Notes:  To keep it simple at this point, 
 *          don't worry about reading in all jobs
 *          or filtering.   This is just a test 
 *          of converting the info.  Also, will
 *          use default images if none present
 *          as my local system does not have 
 *          an "uploadable" uploads directory.
 * 
 */
// defined('ABSPATH') or die();

function get_taste_jobs($get_string) {

  $url ='http://localhost/taste_jobs/wp-json/rlbjobs/v1/jobfilter';

  $url =  $url . "?" . $get_string;

  // echo $url;
  // die();

  $jobs_json = curl_load_file( $url );

  $jobs = json_decode($jobs_json, true);

  print_r($jobs);

  die();

}


function build_taste_jobs_html ( $srch_args = array(), $src = 'BSLC', $detail_flag = false) {
	// $search contains search criteria
	// if empty, search all jobs

	$jobs_html = '';
	$desc_len  = 320;
	$pg = $srch_args['pagenumber'];
	$jobs_page = $srch_args['recordperpage'];
	$sort_field = $srch_args['jlsort'];

  
	$jobs_array = get_taste_jobs( $get_string);
	
	// if( ! $jobs_xml = get_jobs_api( $srch_args )) {
	// 	return '<h2 id="job-error">Error loading jobs data.</h2>';
	// }

	// take xml and convert to array for only requested page
	if (! is_array($jobs = load_jobs_array($jobs_xml, $src, $pg, $jobs_page, $detail_flag)) ) {
		// we have an error string in jobs instead of the array
		return $jobs;
	}

	$jobs_tmp = array_splice( $jobs, 0, 1 );
	$job_cnt = $jobs_tmp[0];
	$tot_pgs = ceil($job_cnt / $jobs_page);

	$pg_links = get_pg_links($tot_pgs, $pg);

	$jobs_hdr = get_listings_hdr( $job_cnt, $tot_pgs, $pg, $pg_links, $jobs_page, $sort_field );

	$jobs_list = get_listings_detail( $jobs_page, $job_cnt, $pg, $jobs, $desc_len, $pg_links );

	$jobs_html = $jobs_hdr . $jobs_list[0];
	$jobs_detail_html = '<article id="jobs-detail">' . $jobs_list[1]. '</article>';

	return $jobs_html . $jobs_detail_html;
}