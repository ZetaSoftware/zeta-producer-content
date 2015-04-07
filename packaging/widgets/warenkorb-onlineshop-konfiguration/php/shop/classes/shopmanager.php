<?php
/**
 * Class ShopManager
 *
 * Provides methodes for working with the shopping basket.
 * 
 * $Id: shopmanager.php 20880 2013-02-13 12:46:04Z sseiz $
 */
class ShopManager
{
	/**
	* Sends an email with the ordered positions to the reseller and the costumer.
	* 
	* @param Array $request
	* @param Array $articles The ordered articles.
	*/
	public static function SendOrderEmail(
		$request,
		$articles,
		$orderdate)
	{
		$nl = "<br/>";
		
		$name = trim( $request['name'] );
		$email = trim( $request['email'] );
		$address = trim( $request['address'] );
		
		// handle some values here in case default isn't set yet	
		$shopname = isset(Configuration::$conf_shopname) ? Configuration::$conf_shopname : "Online Shop";
		$subject = isset(Configuration::$conf_email_subject_shop) ? Configuration::$conf_email_subject_shop : "Neue Bestellung - [SHOPNAME]";
		$subject_customer = isset(Configuration::$conf_email_subject_customer) ? Configuration::$conf_email_subject_customer : "Bestellbestätigung -  [SHOPNAME]";
					
		$viewArticleTable = new View();
		$viewArticleTable->SetTemplate('articletable');
		
		ShopManager::AssignShopConfiguration($viewArticleTable);
		$viewArticleTable->Assign('articles', $articles);
		$viewArticleTable->Assign('allowRemove', false);       
		$viewArticleTable->Assign('allowEdit', false);    
		$viewArticleTable->Assign('showPosNo', true);    
		$viewArticleTable->Assign('showArticleNo', true);    
		
		// Platzhalter.
		$subject = str_replace("[SHOPNAME]", $shopname, $subject);
		$subject_customer = str_replace("[SHOPNAME]", $shopname, $subject_customer);
		
		// Compose E-Mail to Shop Owner
		$text  = '<html><head></head><body style="font-family: Arial, Helvetica, Sans Serif";>';
		
		SetTimeZone();

		$tmp = utf8_encode(Configuration::$conf_email_to_shop);
		$tmp = str_replace("[DATUM]", $orderdate, $tmp);
		$tmp = str_replace("[NAME]", $name, $tmp);
		$tmp = str_replace("[ADRESSE]", nl2br($address), $tmp);
		$tmp = str_replace("[EMAIL]", $email, $tmp);
		$tmp = str_replace("[ZAHLUNGSWEISE]", ShopManager::GetPaymentMethodText($request['payment_method']), $tmp);
		$tmp = str_replace("[BESTELLUNG]", $viewArticleTable->LoadTemplate(), $tmp);
		$tmp = str_replace('\n', "\n", $tmp); //gets back escaped linefeeds into actual linefeeds
		$text .= $tmp;  	
		unset($tmp);								   	
		$text .= '</body></html>';
		
		// Compose E-Mail to Customer
		$text_customer = '<html><head></head><body style="font-family: Arial, Helvetica, Sans Serif";>';
		$tmp = utf8_encode(Configuration::$conf_email_to_customer);
		$tmp = str_replace("[DATUM]", $orderdate, $tmp);
		$tmp = str_replace("[NAME]", $name, $tmp);
		$tmp = str_replace("[ADRESSE]", nl2br($address), $tmp);
		$tmp = str_replace("[EMAIL]", $email, $tmp);
		$tmp = str_replace("[ZAHLUNGSWEISE]", ShopManager::GetPaymentMethodText($request['payment_method']), $tmp);
		$tmp = str_replace("[BESTELLUNG]", $viewArticleTable->LoadTemplate(), $tmp);
		$tmp = str_replace('\n', "\n", $tmp); //gets back escaped linefeeds into actual linefeeds
		$text_customer .= $tmp;  	
		unset($tmp);	
		$text_customer .= '</body></html>';					

		// Platzhalter.
		$text = str_replace("[SHOPNAME]", $shopname, $text);
		$text_customer = str_replace("[SHOPNAME]", $shopname, $text_customer);
		
		/*
		$header = array();
		$header[] = 'From: =?iso-8859-1?B?' . base64_encode($shopname) . "?= <" . Configuration::$conf_shopemail . ">";
		$header[] = 'MIME-Version: 1.0';
		$header[] = 'Content-type: text/html; charset=iso-8859-1';
		$header[] = 'Content-Transfer-Encoding: 8bit';		
		*/

		// --
		// An den Besteller senden.

		$mail = new PHPMailer;

		$mail->setLanguage('de', 'language');
		$mail->CharSet = 'utf-8';

		$mail->isHTML(true); 
		$mail->From      = Configuration::$conf_shopemail;
		$mail->FromName  = $shopname;
		$mail->Subject   = $subject_customer;
		$mail->Body      = $text_customer;
		$mail->addAddress( $email );

		if ( isset(Configuration::$conf_mail_attachment1) && Configuration::$conf_mail_attachment1!="" ) 
		{
			$mail->addAttachment( dirname(realpath(__FILE__)) . "/../../../../" . Configuration::$conf_mail_attachment1 );
		}
		if ( isset(Configuration::$conf_mail_attachment2) && Configuration::$conf_mail_attachment2!="" ) 
		{
			$mail->addAttachment( dirname(realpath(__FILE__)) . "/../../../../" . Configuration::$conf_mail_attachment2 );
		}

		$mail->Send();

		// --
		// An den Shop-Betreiber senden.

		$mail = new PHPMailer;

		$mail->setLanguage('de', 'language');
		$mail->CharSet = 'utf-8';

		$mail->isHTML(true); 
		$mail->From      = Configuration::$conf_shopemail;
		$mail->FromName  = $shopname;
		$mail->Subject   = $subject;
		$mail->Body      = $text;
		$mail->addAddress( Configuration::$conf_shopemail );

		if ( isset(Configuration::$conf_mail_attachment) ) 
		{
			$mail->addAttachment( Configuration::$conf_mail_attachment );
		}

		$mail->Send();

		// --

		/*
		mail( Configuration::$conf_shopemail, 
			"=?iso-8859-1?B?" . base64_encode($subject) . "?=", 
			utf8_decode($text), 
			implode(PHP_EOL, $header) );
		
		mail( $email, 
			"=?iso-8859-1?B?" . base64_encode($subject_customer) . "?=", 
			utf8_decode($text_customer), 
			implode(PHP_EOL, $header) );
		*/
	}
	
