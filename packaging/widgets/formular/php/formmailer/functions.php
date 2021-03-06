<?php

function DoProcessFormAndRedirect( 	$formData,
        							$successPage,
        							$errorPage )
{
	global $antispamField;
	
	if ( isset($formData["url"] )) {
		$antispamField = trim($formData["url"]);
		if ( !empty($antispamField) ) {
			// we got spammed, but won't tell and instead redirect to success page without sending any mail
			header("Location: $successPage");	
			exit;
		}
	}

	// https://github.com/PHPMailer/PHPMailer/blob/master/examples/exceptions.phps
	try {
		DoSendEmail( $formData );
		header("Location: $successPage");	

		// 2015-04-12, Uwe Keim: Im Fehlerfall _nicht_ mehr auf die Fehlerseite weiterleiten,
		// da ansonsten die detaillierte Fehlermeldung nicht mehr zu sehen ist.

	} catch (phpmailerException $e) {
		error_log( $e->errorMessage() ); // http://stackoverflow.com/a/3531852/107625
		echo $e->errorMessage();
		die;
	} catch (Exception $e) {
		error_log( $e->getMessage() ); // http://stackoverflow.com/a/3531852/107625
		echo $e->getMessage();
		die;
	}
}

function CatchUploads( &$formData )
{	
	global $uploadBaseDir, $maxAgeUploads, $scriptName, $uploadedFiles;
	  
	$uploadDir = $uploadBaseDir . "/upload_" . uniqid() . "/";
	$uploadDirRel = StripLeft( $uploadDir, $uploadBaseDir );
	$scriptCmd = $scriptName; 
	
	CleanupOldUploads( $uploadBaseDir, $maxAgeUploads );
	
	if ( count( $_FILES ) >= 1 )
	{	
		if ( isset ( $formData["sc"] ))
		{
			$scriptCmd .="?sc";
		}	
		
		foreach ( $_FILES as $key  => $file )
		{
			if( $file["error"]  == 0)
			{
				$uploadedFiles = true;
				if ( !is_dir( $uploadDir ))
				{
					mkdir( $uploadDir );
				}
				//remove any non ascii chars from filename
				$sanitizedFileName = preg_replace('/[^a-zA-Z0-9-_ .]/','', $file['name']);
				//make sure non one can upload executable .php files
				$sanitizedFileName = preg_replace('"\.php$"', '.phps', $sanitizedFileName);
				
				move_uploaded_file($file['tmp_name'], $uploadDir . $sanitizedFileName);
				
				$formData[$key] = '<a href="' . StripRight( currPageURL(), $scriptCmd ) . $uploadDirRel . rawurlencode($sanitizedFileName) . '">' . $sanitizedFileName . '</a>';
			}
		}
	}
}

// Delete occurance of $remove from left of $s
function StripLeft($s, $remove)
{
	$len = strlen($remove);
	if (strcmp(substr($s, 0, $len), $remove) === 0)
	{
			$s = substr($s, $len);
	}
	return $s;
}
// Delete occurance of $remove from right of $s
function StripRight($s, $remove)
{
	$len = strlen($remove);
	if (strcmp(substr($s, -$len, $len), $remove) === 0)
	{
			$s = substr($s, 0, -$len);
	}
	return $s;
}

function CleanupOldUploads( $baseDir, $olderThanDays )
{
	$deltaSeconds = ( $olderThanDays * 60 * 60 * 24 );
	
	if ( is_dir( $baseDir ))
	{
		$dirContent = scanDirEx( $baseDir, "upload*" );
		
		foreach ( $dirContent as $fileOrDir )
		{
			$fullFilePath = $baseDir . "/" . $fileOrDir;
        	
            if ( is_dir ( $fullFilePath ))	
            {
            	$changeTime = filemtime( $fullFilePath );
            	
            	if ( ($changeTime + $deltaSeconds) < time() )
            	{
            		//echo "DIR '" . $fullFilePath . "' would be removed."; 
            		rrmdir( $fullFilePath );
            	}
            }
		}
    }
}

function ValidateLoadMainFormData( $formData )
{
	global $error;
	global $successPage;
	global $errorPage;
	global $receiverEmailAddress;
	global $cryptKey, $bit_check;
	
	if ( isset($formData["f_success"] ))
	{
		$successPage = $formData["f_success"];
	}
	else 
	{
		$error .="Keine Erfolgsseite angegeben. Prüfen Sie die Formularkonfiguration.</br>";
	}
	if ( isset($formData["f_error"] ))
	{
		$errorPage = $formData["f_error"];
	}
	else 
	{
		$error .="Keine Fehlerseite bei Übermittlungsfehler angegeben. Prüfen Sie die Formularkonfiguration.</br>";
	}
	
	if ( isset($formData["f_receiver"] ))
	{
		$receiverEmailAddress = decrypt( $formData["f_receiver"] , $cryptKey, $cryptKey, $bit_check);
		$arrReceiverEmail = explode( ",", $receiverEmailAddress );
		
		foreach ( $arrReceiverEmail as $testEmail ){
			if( !isValidEmail( $testEmail ) ){
				$error .="Die E-Mail-Adresse des Formularempfängers ist ungültig. Prüfen Sie die Formularkonfiguration</br>";
				break; 
			}
		}
	}
	else 
	{
		$error .="Kein Formularempfänger angegeben. Prüfen Sie die Formularkonfiguration</br>";
	}
	
	return $error;
}

