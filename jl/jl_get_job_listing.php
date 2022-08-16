<?php

date_default_timezone_set('America/Chicago');

/* 
 * get_job_listing.php
 * This is the code that actually retrieves the job listing
 * whether it is from cached xml file or api calls to staffingsoft
 */

include_once(get_stylesheet_directory().'/jl/jl_job_detail_page.php');

function build_jobs_html ( $srch_args = array(), $src = 'BSLC', $detail_flag = false) {
	// $search contains search criteria
	// if empty, search all jobs

	$jobs_html = '';
	$desc_len  = 320;
	$pg = $srch_args['pagenumber'];
	$jobs_page = $srch_args['recordperpage'];
	$sort_field = $srch_args['jlsort'];
	
	if( ! $jobs_xml = get_jobs_api( $srch_args )) {
		return '<h2 id="job-error">Error loading jobs data.</h2>';
	}

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

function get_pg_links($tot_pgs, $pg) {
	// build pagination args, then get and build links
	$p_args = array(
		'base'		=> 'javascript:void(0);',
		'total'		=> $tot_pgs,
		'current'	=> $pg,
		'mid_size'	=> 2,
		'end_size'	=> 1,
		'type'		=> 'array'
	);
	
	$pg_link_array = paginate_links( $p_args);
	$pg_links = '<div class="jl-pagination">';
	$link_count = count($pg_link_array);
	foreach( $pg_link_array as $key=>$pg_link ) {
		$pg_link_nbr = strip_tags($pg_link);
		$pg_links .= '<div class="btn ';
		$pl_links_class = 'btn-pagination';
		if ( $key == 0 && $pg > 1 ) {
			$pg_links .= $pl_links_class.' btn-pagination-arrow prev">&lsaquo;</div>';
			continue;
		}
		if ( $key == $link_count - 1 && $pg < $tot_pgs ) {
			$pg_links .= $pl_links_class.' btn-pagination-arrow next">&rsaquo;</div>';
			continue;
		}
		if ( $pg_link_nbr == $pg ) {
			$pg_links .= ' btn-pagination-active">' . $pg_link_nbr .'</div>';
			continue;
		}
		if ( $pg_link_nbr == '&hellip;' ) {
			$pg_links .= ' btn-pagination-dots">' . $pg_link_nbr .'</div>';
			continue;
		}
		$pg_links .= $pl_links_class.'">' . $pg_link_nbr . '</div>';
	}
	$pg_links .= '</div>';
	
	/*$pg_links = '<ul class="jl-page-numbers">';
	foreach( $pg_link_array as $pg_link ) {
		$pg_links .= '<li class="jl-page-link">' . $pg_link . '</li>';
	}
	$pg_links .= '</ul>';*/
	return $pg_links;
}

function get_listings_hdr( $job_cnt, $tot_pgs, $pg, $pg_links, $jobs_page, $sort_field ) {
	$ret_html = '
		'. $pg_links .'						
			<div id="jl-list-select-div">
				<div class="searchfield">
					<h3>Jobs/Page</h3>
					<select class="jl-select" id="jl-jobs-page" name="jobs-page">
						<option value="10" '. check_option_val( 10, $jobs_page ).' >&nbsp; &nbsp;10</option>
						<option value="20" '. check_option_val( 20, $jobs_page ).'>&nbsp; &nbsp;20</option>
						<option value="50" '. check_option_val( 50, $jobs_page ).'>&nbsp; &nbsp;50</option>
					</select>
				</div>
				<div class="searchfield">
					<h3>Sort By</h3>
					<select class="jl-select" id="jl-jobs-sort" name="jobs-sort">
						<option value="title_asc" '. check_option_val( 'title_asc', $sort_field ).' >Title A-Z</option>
						<option value="title_desc" '. check_option_val( 'title_desc', $sort_field ).' >Title Z-A</option>
						<option value="location_asc" '. check_option_val( 'location_asc', $sort_field ).'>Location A-Z</option>
						<option value="location_desc" '. check_option_val( 'location_desc', $sort_field ).'>Location Z-A</option>
						<option value="date_desc" '. check_option_val( 'date_desc', $sort_field ).'>Date (Newest)</option>
						<option value="date_asc" '. check_option_val( 'date_asc', $sort_field ).'>Date (Oldest)</option>
					</select>
				</div>
			</div>
';
	
	return $ret_html;
}

function get_listings_detail( $jobs_page, $job_cnt, $pg, $jobs, $desc_len, $pg_links ) {
	// for the count we need to stop at the number of jobs per page or
	// the last record in the total list, which is jobcount - (pg-1)*jobspage
	$jobs_html = '<div id="jl-listing-div">';
	$jobs_detail = '';
	for ( $i = 0; $i < min( $jobs_page, $job_cnt - (($pg - 1) * $jobs_page)) ; $i++ ) {
		$desc = $jobs[$i]['description'];
		
		$job_desc  = substr( $desc, 0, min($desc_len, strlen($desc))) . '... ';
		$jobs_html .= '
			<div class="result-container" data-date="'.$jobs[$i]['DateCreated'].'" data-title="'.$jobs[$i]['JobTitle'].'" data-location="'.$jobs[$i]['Location'].'">
				<!--<img class="job-image" src="'.$jobs[$i]['JobImageURL'].'" alt="Job '.$jobs[$i]['JobNumber'].' Image">-->
				<div class="job-image-container">
					<div class="job-image" style="background-image: url(\''.$jobs[$i]['JobImageURL'].'\'); "></div>
				</div>
				<p class="job-type">'.$jobs[$i]['JobType'].'</p>
				<p class="job-location">'.$jobs[$i]['Location'].'</p>
				<h3 class="jl-job-title" data-job="detail-'.$jobs[$i]['JobNumber'].'">'. $jobs[$i]['JobTitle'] .' - '.$jobs[$i]['JobNumber'].'</h3>
				<p>'.  $job_desc.'</p>
				<div class="result-container-nav">
					<div class="btn btn-green jl-show-details" data-job="detail-'.$jobs[$i]['JobNumber'].'">More Details</div>
					<div class="btn btn-green map-button" data-map="'.$jobs[$i]['GoogleMap'].'">Show on map</div>
					<div class="btn btn-orange apply-button" data-job="detail-'.$jobs[$i]['JobNumber'].'">Apply Now</div>
				</div><!-- end of result-container-nav -->
			</div>';
		
		// to make html detail code easier, use ob_start. 
		ob_start();
		get_listings_detail_html( $jobs[$i] );
		
		$jobs_detail .= ob_get_contents();
		ob_end_clean();
	}
	
	$jobs_html .= '</div><!-- end of jl-listing-div-->' . $pg_links;
	return array($jobs_html, $jobs_detail);
}

function check_option_val( $opt_val, $current_val ) {
	if ( $opt_val == $current_val ) {
		return 'selected="selected" ';
	}
	return;
}

function load_jobs_array( $jobs, $src = 'BSLC', $pg = 1, $jobs_page = 10, $detail_flag ) {
	// check for error 
	if ( isset($jobs->error) && ! empty($jobs->error)) {
		// if this is a job detail page and the error says "no results"
		// we need to change the message to be more user friendly
		if ( $detail_flag && strpos(strtolower(strip_tags($jobs->error)), 'no results') !== false ) {
			return '<h2 id="job-error">That job is no longer available.<br>
						Please use this search form to explore currently 
						available positions.</h3>';
		}
		return '<h2 id="job-error">' . $jobs->error . '</h2>';
	}
	
	// set up default images and video
	$job_img_default = get_stylesheet_directory_uri() . '/images/assoc.jpg';
	$loc_img_default = get_stylesheet_directory_uri() . '/images/18660-Brookdale-Green-Hills-Cumberland-Entrance-656x300_c.jpg';
	$job_vid_default = 'CkAbNMgIhPY';
	
	$job_cnt = $jobs->totalcount;
	
	$return_jobs[] = $job_cnt;
	$job_start = ( $pg - 1 ) * $jobs_page;
	
	//$uploads_dir = wp_upload_dir()['basedir'];
	
	if ( $job_cnt > $job_start ) {
		for ( $i = 0 ; ($i <  $jobs_page) && ($i < $job_cnt) ; $i++ ) {
			$job_img = (string) $jobs->job[$i]->JobImageURL;
			$job_vid = (string) $jobs->job[$i]->JobVideoURL;
			$loc_img = (string) $jobs->job[$i]->CommunityImageURL;
			// check that images actually exist or give default
	/******* since the images are only on the actual server, I cannot test for the image until install  ******/
	/******* bad images will just have to not display for now  ******/
			//$job_img = (empty($job_img) || ! file_exists($uploads_dir . explode('uploads',$job_img)[1])) ? $job_img_default : $job_img;
			//$loc_img = (empty($loc_img) || ! file_exists($uploads_dir . explode('uploads',$loc_img)[1])) ? $loc_img_default : $loc_img;
			// $job_img = (empty($job_img) ) ? $job_img_default : $job_img;
			$loc_img = (empty($loc_img) ) ? $loc_img_default : $loc_img;
			$job_img = $job_img_default;
			// retrieve the youtube video id
			$vid_id = getYouTubeId($job_vid);
			$job_vid = (empty($job_vid) || empty($vid_id)) ? $job_vid_default : $vid_id;
			
			// strip the query string from the JobDetailURL and add the src
			$job_detail_url = explode('?', $jobs->job[$i]->JobDetailURL);
			$job_detail_url = '?' . $job_detail_url[1] . '&src=' . $src;
			
			// create array of values for the apply with indeed button data attrs
			$indeed_data = convert_query($jobs->job[$i]->{'indeed-apply-data'});
			$indeed_cont = explode('?',$_SERVER['HTTP_REFERER']);
			$indeed_cont = $indeed_cont[0].$job_detail_url;
			
			$return_jobs[] = array( 
							'JobNumber'			=> trim($jobs->job[$i]->JobNumber),
							'JobTitle'			=> trim($jobs->job[$i]->JobTitle),
							'Location'			=> str_replace(',', ', ',$jobs->job[$i]->Location),
							// description is used for the abbrev job listing and has tags stripped
							'description'		=> preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', strip_tags($jobs->job[$i]->Opportunity)),
							'Opportunity'		=> trim($jobs->job[$i]->Opportunity),
							'RequiredSkills'	=> trim($jobs->job[$i]->RequiredSkills),
							'Benefits'			=> trim($jobs->job[$i]->Benefits),
							// change ApplyURL src to $src
							'ApplyURL'			=> str_replace('src=BSLC', 'src='.$src, $jobs->job[$i]->ApplyURL),
							'JobType'			=> trim($jobs->job[$i]->JobType),
							'DateCreated'		=> strtotime($jobs->job[$i]->DateCreated),
							'Jobclass'			=> trim($jobs->job[$i]->Jobclass),
							'Officetype'		=> trim($jobs->job[$i]->Officetype),
							'Experiencelevel'	=> trim($jobs->job[$i]->Experiencelevel),
							'Key'				=> trim($jobs->job[$i]->Key),
							'Category'			=> trim($jobs->job[$i]->ClientCategory),
							'GoogleMap'			=> "<iframe src='".$jobs->job[$i]->Googlemap."' width='420' height='400' frameborder='0' style='border:0' allowfullscreen></iframe>",
							'JobImageURL'		=> $job_img,
							'JobVideoURL'		=> $job_vid,
							'CommunityImageURL'	=> $loc_img,
							'JobDetailURL'		=> $job_detail_url,
							'IndApplyData'		=> $indeed_data,
							// the indeed apply continue url needs the full path of the job detail page
							'IndContinueUrl'	=> $indeed_cont,
							'Comments'			=> trim($jobs->job[$i]->Comments),
							'CommunityContent'	=> trim($jobs->job[$i]->CommunityContent),
			);
		}

		return $return_jobs;
	} else {
		return 'Invalid job page';  // error code  = not enough jobs for that request
	}
}

function convert_query($query) {
	$pairs = explode('&', $query);

	$pair_array = array();
	foreach ($pairs as $pair) {
		$tmp = explode('=', $pair);
		$pair_array[$tmp[0]] = urldecode($tmp[1]);
	}
	return $pair_array;
}

function getYouTubeId( $url ) {
	// parse the youtube url to retrieve only the video id
	// write code for both 'watch' and 'embed' versions
	// adding in code for youtu.be
	if (strpos( $url, 'watch') !== false ) {
		// find "v="
		$youTubeId = explode('v=', $url);
		$youTubeId = $youTubeId[1];
		// just in case get rid of any & extra vars
		$youTubeId = explode('&', $youTubeId);
		$youTubeId = $youTubeId[0];
		return $youTubeId;
	}
	if (strpos( $url, 'embed') !== false ) {
		// find "embed/"
		$youTubeId = explode('embed/', $url);
		$youTubeId = $youTubeId[1];
		// now remove the ?get portion of the string
		$youTubeId = explode('?', $youTubeId);
		$youTubeId = $youTubeId[0];
		return $youTubeId;
	}
	if (strpos( $url, 'youtu.be') !== false) {
		$youTubeId = explode('youtu.be/', $url);
		$youTubeId = $youTubeId[1];
		return $youTubeId;
	}
	return '';
}

function curl_load_file( $url, $post_string ) {
	 // create curl resource
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
    curl_setopt($ch, CURLOPT_TIMEOUT, 180); 

	curl_setopt($ch, CURLOPT_USERAGENT, 'Jobs');
	
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
	
	// add code to accept https certificate
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	// $output contains the output string
	$output = curl_exec($ch);
	// close curl resource to free up system resources
	curl_close($ch); 

	return $output;
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
		
		//if $key is jlsort, do not include.  
		if ( $key != 'jlsort' ) {
			$out_string .= urlencode($key) . '=' . urlencode(str_replace(array('\"', "\'"), array('"', "''"),$value));
		}
	}
	
	return $out_string;
}

