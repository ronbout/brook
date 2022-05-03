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