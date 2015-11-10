<?php
require_once('helper.php'); 

function err($code,$msg) {
	sendJsonToBrowser(array('error' => array('code'=>intval($code), 'msg' => $msg)));
}

function doesMatchGlobalWildcards($fileName) {
	global $includeFilter;
	
	if ( !$includeFilter || strlen($includeFilter)==0 || $includeFilter=='*' || $includeFilter=='*.*' ) return true;
	
	$parts = strpos($includeFilter, ';') ? explode( ';', $includeFilter ) : array($includeFilter);
	
	foreach($parts as $part)  {
		$part = trim($part);

		if ( strlen($part)==0 ) continue;
		if( $part=='*' || $part=='*.*' ) return true;
		
		$r = my_fnmatch( $part, $fileName );
		
		if ( $r ) return true;
	}
	
	return false;
}
?>