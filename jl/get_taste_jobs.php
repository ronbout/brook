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


  $url ='http://10.0.0.78/taste_jobs/wp-json/rlbjobs/v1/jobfilter';

  echo $url . "?" . $get_string;
  die();

  $jobs_json = curl_load_file( $url, $get_string );

  $jobs = json_decode($jobs_json, true);

  print_r($jobs);

  die();

}

function curl_load_file( $url, $get_string ) {
  // create curl resource
 $ch = curl_init();

 // set url
 curl_setopt($ch, CURLOPT_URL, $url);

 //return the transfer as a string
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
   curl_setopt($ch, CURLOPT_TIMEOUT, 180); 

 curl_setopt($ch, CURLOPT_USERAGENT, 'Jobs');
 
 curl_setopt($ch, CURLOPT_POST, 0);
 
 // curl_setopt($ch, CURLOPT_POSTFIELDS, $get_string );
 
 // set up http header fields
 $headers = array(
   'Accept: application/json',
   'Pragma: no-cache',
   'Content-Type: application/x-www-form-urlencoded',
   'Content-Length: '. strlen($get_string),
   'Connection: keep-alive'
 );
 
 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
 
 // add code to accept https certificate
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

 // $output contains the output string
 $output = curl_exec($ch);
 // close curl resource to free up system resources
 curl_close($ch); 

 return $output;
}