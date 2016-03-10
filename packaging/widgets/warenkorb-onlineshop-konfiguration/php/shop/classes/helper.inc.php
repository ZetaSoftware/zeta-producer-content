<?php

function SetTimeZone() {
	date_default_timezone_set('Europe/Berlin'); //17.08.2012 - Uwe Keim
}

function AppendToUrl($url, $append) {
	$query = parse_url($url, PHP_URL_QUERY);
	return $url . ($query ? '&' : '?' ) . $append;
}

function format_currency( $value ) {
	$s = number_format( $value, Configuration::$conf_curr_decimals, Configuration::$conf_curr_comma, Configuration::$conf_curr_thousands ) . ' ' . utf8_encode(Configuration::$conf_currency);  
	return $s; 
}

function is_valid_email( $email ) {
	return preg_match("/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i", $email);
}

function decrypt( $encrypted_text, $key, $iv, $bit_check) {
	$cryptKeyString = null;
	$cryptIvString = null;
	
	foreach( $key as $val )
	{
		$cryptKeyString .= chr($val);	
	}
	
	foreach( $iv as $val )
	{
		$cryptIvString .= chr($val);	
	}
	
	$cipher = mcrypt_module_open(MCRYPT_DES,'','cbc','');
	mcrypt_generic_init($cipher, $cryptKeyString, $cryptIvString);
	$decrypted = mdecrypt_generic($cipher,base64_decode($encrypted_text));
	mcrypt_generic_deinit($cipher);
	
	// http://www.php.net/manual/de/function.mdecrypt-generic.php#107979
	$decrypted = preg_replace( "/\p{Cc}*$/u", "", $decrypted );
                
	$last_char=substr($decrypted,-1);
	for($i=0;$i<$bit_check-1; $i++)
	{
    	if(chr($i)==$last_char)
    	{    
        	$decrypted=substr($decrypted,0,strlen($decrypted)-$i);
         	break;
     	}
 	}
 	return $decrypted;
}

?>