function DisplayCaptchaChallenge()
{
	global $error, $publickey, $encodedFormData;
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	  <head>
		<title>Spam-Schutz</title>
	  </head>
	  <body>
	  	<style>
			body, input, submit
			{
				font-family: Verdana;
				font-size: 10pt;
			}
			h1
			{
				font-size: 16pt;
				font-weight: bold;
				font-family: Verdana;
			}
	 	</style>
	  	<script type="text/javascript">
			var RecaptchaOptions = 
				{   
					lang : 'de',
					theme : 'clean' 
				};
		</script>
		<h1>Spam-Schutz</h1>
		<p style="width:600px;">Um sicherzustellen, dass dieser Formularservice nicht missbräuchlich verwendet wird, geben Sie bitte die 2 Wörter im Feld unten ein und bestätigen Sie mit "Absenden". Ihre Nachricht wird dann umgehend an den Empfänger weitergeleitet.</p>
	    <form action="" method="post">
	    <input type="hidden" name="formData" value="<?php echo $encodedFormData ?>" />
	    <input type="hidden" name="pending" value="true" />
		<?php echo recaptcha_get_html($publickey, $error); ?>
	    <br/>
	    <input type="submit" value="Absenden" />
	    </form>
	  </body>
	</html>
<?php
} 

function DoSendEmail( 
			$formData )
{
	global $cryptKey, $bit_check, $maxAgeUploads, $uploadedFiles;

	mb_internal_encoding("UTF-8");
	
	$receiverEmail = decrypt( $formData["f_receiver"] , $cryptKey, $cryptKey, $bit_check);
	$arrReceiverEmail = explode( ",", $receiverEmail );
	
	$senderEmail = $arrReceiverEmail[0];
	$senderName = "Formular";
	$searchSenderName = true;
	$searchSenderEmail = true;

	$subject = $formData["f_title"];
	$subject = expandFieldnamePlaceholders($subject, $formData);
	
	$css = file_get_contents('css.inc');
	$emailTemplate = isset(Configuration::$conf_email_template) ? Configuration::$conf_email_template : "<p>Es wurde keine Vorlage eingegeben!<br />No template has been set!</p>";
	$body[] = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\">";
    $body[] = "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><title></title><style type=\"text/css\">" . $css . "</style></head><body>";
    //$body[] = "<h1>Ein Formular wurde Ihnen von Ihrer Website gesendet</h1>";
    //$body[] = "<p>Jemand hat Ihnen ein Formular mit den folgenden Werten gesendet.</p>";
    
    $formbody[] = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
    
	for ($i = 1; $i <= 1000; $i++) 
	{
		if ( isset( $formData["NAME".$i] ))
		{
			$name = $formData["NAME".$i];
			
			if ( $name === "(Beschreibungstext)" )
			{
				continue;
			}
					
			if( $name == "——————————————" )
			{
					$name = "&nbsp;";
					$value = "&nbsp;";	
			}
			else
			{			
				if( isset( $formData["F".$i] ))
				{
					if (is_array($formData["F".$i])) {
						$value = nl2br(implode( ", ", $formData["F".$i] ));
					}
					else {
						$value = nl2br( $formData["F".$i] );
					}
				}
				else
				{
					$value = "-";
				}
	
				if (is_array($value)) 
	        	{
	        		$value = implode( ", ", $value );
	        	}
	        	if ( $searchSenderEmail &&
	        		 (stripos($name, "email") !== false ||
					 stripos($name, "e-mail") !== false ))
				{
					if ( !empty($value) && isValidEmail($value) ) {
						$senderEmail = $value;
					}
					$searchSenderEmail = false;
				}
				
				if ( $searchSenderName &&
					 stripos($name, "name") !== false )
				{
					if ( !empty($value) ) {
						$senderName = $value;
					}
					$searchSenderName = false;
				}
			}
        				
			$formbody[] = "<tr><th class=\"left\">" . $name. "</th><td class=\"right\">" . $value . "</td></tr>";
		}
		else
		{
			break;
		}
	}

	// --
	
	$deleteDate = strtotime("+$maxAgeUploads day");        
	$deleteDate = date('d.m.Y', $deleteDate);
	$formbody[] = "</table>";
	// replace the text makro in the template with the transferred form fields
	$emailTemplate = expandFieldnamePlaceholders($emailTemplate, $formData);
	$body[] = str_replace("[FORM_TABLE]", implode("\r\n",$formbody), $emailTemplate);
	$body[] = "<br/>";
	//$body[] = "<hr/>";
	if ( $uploadedFiles ) {
		$body[] = "<p class=\"footer\">* Dateien werden nach $maxAgeUploads Tagen, ab $deleteDate automatisch vom Server gelöscht.<br/>";
	}
	//$body[] = "<p class=\"footer\">Besucher-IP-Adresse: " . $_SERVER["REMOTE_ADDR"] . "<br/>";
	//$body[] = "Besucher-Hostname: " . gethostbyaddr( $_SERVER["REMOTE_ADDR"] ) . "<br/>";
    $body[] = "</body></html>";

	// --

	// Passing true to the constructor enables the use of exceptions for error handling.
	$mail = new PHPMailer(true);

	$mail->setLanguage('de', 'language');
	$mail->CharSet = 'utf-8';

	$mail->isHTML(true); 
	$mail->From      = $arrReceiverEmail[0];
	$mail->FromName  = $senderName;
	$mail->Subject   = $subject;
	$mail->Body      = implode("\r\n",$body);
	// generate TEXT-Part of the mail to lower spam scores
	$mail->AltBody   = "Alle Informationen dieser E-Mail finden Sie im HTML-Teil dieser E-Mail. \n\nAll content is contained in the HTML-part of this email.";
	// Suppoert multiple komma-separated receipients
	foreach($arrReceiverEmail as $remail)
	{
	   $mail->AddAddress($remail);
	}
	$mail->addReplyTo( $senderEmail, $senderName );

	$mail->Send();

	/*
    $header = array();
	$header[] = "From: =?UTF-8?B?" . base64_encode($senderName) . "?= <" . $receiverEmail . ">";
	$header[] = "Reply-To: =?UTF-8?B?" . base64_encode($senderName) . "?= <" . $senderEmail . ">";
	$header[] = "MIME-Version: 1.0";
	$header[] = "Content-Type: text/html; charset=UTF-8";
		
    if( mail( $receiverEmail, 
	      	   mb_mime_header($subject, "utf-8"), 
	    	   implode("\r\n",$body),
	    	   implode(PHP_EOL, $header)))
	{
		return true;	   	  	
	}
	else 
	{
		return false;
	}
	*/
}

