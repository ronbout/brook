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

  $url ='http://localhost/taste/wp-json/rlbjobs/v1/jobfilter';

  $url =  $url . "?" . $get_string;

  // echo $url;
  // die();

  $jobs_json = curl_load_file( $url );

  $jobs = json_decode($jobs_json, true);

  return $jobs;

}


function build_taste_jobs_html ( $srch_args = array(), $src = 'BSLC', $detail_flag = false) {
	// $search contains search criteria
	// if empty, search all jobs

	$jobs_html = '';
	$desc_len  = 320;
	// $pg = $srch_args['pagenumber'];
	// $jobs_page = $srch_args['recordperpage'];
	// $sort_field = $srch_args['jlsort'];
  $pg = 1;
  $jobs_page = 10;
  $sort_field = "date_desc";

  $get_string = build_get_string($srch_args);
  
	$jobs_array = get_taste_jobs( $get_string);
	
	// if( ! $jobs_xml = get_jobs_api( $srch_args )) {
	// 	return '<h2 id="job-error">Error loading jobs data.</h2>';
	// }

	// take xml and convert to array for only requested page
	if (! is_array($jobs = load_taste_jobs_array($jobs_array, $src, $pg, $jobs_page, $detail_flag)) ) {
		// we have an error string in jobs instead of the array
		return $jobs;
	}

	$jobs_tmp = array_splice( $jobs, 0, 1 );
	$job_cnt = $jobs_tmp[0];
	$tot_pgs = ceil($job_cnt / $jobs_page);

	$pg_links = get_pg_links($tot_pgs, $pg);

	$jobs_hdr = get_listings_hdr( $job_cnt, $tot_pgs, $pg, $pg_links, $jobs_page, $sort_field );

	$jobs_list = get_taste_listings_detail( $jobs_page, $job_cnt, $pg, $jobs, $desc_len, $pg_links );

	$jobs_html = $jobs_hdr . $jobs_list[0];
	$jobs_detail_html = '<article id="jobs-detail">' . $jobs_list[1]. '</article>';

	return $jobs_html . $jobs_detail_html;
}


