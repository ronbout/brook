<?php

/* 
 * author: Ron Boutilier
 * Date:  07-05-2016
 * Description: build jobs-sitemap.xml which will contain all the job detail
 *				pages that are currently available in this system.
 */

 // give large time limit as it might be awhile
set_time_limit(60 * 30);

// set the timezone to Central
date_default_timezone_set('America/Chicago');

$jobs_page = 50;
$total_jobs = 500;
 
// build filename
$filename 	= $_SERVER['DOCUMENT_ROOT'].'/jobs-sitemap.xml';
//$filename = 'jobs-sitemap.xml';

// delete old file
if (file_exists($filename)) {
	unlink ($filename);
}

// open file for appending and write out header 
$file_ptr = fopen($filename, 'a');
$output = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
if ( ! fwrite($file_ptr, $output) ) {
	// cannot write rest of file if header is not included
	die();
}

// loop through api calls, pulling $jobs_page/call (test different #'s for speed)
$job_cnt = 0;
$pg_cnt = 1;

while ( get_next_jobs($output, $job_cnt, $pg_cnt, $jobs_page, $total_jobs) != false) {
	// append to file
	fwrite($file_ptr, $output);
}


// must write out final output and closing tags
$output .= '
</urlset> ';


fwrite($file_ptr, $output);
fclose($file_ptr);

// track the creation of the sitemap while development and testing
$logname 	= $_SERVER['DOCUMENT_ROOT'].'/sitemap_log.txt';
$date 		= date('m-d-y  H:i:s');
$logdata 	= $date . '   Generated jobs-sitemap.xml' . "\n";
file_put_contents($logname, $logdata, FILE_APPEND);


// if this is the brookdale live site, send a GET request to Google
// informing them of the sitemap update
if (strtolower($_SERVER['HTTP_HOST']) == 'www.brookdalecareers.com') {
	// use curl to send get request
	$google_url = 'http://www.google.com/ping?sitemap=http://www.brookdalecareers.com/sitemap_index.xml';
	curl_load_file( $google_url, '', false );
} 

////////////////////////////////////////////////////////

function get_next_jobs(&$output, &$job_cnt, &$pg_cnt, $jobs_page, $total_jobs) {	
	$url = 'https://career.StaffingSoft.com/careers/ssjobsapi.ashx';
	$output = '';

	$post_data = array(
		'si' => '013',
		'ui' => 'bslapiuser',
		'pwd' => 'm!A9%\R\a5Qtj',
		'cwid' => '1',
		'pagenumber' => $pg_cnt++,
		'recordperpage' => $jobs_page,
	);

	// build jobs xml
	$post_string = build_post_string($post_data);


	// call api for next group 
	$ret_xml = curl_load_file( $url, $post_string);
	
	$jobs = simplexml_load_string($ret_xml);
	
	if ( isset($jobs->error) && ! empty($jobs->error)) {
		// not sure what is happening, but don't keep going
		return false;
	}
	
	// build sitemap xml structure
	$output = build_sitemap_xml($jobs, $job_cnt);
	
	// see if we have more jobs to process by comparing job_cnt to jobs->totalcount
	// due to timing issues running on WPEngine, only going to process most recent
	// 2,000 jobs.  Since it runs every day, each job will have been made available to
	// the search engines for several days, if not it's entire lifespan
	if ($job_cnt >= min($total_jobs,$jobs->totalcount)) {
		return false;
	} else {
		return true;
	}
}

function build_sitemap_xml(&$jobs, &$job_cnt) {
	$output_xml = '';
	
	// loop through $jobs to build <url> for each
	// the xml count -2 = # of jobs ..<error> and <totalcount>
	
	$xml_count = $jobs->count() - 2;
	
	for ( $i = 0; $i < $xml_count; $i++ ) {
		$output_xml .= '
		<url>
			<loc>'. str_replace('&', '&amp;', $jobs->job[$i]->JobDetailURL).'</loc>
			<lastmod>'.date('Y-m-d', strtotime($jobs->job[$i]->DateCreated)).'</lastmod>
			<changefreq>monthly</changefreq>
			<priority>0.8</priority>
		</url>';
		$job_cnt++;
	}
	return $output_xml;
}

function build_post_string( $post_array ) {
	if( ! is_array($post_array) ) {
		return false;
	}
	
	$out_string = '';
	foreach( $post_array as $key => $value ) {
		if ( $out_string != '' ) {
			$out_string .= '&';
		}
		$out_string .= urlencode($key) . '=' . urlencode($value);
	}
	
	return $out_string;
}

function curl_load_file( $url, $post_string, $post_flg = true ) {
	 // create curl resource
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
    curl_setopt($ch, CURLOPT_TIMEOUT, 180); 

	curl_setopt($ch, CURLOPT_USERAGENT, 'Jobs');
	
	if ($post_flg) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string );
		// set up http header fields
		$headers = array(
			'Accept: text/xml',
			'Pragma: no-cache',
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: '. strlen($post_string),
			'Connection: keep-alive'
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	} else {
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
	}

	// add code to accept https certificate
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	// $output contains the output string
	$output = curl_exec($ch);
	// close curl resource to free up system resources
	curl_close($ch); 
	return $output;
}