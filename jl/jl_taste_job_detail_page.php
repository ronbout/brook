<?php
/*
 * jl_taste_job_detail_page.php
 * The HTML and php used to create the job detail display
 */

function get_listings_detail_html($job) {
  
	global $crawler_flag, $query_flag, $q_string;

	// if search engine crawler, must render all of it now 
	// as it will be displayed directly and not through a click
	if ($crawler_flag) {
		$job_image = '<img alt="'. $job['JobTitle'].'- Brookdale Careers image" src="' . $job['JobImageURL']. '">';
		$job_video = '<iframe width="300" height="169" src="http://www.youtube.com/embed/'. $job['JobVideoURL']. '?autoplay=0"></iframe>';
		$community_image = '<img alt="' . $job['Location'].' Brookdale Community image" src="' . $job['CommunityImageURL']. '">';
		$script_markup = build_json_markup($job);
	} else {
		$job_image = '';
		$job_video = '';
		$community_image = '';
		$script_markup = '';
	}
	$back_link = '&lsaquo; Search More Jobs ';
	//$back_link = '&lsaquo; Back to Search Results ';
	?>
<article id="detail-<?php echo $job['JobNumber']; ?>" data-vid="<?php echo str_replace('"',"'",$job['JobVideoURL']); ?>"
  data-job_img="<?php echo str_replace('"',"'",$job['JobImageURL']); ?>"
  data-loc_img="<?php echo str_replace('"',"'",$job['CommunityImageURL']); ?>"
  data-detail_url="<?php echo $job['JobDetailURL']; ?>" data-job_title="<?php echo $job['JobTitle'];?>"
  <?php echo build_data($job['IndApplyData']); ?> data-indeed-apply-onclick="jlPostApply"
  data-indeed-apply-continueURL="<?php echo $job['IndContinueUrl'];?>" data-href="<?php echo $job['ApplyURL'];?>">

  <?php echo $script_markup; ?>
  <div class="left-50">
    <div class="breadcrumb"><?php echo $back_link; ?></div>
    <div>
      <p class="job-type"><?php echo $job['JobType']; ?></p>
      <h1><?php echo $job['JobTitle']; ?></h1>
      <?php echo $job['Opportunity']; ?>
    </div>
    <div class="job-nav">
      <div class="btn btn-green social-button">Share Job</div>
      <div class="btn btn-green map-button" data-map="<?php echo $job['GoogleMap'];?>">Show on map</div>
      <div class="btn btn-orange apply-button" data-job="detail-<?php echo $job['JobNumber'];?>">Apply Now</div>
    </div><!-- end of job-nav -->
  </div> <!-- end of left -->

  <div class="right-50">
    <div class="img-assoc"><?php echo $job_image; ?>
      <div class="job-nav">
        <div class="detail-btn">
          <div class="btn btn-green social-button">Share Job</div>
          <div class="btn btn-green map-button" data-map="<?php echo $job['GoogleMap'];?>">Show on map</div>
          <div class="btn btn-orange apply-button" data-job="detail-<?php echo $job['JobNumber'];?>">Apply Now</div>
        </div>
      </div><!-- end of job-nav -->
    </div>
    <div class="left-50-summary job-summary">
      <?php //				<div class="job-summary"> ?>
      <h4>Job Summary</h4>
      <h5>Job Title</h5>
      <p><?php echo $job['JobTitle']; ?></p>
      <h5>Location</h5>
      <p><?php echo $job['Location']; ?></p>
      <h5>Job Type</h5>
      <p><?php echo $job['JobType']; ?></p>
      <h5>Job Class</h5>
      <p><?php echo $job['Jobclass']; ?></p>
      <h5>Salary</h5>
      <p><?php echo $job['Salary']; ?></p>
      <h5>Date Created</h5>
      <p><?php echo date ('M j, Y', $job['DateCreated']); ?></p>
      <div class="facility map-button" data-map="<?php echo $job['GoogleMap'];?>">
        <h4>Location</h4>
        <div class="loc-image"></div><?php echo $community_image; ?>
        <h5><?php echo $job['Location']; ?></h5>
      </div>
      <?php //				</div> ?>
      <?php 
					if ($job['CommunityContent'] != '' ) {
				?>
      <div class="job-communityContent">
        <div class="job-communityContent-text">
          <?php echo $job['CommunityContent']; ?>
        </div>
      </div>
      <?php 
					} 
				?>
    </div>
    <div class="right-50-video job-video"><?php echo $job_video;?>
      <h4>What our people say</h4>
      <div class="video-div"></div>
      <?php 
					if ($job['Comments'] != '' ) {
				?>
      <div class="job-comments">
        <h4>Required Reading</h4>
        <div class="job-comments-text">
          <?php echo $job['Comments']; ?>
        </div>
      </div>
      <?php 
					} 
				?>
      <div class="usefulinfo">
        <h4>Application Tips</h4>
        <?php
	include(get_stylesheet_directory().'/jl/jl_application_tips.php');
?>
      </div>
    </div>
  </div><!-- end of right -->

</article>
<?php
}

/**
 * build_data() will build the indeed data attrs from
 * an associative array.  These will later be put into 
 * a span for the Indeed Apply button
 */
function build_data($data) {
	$data_str = '';
	foreach($data as $key => $value) {
		$data_str .= "data-$key=\"$value\" ";
	}
	return $data_str;
}

/**
 * build_json_markup() will build a string that contains JSON-LD
 * JobPosting markup.  It will only be used when a crawler is 
 * looking at the page as otherwise it would slow down the load
 * for a human user.
 */
function build_json_markup($job) {
	// break up the location
	$locArray = explode(',', $job['Location']);
	$json_array = array(
			'@context' => 'http://schema.org/',
			'@type' => 'JobPosting',
			'title' => $job['JobTitle'],
			'jobLocation' => array(
					'@type' => 'Place',
					'address' => array(
							'@type' => 'PostalAddress',
							'addressLocality' => isset($locArray[0]) ? $locArray[0] : '',
							'addressRegion' => isset($locArray[1]) ? $locArray[1] : '',
							'postalCode' => isset($locArray[2]) ? $locArray[2] : ''
					),
			),
			'hiringOrganization' => array(
					'@type' => 'Organization',
					'name' => 'Brookdale',
					'sameAs' => 'http://www.brookdalecareers.com'
			),
			'description' => $job['Opportunity'],
			'datePosted' => date ('M j, Y', $job['DateCreated']),
			'employmentType' => $job['JobType'],
			'identifier' => $job['JobNumber'],
			'qualifications' => $job['RequiredSkills'],
			'jobBenefits' => $job['Benefits'],
			'image' => $job['JobImageURL']

	);
	
	$json_str = '<script type="application/ld+json">' . json_encode($json_array) . '</script>';
	return $json_str;
}

/*


*/