function decrypt( $encrypted_text,
				  $key,
				  $iv,
				  $bit_check)
{
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

function rrmdir($dir) 
{ 
   if (is_dir($dir)) { 
     $objects = scandir($dir);
      
     foreach ($objects as $object) 
     { 
       if ($object != "." && $object != "..") 
       { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
       } 
     }
     
     reset($objects); 
     rmdir($dir); 
   } 
}

function scanDirEx( $path='.', $mask='*' )
{  
	$sdir = array();
    $entries = scandir($path);
     
    foreach ($entries as $i=>$entry) { 
        if ($entry!='.' && $entry!='..' && fnmatch($mask, $entry) ) { 
            $sdir[] = $entry; 
        } 
    } 
    return ($sdir); 
}

function isValidEmail( $email )
{
	return preg_match("/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i", $email);
}

function mb_mime_header($string, $encoding=null, $linefeed="\r\n") 
{
   if(!$encoding) $encoding = mb_internal_encoding();
   $encoded = '';
 
  while($length = mb_strlen($string)) 
  {
     $encoded .= "=?$encoding?B?"
              . base64_encode(mb_substr($string,0,24,$encoding))
              . "?=$linefeed";
 
    $string = mb_substr($string,24,$length,$encoding);
   }
 
  return $encoded;
}

function currPageURL() 
{
	$pageURL = 'http://';
	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	
 return $pageURL;
}

function expandFieldnamePlaceholders( $inputString, $formData){
	//xdebug_break();
	$resultString = $inputString;
	preg_match_all('/\[[^\]]+\]/', $inputString, $matches);
	
	foreach ($matches[0] as $value) {
		$fieldDisplayname = substr($value, 1, -1); 					// strip out leading "[" and trailing "]" to get the form-fields name
		$fieldname = array_search($fieldDisplayname, $formData);
		preg_match('/[0-9]+$/', $fieldname, $matches); 				// gets the sequential number of the fieldname
		$fieldname = "F" . $matches[0]; 							// e.g. "F10"
		
		if ( isset($formData[$fieldname]) ){
			$resultString = str_replace($value, $formData[$fieldname], $resultString);
		}
	}
	
	return $resultString;
}

?>