	/**
	* Validates the input of order form 1.
	* Returns an error message if validation failes.
	* 
	* @param	Array	$request
	* @return	String	Error message
	*/
	public static function ValidateAddressFormInput(
		$request)
	{	
		$error = '';
		$acceptagb = false;
		$payment_method = '';

		$name = trim( utf8_decode($request['name']) );
		$email = trim( $request['email'] );
		$address = trim( utf8_decode($request['address']) );
		if ( isset($request['payment_method']) ) $payment_method = $request['payment_method'];
		if ( isset($request['acceptagb']) ) $acceptagb = (bool)$request['acceptagb'];

		if ( empty( $name ))
		{
			$error = !empty(Configuration::$conf_error_name) ? Configuration::$conf_error_name : 'Bitte geben Sie einen Namen an.';
		}   
		
		if ( empty( $email ) ||
			!is_valid_email( $email ) )
		{
			if ( !empty( $error ) )
				$error .= "<br>";
			$error .= !empty(Configuration::$conf_error_email) ? Configuration::$conf_error_email : 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
		}
		
		if ( empty( $address ) )
		{
			if ( !empty( $error ) )
				$error .= "<br>";
			$error .= !empty(Configuration::$conf_error_address) ? Configuration::$conf_error_address : 'Bitte geben Sie eine Anschrift an.';
		}   		
		
		if ( empty( $payment_method ) )
		{
			if ( !empty( $error ) )
				$error .= "<br>";
			$error .= !empty(Configuration::$conf_error_payment) ? Configuration::$conf_error_payment : 'Bitte wählen Sie die gewünschte Zahlungsweise aus.';
		}   		
		
		if ( !$acceptagb )
		{
			if ( !empty( $error ) )
				$error .= "<br>";
			$error .= !empty(Configuration::$conf_error_agb) ? Configuration::$conf_error_agb : 'Bitte lesen und bestätigen Sie unsere AGB um mit der Bestellung fortzufahren.';
		}
		return $error;   		
	}

