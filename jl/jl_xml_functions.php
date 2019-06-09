<?php
/*  xml_functions.php */

function build_contains( $fieldname, $val ) {
	return 'contains(translate('. $fieldname .', "ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"), "'. $val .'")';
}

function convert_spaces ( $str ) {
	$quote_flg = false;
	for ( $i = 0; $i < strlen($str); $i++ ) {
		if ( $str[$i] == '"' ) {
			$quote_flg = ( $quote_flg ) ? false : true;
		} else {
			if ( $str[$i] == ' ' && $quote_flg ) {
				$str[$i] = chr(253);
		  }
		}
	}
	
	$str_array = str_replace(chr(253), ' ', explode(' ', $str));
	$str_array = str_replace('"', '', $str_array);
	return $str_array;
}

function keywords2array( $keywords ) {
	// remove all multiple spaces
	$keywords = preg_replace('/\s+/', ' ', $keywords);

	// convert to lowercase
	$keywords = strtolower( $keywords );

	// remove or
	$keywords = str_replace( ' or', '', $keywords );

	return convert_spaces( $keywords );
}

function build_srch_str( $keywords, $fieldname ) {
	$connector = ' or ';
	$srch = keywords2array( $keywords );
	
	$srch_str = '';
	foreach( $srch as $val ) {
		$val = strtolower($val);
		// if first loop, then just create contains
		if ( empty( $srch_str ) ) {
			$srch_str = build_contains( $fieldname, $val );
		} else {
			if ( $val != 'AND' ) {
				$srch_str .= $connector . build_contains( $fieldname, $val );
				$connector = " or ";
			} else {
				$connector = " and ";
			}
		}
	}
	
	return $srch_str;
}
