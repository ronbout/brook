<?php

/* 
 * jl_form_functions.php
 * functions that build the input html
 */


define('CATEGORY_FILE', get_stylesheet_directory().'/resources/jl_categories.php' );
define('JOBKEY_FILE', get_stylesheet_directory().'/resources/jl_job_key.php' );
define('LOCATION_FILE', get_stylesheet_directory().'/resources/jl_locations.php' );
define('EXPERIENCE_FILE', get_stylesheet_directory().'/resources/jl_experience.php' );
define('JOBTYPE_FILE', get_stylesheet_directory().'/resources/jl_job_type.php' );
define('JOBCLASS_FILE', get_stylesheet_directory().'/resources/jl_job_class.php' );
define('OFFICETYPE_FILE', get_stylesheet_directory().'/resources/jl_office_type.php' );
define('DEPTCODE_FILE', get_stylesheet_directory().'/resources/jl_dept_code.php' );
define('STATE_FILE', get_stylesheet_directory().'/resources/jl_states.txt' );


function jl_load_select_options( $filename ) {
	// get the data from $filename and load rows into options as both value and display

	$options = file( $filename );

	echo '<option></option>';
	foreach ( $options as $option ) {
		$option = str_replace( array("\n", chr(13)), '', $option );
		if ( !empty( $option )) {
			echo '<option value="' . $option . '">' . $option . '</option>';
		}
	}
	return;
}

function jl_load_checkboxes( $filename, $checkname, $classname ) {
	// get the data from filename and load into checkboxes with name = checkname
	// and each input having class = classname
	
	$check_data = file($filename);
	
	foreach ( $check_data as $i => $checkval ) {
		$checkval = str_replace( array("\n", chr(13)), '', $checkval );
		if ( !empty( $checkval )) {
			echo '<li class="checkmark-li">
					<div class="checkMark">
						<input type="checkbox" id="' . $checkname . $i . '" name="' . $checkname . '" value="' . $checkval . '" class="' . $classname . '">
						<label for="' . $checkname . $i . '"></label>
					</div> 
					' . $checkval . '
				</li>';
		}
	}
	return;
}

function jl_get_locations() {
	jl_load_checkboxes( LOCATION_FILE, 'jl-location', 'jl-check-item' );
}

function jl_get_states() {
	$check_data = file(STATE_FILE);
	$checkname = 'state';
	$classname = 'jl-check-item';
	
	foreach ( $check_data as $i => $checkval ) {
		
		$checkval = str_replace( array("\n", chr(13)), '', $checkval );
		
		if ( !empty( $checkval )) {
			// break string into display value and return value
			$state_data = explode( '|', $checkval );
			$state = $state_data[0];
			$state_val = ( isset($state_data[1]) ) ? $state_data[1] : $state;
			echo '<li class="checkmark-li">
					<div class="checkMark">
						<input type="checkbox" id="' . $checkname . $i . '" name="' . $checkname . '" value="' . $state_val . '" class="' . $classname . '">
						<label for="' . $checkname . $i . '"></label>
					</div>
					' . $state . '
				</li>';
		}
	}
}

function jl_get_experience() {
	jl_load_checkboxes( EXPERIENCE_FILE, 'experiencelevel', 'jl-check-item' );
}

function jl_get_job_type() {
	jl_load_checkboxes( JOBTYPE_FILE, 'jobtype', 'jl-check-item' );
}

function jl_get_job_class() {
	jl_load_checkboxes( JOBCLASS_FILE, 'jobclass', 'jl-check-item' );
}

function jl_get_office_type() {
	jl_load_checkboxes( OFFICETYPE_FILE, 'officetype', 'jl-check-item' );
}

function jl_get_keys() {
	jl_load_checkboxes( JOBKEY_FILE, 'key', 'jl-check-item' );
}

function jl_get_dept_code() {
	jl_load_checkboxes( DEPTCODE_FILE, 'department', 'jl-check-item' );
}

function jl_get_categories() {
	jl_load_checkboxes( CATEGORY_FILE, 'clientcategory', 'jl-check-item' );
}