	/**
	* In order to successfully redirect to PayPal and return, the address of the user
	* has to be put to the session.
	*/
	public static function PutRequestToSession(
		$request)
	{
		foreach($request as $key => $value)
		{
			if( $key!='action' )
			{
				$_SESSION[$key] = $value;
			}
		}
	}
	
	/**
	* Returns the payment method as translated full text.
	* 
	* @param	string $paymentMethod System internal name for current payment method	
	* @return	String
	*/
	public static function GetPaymentMethodText(
		$paymentMethod)
	{
		// Translation not implemented yet.
		switch($paymentMethod)		
		{
			case 'delivery':
				return !empty(Configuration::$conf_label_payment_delivery) ? Configuration::$conf_label_payment_delivery : 'Nachnahme';
				
			case 'advance':
				return !empty(Configuration::$conf_label_payment_advance) ? Configuration::$conf_label_payment_advance : 'Vorkasse (Überweisung)';
				
			case 'bill':
				return !empty(Configuration::$conf_label_payment_bill) ? Configuration::$conf_label_payment_bill : 'Rechnung';
				
			case 'paypal':
				return !empty(Configuration::$conf_label_payment_paypal) ? Configuration::$conf_label_payment_paypal : 'PayPal';
				
			default:
				return !empty(Configuration::$conf_label_payment_advance) ? Configuration::$conf_label_payment_advance : 'Vorkasse (Überweisung)';
		}
	}

