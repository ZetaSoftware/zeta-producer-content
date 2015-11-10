<?php
/**
 * Online-CMS master entry point.
 *
 * $Id: cms.inc.php 32282 2015-09-16 11:06:43Z sseiz $
 */

$cmszipfile = "zpocmsmodule.zip";

if ( file_exists( dirname( __FILE__ ) . "/" . $cmszipfile ))
{
	require_once('pclzip.lib.php');
	
 	$archive = new PclZip( dirname( __FILE__ ). "/" . $cmszipfile);
 	$list = $archive->extract(PCLZIP_OPT_PATH, dirname(__FILE__));
 	
	if ( $archive->errorCode() != 0 )  
	{    
		die( "Fehler. Das Online-CMS-Modul konnte nicht installiert werden. " . $archive->errorInfo(true) );
	}
	else
	{
		unlink( dirname( __FILE__ ) . "/" . $cmszipfile );
	}
}

require_once('etc/helper.php');
require_once('config.inc.php');
require_once('classes/View.php');
require_once('classes/Controller.php');
require_once('classes/Model.php');

ini_set('default_charset', 'utf-8');
//ini_set('session.cache_limiter', 'nocache');

// Little workaround because headers could be already sent 
// due to this script is implemented in a ZP page.
ini_set('display_errors', 'Off');
ini_set('error_reporting', 0);
// Send a few headers to avoid caching

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Past date
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

if(!isset($_SESSION)) 
{ 
	session_start(); 
} 
//ini_set('error_reporting', 0);

ini_set('error_log', 'phperrors.log');
ini_set('display_errors', 'Off');
ini_set('error_reporting', 0);

// Merge get and post arrays to one request.
$request = array_merge($_GET, $_POST);  
if( isset( $request["PHPSESSID"] ))
{
	session_id($request["PHPSESSID"] );
}

$controller = new Controller($request);   

echo $controller->Display();
?>