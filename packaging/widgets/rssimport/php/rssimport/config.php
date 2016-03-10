<?php
define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
define('MAGPIE_CACHE_DIR', './assets/php/RSS_CACHE');
function getBody($item)
{
	$key = 'content';
	if ( array_key_exists($key, $item) ) 
	{
		$key2 = 'encoded';
		if( array_key_exists($key2, $item[/**/$key]) ) return $item[/**/$key][/**/$key2];

		return $item[/**/$key];
	}
	
	$key = 'description';
	if ( array_key_exists($key, $item) ) return $item[/**/$key];
	
	$key = 'atom_content';
	if ( array_key_exists($key, $item) ) return $item[/**/$key];
	
	return "";
}
?>