	/**
	* Assigns the shop configuration to the given view
	* 
	* @param View
	*/
	public static function AssignShopConfiguration(
		$view)
	{   	
		if ( isset(Configuration::$conf_buttontext) ){ 	// need to check in case the widget didn't yet pass over this new value
			$view->Assign('conf_buttontext',  Configuration::$conf_buttontext);
		}
		else{
			$view->Assign('conf_buttontext',  "Zahlungspflichtig bestellen");
		}
		if ( isset(Configuration::$conf_buttontext_paypal) ){ 	// need to check in case the widget didn't yet pass over this new value
			$view->Assign('conf_buttontext_paypal',  Configuration::$conf_buttontext_paypal);
		}
		else{
			$view->Assign('conf_buttontext_paypal',  "Zahlungspflichtig bestellen (via PayPal)");
		}
		$view->Assign('conf_shopname', !empty(Configuration::$conf_shopname) ? Configuration::$conf_shopname : 'Online Shop' );
		$view->Assign('conf_shopemail', Configuration::$conf_shopemail);
		$view->Assign('conf_shippingcosts', Configuration::$conf_shippingcosts);
		$view->Assign('conf_currency', Configuration::$conf_currency);
		$view->Assign('conf_curr_comma', Configuration::$conf_curr_comma);
		$view->Assign('conf_curr_thousands', Configuration::$conf_curr_thousands);
		$view->Assign('conf_curr_decimals', Configuration::$conf_curr_decimals);
		$view->Assign('conf_payment_advance',Configuration::$conf_payment_advance);
		$view->Assign('conf_payment_bill', Configuration::$conf_payment_bill);
		$view->Assign('conf_payment_delivery', Configuration::$conf_payment_delivery);
		$view->Assign('conf_payment_paypal', Configuration::$conf_payment_paypal);
		$view->Assign('conf_page_agb', Configuration::$conf_page_agb);
		$view->Assign('conf_page_widerruf', Configuration::$conf_page_widerruf);
		$view->Assign('conf_basketUrl', Configuration::$conf_basketurl);
		$view->Assign('conf_basket_anchor', !empty(Configuration::$conf_basket_anchor) ? Configuration::$conf_basket_anchor : '');
		// New Text Templates to replace static Text
		$view->Assign( 'conf_email_subject_shop', !empty(Configuration::$conf_email_subject_shop) ? Configuration::$conf_email_subject_shop : 'Default Shop Subject' );
		//$view->Assign('conf_email_subject_shop', Configuration::$conf_email_subject_shop);
		$view->Assign('conf_email_subject_customer', Configuration::$conf_email_subject_customer);
		$view->Assign('conf_email_to_shop', Configuration::$conf_email_to_shop);
		
		$view->Assign( 'conf_label_payment_paypal', !empty(Configuration::$conf_label_payment_paypal) ? Configuration::$conf_label_payment_paypal : 'PayPal' );
		$view->Assign( 'conf_label_payment_delivery', !empty(Configuration::$conf_label_payment_delivery) ? Configuration::$conf_label_payment_delivery : 'Nachnahme' );
		$view->Assign( 'conf_label_payment_advance', !empty(Configuration::$conf_label_payment_advance) ? Configuration::$conf_label_payment_advance : 'Vorkasse (Überweisung)' );
		$view->Assign( 'conf_label_payment_bill', !empty(Configuration::$conf_label_payment_bill) ? Configuration::$conf_label_payment_bill : 'Rechnung' );
		$view->Assign( 'conf_label_name', !empty(Configuration::$conf_label_name) ? Configuration::$conf_label_name : 'Name' );
		$view->Assign( 'conf_label_email', !empty(Configuration::$conf_label_email) ? Configuration::$conf_label_email : 'E-Mail-Adresse' );
		$view->Assign( 'conf_label_address', !empty(Configuration::$conf_label_address) ? Configuration::$conf_label_address : 'Anschrift' );
		$view->Assign( 'conf_label_payment', !empty(Configuration::$conf_label_payment) ? Configuration::$conf_label_payment : 'Gewünschte Zahlungsweise' );
		$view->Assign( 'conf_label_agb', !empty(Configuration::$conf_label_agb) ? Configuration::$conf_label_agb : 'Ich habe die allgemeinen Geschäftsbedingungen ([LINK]AGB[/LINK]) gelesen und akzeptiere diese ausdrücklich.' );
		$view->Assign( 'conf_label_position', !empty(Configuration::$conf_label_position) ? Configuration::$conf_label_position : 'Position' );
		$view->Assign( 'conf_label_artikelnummer', !empty(Configuration::$conf_label_artikelnummer) ? Configuration::$conf_label_artikelnummer : 'Artikelnummer' );
		$view->Assign( 'conf_label_menge', !empty(Configuration::$conf_label_menge) ? Configuration::$conf_label_menge : 'Menge' );
		$view->Assign( 'conf_label_bezeichnung', !empty(Configuration::$conf_label_bezeichnung) ? Configuration::$conf_label_bezeichnung : 'Bezeichnung' );
		$view->Assign( 'conf_label_einzelpreis', !empty(Configuration::$conf_label_einzelpreis) ? Configuration::$conf_label_einzelpreis : 'Einzelpreis' );
		$view->Assign( 'conf_label_gesamtpreis', !empty(Configuration::$conf_label_gesamtpreis) ? Configuration::$conf_label_gesamtpreis : 'Gesamtpreis' );
		$view->Assign( 'conf_label_entfernen', !empty(Configuration::$conf_label_entfernen) ? Configuration::$conf_label_entfernen : 'Entfernen' );
		$view->Assign( 'conf_label_zwischensumme', !empty(Configuration::$conf_label_zwischensumme) ? Configuration::$conf_label_zwischensumme : 'Zwischensumme' );
		$view->Assign( 'conf_label_versandkosten', !empty(Configuration::$conf_label_versandkosten) ? Configuration::$conf_label_versandkosten : 'Versandkosten' );
		$view->Assign( 'conf_label_gesamtsumme', !empty(Configuration::$conf_label_gesamtsumme) ? Configuration::$conf_label_gesamtsumme : 'Gesamtsumme inkl. USt.' );
		$view->Assign( 'conf_label_basket', !empty(Configuration::$conf_label_basket) ? Configuration::$conf_label_basket : 'Mein Warenkorb' );
		$view->Assign( 'conf_label_product', !empty(Configuration::$conf_label_product) ? Configuration::$conf_label_product : 'Artikel' );
		$view->Assign( 'conf_head_basketpage', !empty(Configuration::$conf_head_basketpage) ? Configuration::$conf_head_basketpage : 'Ihr Warenkorb' );
		$view->Assign( 'conf_head_orderpage', !empty(Configuration::$conf_head_orderpage) ? Configuration::$conf_head_orderpage : 'Bestellung aufgeben' );
		$view->Assign( 'conf_help_fillform', !empty(Configuration::$conf_help_fillform) ? Configuration::$conf_help_fillform : 'Bitte füllen Sie das Formular vollständig aus.' );
		$view->Assign( 'conf_help_emptybasket', !empty(Configuration::$conf_help_emptybasket) ? Configuration::$conf_help_emptybasket : 'Es befinden sich keine Artikel in Ihrem Warenkorb.' );
		$view->Assign( 'conf_label_updatebasket', !empty(Configuration::$conf_label_updatebasket) ? Configuration::$conf_label_updatebasket : 'Warenkorb aktualisieren' );
		$view->Assign( 'conf_label_goaddressform', !empty(Configuration::$conf_label_goaddressform) ? Configuration::$conf_label_goaddressform : 'Adresse eingeben und Bestellung absenden' );
		$view->Assign( 'conf_text_thankyoupage', !empty(Configuration::$conf_text_thankyoupage) ? Configuration::$conf_text_thankyoupage : '<h2>Vielen Dank!</h2><p>Ihre Bestellung wurde abgesendet.</p><p>Sie erhalten umgehend eine Bestellbestätigung per E-Mail.</p>' );
		$view->Assign( 'conf_error_name', !empty(Configuration::$conf_error_name) ? Configuration::$conf_error_name : 'Bitte geben Sie einen Namen an.' );
		$view->Assign( 'conf_error_email', !empty(Configuration::$conf_error_email) ? Configuration::$conf_error_email : 'Bitte geben Sie eine gültige E-Mail-Adresse an.' );
		$view->Assign( 'conf_error_address', !empty(Configuration::$conf_error_address) ? Configuration::$conf_error_address : 'Bitte geben Sie eine Anschrift an.' );
		$view->Assign( 'conf_error_payment', !empty(Configuration::$conf_error_payment) ? Configuration::$conf_error_payment : 'Bitte wählen Sie die gewünschte Zahlungsweise aus.' );
		$view->Assign( 'conf_error_agb', !empty(Configuration::$conf_error_agb) ? Configuration::$conf_error_agb : 'Bitte lesen und bestätigen Sie unsere AGB um mit der Bestellung fortzufahren.' );
		$view->Assign( 'conf_bgcolor_head', !empty(Configuration::$conf_bgcolor_head) ? Configuration::$conf_bgcolor_head : '#E7E7E7' );
		$view->Assign( 'conf_textcolor_head', !empty(Configuration::$conf_textcolor_head) ? Configuration::$conf_textcolor_head : '#323232' );
		$view->Assign( 'conf_bgcolor_foot', !empty(Configuration::$conf_bgcolor_foot) ? Configuration::$conf_bgcolor_foot : '#E7E7E7' );
		$view->Assign( 'conf_textcolor_foot', !empty(Configuration::$conf_textcolor_foot) ? Configuration::$conf_textcolor_foot : '#323232' );
		$view->Assign( 'conf_paypal_userid', Configuration::$conf_paypal_userid );
		$view->Assign( 'conf_paypal_password', Configuration::$conf_paypal_password );
		$view->Assign( 'conf_paypal_signature', Configuration::$conf_paypal_signature );
		$view->Assign( 'conf_head_paypalbasketverify', !empty(Configuration::$conf_head_paypalbasketverify) ? Configuration::$conf_head_paypalbasketverify : 'Bestellung überprüfen' );
	}
}
?>