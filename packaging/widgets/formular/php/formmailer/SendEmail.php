<?php

/**
 * SendEmail.php
 *
 * Zeta Producer Form-Mailer
 * 
 * $Id: SendEmail.php 36665 2016-02-09 14:20:04Z sseiz $
 */

require_once('debug.inc.php');
require_once('recaptchalib.php'); 
require_once('mailer/PHPMailerAutoload.php');
require_once('functions.php');

if( DebugConfiguration::$errorDisplay ) {
	error_reporting(E_ALL);
	ini_set('display_startup_errors', 'On');
	ini_set('display_errors', 'On');
	
	ini_set("log_errors", 1);
	ini_set('error_log', 'phperrors.log');
}
else {
	ini_set('display_errors', 'Off');
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");				// Past date
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// Modified
header("Cache-Control: no-store, no-cache, must-revalidate");	// HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");										// HTTP/1.0

session_start();

$scriptName = basename($_SERVER["SCRIPT_NAME"]);

// reCAPTCHA keys
$publickey = "6LdqbskSAAAAABotr2Ji4pC9xFS16Mv4zlDbOLV1";
$privatekey = "6LdqbskSAAAAAJ2PU_dNQScTCYuNELTaXDBMB8xv";
// the response from reCAPTCHA
$resp = null;

// Encryption
$bit_check=8;
$cryptKey = array( 0x0E,
					0x41,
					0x6A,
					0x29,
					0x94,
					0x12,
					0xEB,
					0x63 );

$uploadBaseDir = dirname( __FILE__ );
// Max age of uploaded files - In days.
$maxAgeUploads = 30;
$uploadedFiles = false;
$request = array_merge($_GET, $_POST);

$error = null;
$successPage = null;
$errorPage = null;
$enteredWithCaptcha = null;
$receiverEmailAddress = null;
$antispamField = null;
$formData = array();

// read config file specific to the instance of the sent form
if ( isset($request["f_id"]) ){
	require_once(intval($request["f_id"]) . '_config.inc.php');
}
if( isset( $request["PHPSESSID"] ))
{
	session_id($request["PHPSESSID"] );
}
if (!isset($_SESSION['verifiedcaptcha']))
{
   $_SESSION['verifiedcaptcha'] = "";
}

// Captcha verification via Ajax
if ( isset($request["verifycaptcha"]) ) {
	// check if captcha fields were sent
	if( !isset($request["recaptcha_challenge_field"]) || !isset($request["recaptcha_response_field"])) {
		echo "NOK: Missing captcha challenge and response!";
		exit;
	}
	elseif ( $_SESSION['verifiedcaptcha'] == $request["recaptcha_challenge_field"] . $request["recaptcha_response_field"] ) {
		echo "OK";
		exit;
	}
		
	$resp = recaptcha_check_answer ($privatekey,
                                  $_SERVER["REMOTE_ADDR"],
                                  $request["recaptcha_challenge_field"],
                                  $request["recaptcha_response_field"]);
  
  if ( $resp->is_valid ) {
  	$_SESSION['verifiedcaptcha'] = $request["recaptcha_challenge_field"] . $request["recaptcha_response_field"];
  	echo "OK";
  }
  else {
  	echo "NOK: " . $resp->error;
  }
	exit;
}

// Stage 1
if ( isset($request["f_receiver"]) )
{
	CatchUploads( $request );
	$formData = $request;
	$encodedFormData = base64_encode( serialize( $formData ));
	
	if( isset( $request["sc"] )) {
		//ValidateLoadMainFormData( $formData );
		$error = ValidateLoadMainFormData( $formData );
		
		if ( $error == null ) {	
			DoProcessFormAndRedirect( 	$formData,
        									$successPage,
        									$errorPage );
    }
    else {
    	echo $error;
    	exit;
    }
	}
	else 
	{
		if ( isset( $request["recaptcha_response_field"] ) ) {
			// we entered the PHP with captcha already in the form Data. Need to handle this differently below.
			$enteredWithCaptcha = "yes";
		}
		else {
			DisplayCaptchaChallenge();		 	
		}
	}	
	
	if ( $enteredWithCaptcha == null ) {
		exit;
	}
}

// Stage 2
if ( isset( $request["recaptcha_response_field"] ) ||
	 isset( $request["pending"] )) 
{
	if ( $enteredWithCaptcha == null ) {
		$encodedFormData = $request["formData"];
		$formData = unserialize( base64_decode( $request["formData"] ));
	}
	else {
		// we entered the PHP with captcha already in the form Data so the request doesn't yet include formData
		$formData = $request;
	}
	$error = ValidateLoadMainFormData( $formData );
	
	if ( $error == null )
	{
        //check if we already verified the captcha once (a captcha can only be solved once)
        if ( $_SESSION['verifiedcaptcha'] == $request["recaptcha_challenge_field"] . $request["recaptcha_response_field"] ) {
        	DoProcessFormAndRedirect( $formData, $successPage, $errorPage );
        }
        else {
					$resp = recaptcha_check_answer ($privatekey,
																					$_SERVER["REMOTE_ADDR"],
																					$request["recaptcha_challenge_field"],
																					$request["recaptcha_response_field"]);
	
					if ( $resp->is_valid ) 
					{
							DoProcessFormAndRedirect( $formData, $successPage, $errorPage );
					} 
					else 
					{
									$error = $resp->error;
									DisplayCaptchaChallenge();
					}
        }
	}
	else
	{
		echo $error;
	}
	exit;
}
echo "Illegal script call.";
exit;
// ========================================================

?>