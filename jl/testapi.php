<!-- template.html  -->
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Test SS API</title>
	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<style>
		p {
			width: 1200px;
		}
	</style>
</head>
<body>
<pre>
<?php 


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
		$out_string .= urlencode($key) . '=' . urlencode($value);
	}
	
	return $out_string;
}


$url = 'https://career.StaffingSoft.com/careers/ssjobsapi.ashx';

//$url = 'http://www.google.com';
$post_data = array(
	'si' => '013',
	'ui' => 'bslapiuser',
	'pwd' => 'm!A9%\R\a5Qtj',
	'cwid' => '1',
	//'id' => '151081',
	//'keywords' => "HWDSEgreSC00076",
	//'jobtype' => 'Full Time',
	//'jobclass' => 'Salary',
	//'clientcategory' => 'Therapy',
	//'Experiencelevel' => 'manager',
	//'city' => 'dallas',
	//'state' => 'tx',
	//'zip' => '78598',
	//'radius' => '5',
	'pagenumber' => '1',
	'recordperpage' => '20',
);

$post_string = build_post_string($post_data);
//$post_string = 'keywords=technician&state=al%2Caz%2Cca&jobtype=Full+Time%2CPart+Time&key=BAH&pagenumber=1&recordperpage=10&si=013&ui=bslapiuser&pwd=m%21A9%25%5CR%5Ca5Qtj&cwid=1';


//$post_string ="si=013&ui=bslapiuser&pwd=m%21A9%25%5CR%5Ca5Qtj&cwid=1&city=dallas&state=tx&radius=5&pagenumber=1&recordperpage=10";
//$post_string = "keywords=%5C%22%5C%27%2F%5C%22vice+president%2F%5C%27%5C%22&zip=45236&radius=75&pagenumber=1&recordperpage=10&&si=013&ui=bslapiuser&pwd=m%21A9%25%5CR%5Ca5Qtj&cwid=1";

echo '<p>', $post_string, '</p>';

$ret_xml = curl_load_file( $url, $post_string);

$jobs_xml = simplexml_load_string($ret_xml);

//echo '<p>Count: ', $jobs_xml->count(), '</p>';

var_dump($ret_xml);
		
?>


</pre>
</body>
</html>