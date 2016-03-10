<?php
/**
 * SendEmailReCaptcha.php
 *
 * Zeta Producer Form-Mailer
 * 
 * $Id: SendEmailReCaptcha.php 36665 2016-02-09 14:20:04Z sseiz $
 */

require_once('debug.inc.php');
require_once('recaptcha/autoload.php'); 
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

// the response from reCAPTCHA
$captchaisverified = false;

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

$publickey = isset(Configuration::$conf_siteKey) ? decrypt( Configuration::$conf_siteKey , $cryptKey, $cryptKey, $bit_check) : "";
$privatekey = isset(Configuration::$conf_secretKey) ? decrypt( Configuration::$conf_secretKey , $cryptKey, $cryptKey, $bit_check) : "";

if( isset( $request["PHPSESSID"] ))
{
	session_id($request["PHPSESSID"] );
}

// Stage 1
if ( isset($request["f_receiver"]) )
{
	CatchUploads( $request );
	$formData = $request;
	$encodedFormData = base64_encode( serialize( $formData ));
	
	if ( isset( $request["g-recaptcha-response"] ) ) {
		// we entered the PHP with captcha already in the form Data. Need to handle this differently below.
		$enteredWithCaptcha = "yes";
	}
	else {
		DisplayCaptchaChallenge();		 	
	}
	
	if ( $enteredWithCaptcha == null ) {
		exit;
	}
}

// Stage 2
if ( isset( $request["g-recaptcha-response"] ) || isset( $request["pending"] )) {
	if ( ini_get('allow_url_fopen') ){
		$recaptcha = new \ReCaptcha\ReCaptcha($privatekey);
	}
	else{
		// If file_get_contents() or allow_url_fopen is locked down on your PHP installation to disallow
		// its use with URLs, then you can use the alternative request method instead.
		// This makes use of fsockopen() instead.
		$recaptcha = new \ReCaptcha\ReCaptcha($privatekey, new \ReCaptcha\RequestMethod\SocketPost());
	}
	$resp = $recaptcha->verify($request["g-recaptcha-response"], $_SERVER['REMOTE_ADDR']);

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
        if ( $resp->isSuccess() ){
        	DoProcessFormAndRedirect( $formData, $successPage, $errorPage );
        }
        else{
        	// captcha verification failed
        	echo'<div class="formvalidateerror" style="max-width: 460px; margin: 40px auto; font-family: helvetica, arial; color: #fff; background-color: red; padding: 6px 12px;">';
        	echo '<h2>Spam-Schutz / Spam-Protection</h2>';
	       	echo '<noscript>';
				echo '<p><strong>Um das Kontaktformular zu nutzen, aktivieren Sie bitte JavaScript!</strong></p>';
				echo '<p>In order to use this form, you need to activate JavaScript!</p>';
			echo '</noscript>';
			echo '<p>Das reCAPTCHA konnte nicht verifiziert werden.<br />Could not verify the reCAPTCHA.</p><p><strong>Fehler / Error:</strong><br />';
        	foreach ($resp->getErrorCodes() as $code) {
                switch ($code){
                	case "missing-input-secret":
                		echo "Der geheime Schlüssel fehlt. / The secret parameter is missing.<br />";
             			break;
                	case "invalid-input-secret":
                		echo "Der geheime Schlüssel ist falsch. / The secret parameter is invalid or malformed.<br />";
                		break;
                	case "missing-input-response":
                		echo "reCAPTCHA Antwort fehlt. / The response parameter is missing.<br />";
                		break;
                	case "invalid-input-response":
                		echo "reCAPTCHA Antwort is falsch oder ungültig. / The response parameter is invalid or malformed.<br />";
                		break;
                	case "invalid-json":
                		echo "Der reCAPTCHA-Server konnte nicht kontaktiert werden. Ist <tt>allow_url_fopen</tt> in Ihrer php.ini aktiviert? /<br /> Couldn't contact the reCAPTCHA-Server. Is <tt>allow_url_fopen</tt> enabled in your php.ini?<br />";
                		break;
                	default:
                		 echo '<tt>' , $code , '</tt> ';
                }
            }
			echo '</p><p style="margin-top: 20px; text-align: center;"><strong><a style="color: white;" href="javascript:history.back()">Zurück zum Formular</a> / <a style="color: white;" href="javascript:history.back()">Back to the Form</a></strong></p>';
			echo '</div>';
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