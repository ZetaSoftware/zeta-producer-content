<?php

function SetTimeZone()
{
	date_default_timezone_set('Europe/Berlin'); //17.08.2012 - Uwe Keim
}

function AppendToUrl($url, $append)
{
	$query = parse_url($url, PHP_URL_QUERY);
	return $url . ($query ? '&' : '?' ) . $append;
}

function format_currency( $value )
{
	$s = number_format( $value, Configuration::$conf_curr_decimals, Configuration::$conf_curr_comma, Configuration::$conf_curr_thousands ) . ' ' . utf8_encode(Configuration::$conf_currency);  
	return $s; 
}

function is_valid_email( $email )
{
	return preg_match("/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i", $email);
}

?>