function get_jobs_api( $srch_args ) {

	/*
	$jobs_url = 'https://career.StaffingSoft.com/careers/ssjobsapi.ashx';
	
	$access_data = array(
		'si' => '013',
		'ui' => 'bslapiuser',
		'pwd' => 'm!A9%\R\a5Qtj',
		'cwid' => '1',
	);

	$post_data = array_merge($srch_args, $access_data);
	$post_string = build_post_string($post_data);
	
	$jobs_string =  curl_load_file($jobs_url, $post_string );

	if (!($jobs_xml = simplexml_load_string($jobs_string))) {
		// log to an error file
		$log_file = get_stylesheet_directory().'/api_error.log';
		$err_msg = date('d-m-Y H:i:s') . "\n";
		$err_msg .= $post_string . "\n";
		$err_msg .= substr($jobs_string, 0, 320) . "\n*********************************";
		error_log($err_msg, 3, $log_file);
		
		// write out count to daily error log
		write_daily_errors();
	}
*/

/**
 * 
 * The site is no longer up.  Brookdale changed their ATS system to icims.
 * For a demo of the old site, I need to pull in a static jobs xml file.
 * 
 */

 	$jobs_file = get_stylesheet_directory().'/xml/jobs.xml';

	$jobs_xml = simplexml_load_file($jobs_file);

	return $jobs_xml;
}

function write_daily_errors() {
	$daily_filename = get_stylesheet_directory().'/daily_error.log';
	// get file or create new one
	if ( ($date_file = @file($daily_filename)) === false ) {
		$date_file = array(date('Y-m-d') => 0);
	} else {
		$new_file = array_reduce($date_file, function ($new_array, $ln) {
			$tmp = explode(',', trim($ln));
			$new_array[$tmp[0]] = $tmp[1];
			return $new_array;
		}, array() );
			$date_file = $new_file;
	}
	//  look for current date
	$today = date('Y-m-d');
	if ( array_key_exists($today, $date_file) ) {
		$date_file[$today] += 1;
	} else {
		$date_file[$today] = 1;
	}
	
	$out_file = '';
	foreach($date_file as $key => $val ) {
		$out_file .= $key . ',' . $val . "\n";
	}
	
	file_put_contents($daily_filename, trim($out_file));
}