function load_taste_jobs_array( $jobs, $src = 'BSLC', $pg = 1, $jobs_page = 10, $detail_flag ) {
	// check for error 
	if ( isset($jobs['error']) && ! empty($jobs['error'])) {
		// if this is a job detail page and the error says "no results"
		// we need to change the message to be more user friendly
		if ( $detail_flag && strpos(strtolower(strip_tags($jobs['error'])), 'no results') !== false ) {
			return '<h2 id="job-error">That job is no longer available.<br>
						Please use this search form to explore currently 
						available positions.</h3>';
		}
		return '<h2 id="job-error">' . $jobs['error'] . '</h2>';
	}
	
	// set up default images and video
	$job_img_default = get_stylesheet_directory_uri() . '/images/assoc.jpg';
	$loc_img_default = get_stylesheet_directory_uri() . '/images/18660-Brookdale-Green-Hills-Cumberland-Entrance-656x300_c.jpg';
	$job_vid_default = 'CkAbNMgIhPY';
	
	$job_cnt = $jobs['jobs_count'];
	
	$return_jobs[] = $job_cnt;
	$job_start = ( $pg - 1 ) * $jobs_page;
	
	//$uploads_dir = wp_upload_dir()['basedir'];
	
		for ( $i = 0 ; ($i <  $jobs_page) && ($i < $job_cnt) ; $i++ ) {
      $job = $jobs['jobs'][$i];

			// $job_img = (string) $jobs->job[$i]->JobImageURL;
			$job_img = $job['logo_img'];
			// $job_vid = (string) $jobs->job[$i]->JobVideoURL;
      $job_vid = "https://youtu.be/e-Ge8wRkHzs";
			// $loc_img = (string) $jobs->job[$i]->CommunityImageURL;
			$loc_img = $loc_img_default;
			// check that images actually exist or give default
	/******* since the images are only on the actual server, I cannot test for the image until install  ******/
	/******* bad images will just have to not display for now  ******/
			//$job_img = (empty($job_img) || ! file_exists($uploads_dir . explode('uploads',$job_img)[1])) ? $job_img_default : $job_img;
			//$loc_img = (empty($loc_img) || ! file_exists($uploads_dir . explode('uploads',$loc_img)[1])) ? $loc_img_default : $loc_img;
			$job_img = (empty($job_img) ) ? $job_img_default : $job_img;
			$loc_img = (empty($loc_img) ) ? $loc_img_default : $loc_img;
			// retrieve the youtube video id
			$vid_id = getYouTubeId($job_vid);
			$job_vid = (empty($job_vid) || empty($vid_id)) ? $job_vid_default : $vid_id;
      
			
			// strip the query string from the JobDetailURL and add the src
			// $job_detail_url = explode('?', $jobs->job[$i]->JobDetailURL);
			// $job_detail_url = '?' . $job_detail_url[1] . '&src=' . $src;
			$job_detail_url = $job['application'];
      $fake_google_url = "http://www.google.com/maps?q=3250 Chanate Road,Santa Rosa,CA,95404&output=embed";
			
			// create array of values for the apply with indeed button data attrs
			// $indeed_data = convert_query($jobs->job[$i]->{'indeed-apply-data'});
			// $indeed_cont = explode('?',$_SERVER['HTTP_REFERER']);
			// $indeed_cont = $indeed_cont[0].$job_detail_url;
			$indeed_data = '';
			$indeed_cont = 'https://ie.indeed.com/';
			
			$return_jobs[] = array( 
							// 'JobNumber'			=> trim($jobs->job[$i]->JobNumber),
							'JobNumber'			=> trim($job['job_id']),
							// 'JobTitle'			=> trim($jobs->job[$i]->JobTitle),
							'JobTitle'			=> trim($job['job_title']),
							// 'Location'			=> str_replace(',', ', ',$jobs->job[$i]->Location),
							'Location'			=> trim($job['job_location']),
							// description is used for the abbrev job listing and has tags stripped
							// 'description'		=> preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', strip_tags($jobs->job[$i]->Opportunity)),
							'description'		=> preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', strip_tags($job['job_desc'])),
							// 'Opportunity'		=> trim($jobs->job[$i]->Opportunity),
							'Opportunity'		=> trim($job['job_desc']),
							// 'RequiredSkills'	=> trim($jobs->job[$i]->RequiredSkills),
							'RequiredSkills'	=> '',
							// 'Benefits'			=> trim($jobs->job[$i]->Benefits),
							'Benefits'			=> '',
							// change ApplyURL src to $src
							// 'ApplyURL'			=> str_replace('src=BSLC', 'src='.$src, $jobs->job[$i]->ApplyURL),
							'ApplyURL'			=> $job['application'],
							// 'JobType'			=> trim($jobs->job[$i]->JobType),
							'JobType'			=> trim($job['job_type']),
							// 'DateCreated'		=> strtotime($jobs->job[$i]->DateCreated),
							'DateCreated'		=> strtotime($job['job_date']),
							// 'Jobclass'			=> trim($jobs->job[$i]->Jobclass),
							// 'Jobclass'			=> trim($jobs->job[$i]->Jobclass),
							'Jobclass'			=> trim($job['job_class']),
							// 'Officetype'		=> trim($jobs->job[$i]->Officetype),
							'Officetype'		=> '',
							// 'Experiencelevel'	=> trim($jobs->job[$i]->Experiencelevel),
							'Experiencelevel'	=> '',
							// 'Key'				=> trim($jobs->job[$i]->Key),
							'Key'				=> '',
							// 'Category'			=> trim($jobs->job[$i]->ClientCategory),
							'Category'			=> '',
							'GoogleMap'			=> "<iframe src='".$fake_google_url."' width='420' height='400' frameborder='0' style='border:0' allowfullscreen></iframe>",
							'JobImageURL'		=> $job_img,
							'JobVideoURL'		=> $job_vid,
							'CommunityImageURL'	=> $loc_img,
							'JobDetailURL'		=> $job_detail_url,
							'IndApplyData'		=> $indeed_data,
							// the indeed apply continue url needs the full path of the job detail page
							'IndContinueUrl'	=> $indeed_cont,
							// 'Comments'			=> trim($jobs->job[$i]->Comments),
							'Comments'			=> '',
							// 'CommunityContent'	=> trim($jobs->job[$i]->CommunityContent),
							'CommunityContent'	=> '',
              /**
               * add in fields not present in brook data, but in taste
               */
              'Salary' => trim($job['job_salary']),
			);
		}

		return $return_jobs;
}

function get_taste_listings_detail( $jobs_page, $job_cnt, $pg, $jobs, $desc_len, $pg_links ) {

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
				<h3 class="jl-job-title" data-job="detail-'.$jobs[$i]['JobNumber'].'">'. $jobs[$i]['JobTitle'] .'</h3>
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