<?php
/**
 * Webshop master entry point for the Zeta Producer Onlineshop module
 *
 * $Id: shop.inc.php 32282 2015-09-16 11:06:43Z sseiz $
 */

// 2013-11-10, Uwe Keim: Scheint, als ob er von http://tutorials.lemme.at/mvc-mit-php/ das Tutorial genommen hat.

require_once('debug.inc.php');
require_once('config.inc.php');

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

// turn off when done developing TODO
if( DebugConfiguration::$errorDisplay )
{
	error_reporting(E_ALL);
	ini_set('display_startup_errors', 'On');
	ini_set('display_errors', 'On');
}

require_once('classes/controller.php');
require_once('classes/view.php');
require_once('classes/model.php');
require_once('classes/paypal.inc.php');
require_once('classes/mailer/PHPMailerAutoload.php');

ini_set('error_log', 'phperrors.log');

// Little workaround because headers could be already sent 
// due to this script is implemented in a zp page.
ini_set('display_errors', 'Off');

session_start();

if( DebugConfiguration::$errorDisplay )
{
	ini_set('display_errors', 'On');
}

// Neu, auch Session in das $request-Objekt hinzufügen, damit
// die Formulardaten zwischen PayPal-Aufrufen erhalten bleiben.
// Dabei die 'action' stets löschen, sonst springt jedes Mal wieder
// zu PayPal.
$_SESSION['action'] = null;

// Merge get and post arrays to one request.
$request = array_merge($_SESSION, $_GET, $_POST);  

$controller = new Controller($request);   

echo $controller->Display(); 	
?>