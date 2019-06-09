<?php
/*
* Template name: OldJobs
*/
// detect mobile on jobs page and redirect
if(is_mobile() && is_page( 'jobs' )) {
	$uri = $_SERVER['REQUEST_URI'];
	$newuri = str_replace("/jobs/","",$uri);
	$passuri = "http://career.staffingsoft.com/careers/brookdalejobs.html" . $newuri;
	echo '<script> window.location = "' . $passuri . '"; </script>';
} else {
	genesis();
}


