<?php

/**
 * Class PayPal
 *
 * Helper methods for dealing with PayPal Express Checkout.
 * 
 * Gesamter Ablauf beschrieben:
 * https://developer.paypal.com/docs/classic/express-checkout/ht_ec-singleItemPayment-curl-etc/
 
 * $Id: paypal.inc.php 20608 2013-11-10 13:17:01Z ukeim $
 */
class PayPal
{   
	/**
	* Initial call to PayPal.
	* https://developer.paypal.com/webapps/developer/docs/classic/express-checkout/ht_ec-singleItemPayment-curl-etc/
	* 
	* @param Array $request
	*
	* @return ExpressCheckoutResult type. 
	*/
	public function SetExpressCheckout(
		$request)
	{
		SetTimeZone();

		$fullUrl = 
			$this->makeFullUrl( sprintf(
				"{PayPalApiCallUrl}" . 
				"?USER=%s" .								// User ID of the PayPal caller account
				"&PWD=%s" .									// Password of the caller account
				"&SIGNATURE=%s" .							// Signature of the caller account
				"&METHOD=SetExpressCheckout" .
				"&VERSION=93" .
				"&PAYMENTREQUEST_0_PAYMENTACTION=SALE" .	// type of payment
				"&PAYMENTREQUEST_0_AMT=%s" .				// amount of transaction
				"&PAYMENTREQUEST_0_CURRENCYCODE=%s" .		// currency of transaction
				"&RETURNURL=%s" .							// URL of your payment confirmation page
				"&CANCELURL=%s",							// URL redirect if customer cancels payment
				urlencode(Configuration::$conf_paypal_userid),
				urlencode(Configuration::$conf_paypal_password),
				urlencode(Configuration::$conf_paypal_signature),
				urlencode(Model::GetBasketTotalEndSum()),
				urlencode(Configuration::$conf_currency),
				urlencode(AppendToUrl( Configuration::$conf_basketurl, "view=paypalbasketverify" )),
				urlencode(AppendToUrl( Configuration::$conf_basketurl, "view=default&action=generateOrder" ))));

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_URL, $fullUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($curl);		
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		$responseArray = $this->convertNvpToArray($response);
		$errors = $this->GetErrors($responseArray);

		// --

		$result = new ExpressCheckoutResult();

		if( $response===FALSE )
		{
			$ce = curl_error($curl);
			$result->error = "Fehler '$ce' beim PayPal-Aufruf von 'SetExpressCheckout'.";
		}
		else
		{
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if( $httpCode==200 )
			{
				if(isset($responseArray['TOKEN']) && $responseArray['TOKEN'] != '')
				{
					$result->token = $responseArray['TOKEN'];
				}
				else
				{
					$result->error = $this->DisplayErrors($this->GetErrors($responseArray));
				}
			}
			else
			{
				$result->error = "HTTP-Fehler $httpCode beim PayPal-Aufruf von 'SetExpressCheckout' ($response).";
			}
		}

		// --

		curl_close($curl);

		return $result;
	}

	/**
	* Redirect after successful initial call to SetExpressCheckout.
	* https://developer.paypal.com/webapps/developer/docs/classic/express-checkout/ht_ec-singleItemPayment-curl-etc/
	* 
	* @param $token The token returned from the initial call to SetExpressCheckout.
	*
	* @return ExpressCheckoutResult type. 
	*/
	public function Redirect(
		$token )
	{
		$url = $this->makeFullUrl("{PayPalRedirectBaseUrl}/cgi-bin/webscr?cmd=_express-checkout&token=" . $token);
		header("Location: $url");
	}

	/**
	* Get details after successful initial call PayPal.
	* https://developer.paypal.com/webapps/developer/docs/classic/express-checkout/ht_ec-singleItemPayment-curl-etc/
	* 
	* @param Array $request
	* @param string $token The token returned from the SetExpressCheckout call.
	*
	* @return ExpressCheckoutDetailResult type. 
	*/
	public function GetExpressCheckoutDetails(
		$request,
		$token )
	{
		SetTimeZone();

		$fullUrl = 
			$this->makeFullUrl( sprintf(
				"{PayPalApiCallUrl}" . 
				"?USER=%s" .								// User ID of the PayPal caller account
				"&PWD=%s" .									// Password of the caller account
				"&SIGNATURE=%s" .							// Signature of the caller account
				"&METHOD=GetExpressCheckoutDetails" .
				"&VERSION=93" .
				"&TOKEN=%s",							// URL redirect if customer cancels payment
				urlencode(Configuration::$conf_paypal_userid),
				urlencode(Configuration::$conf_paypal_password),
				urlencode(Configuration::$conf_paypal_signature),
				urlencode($token)));

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_URL, $fullUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($curl);		
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		$responseArray = $this->convertNvpToArray($response);
		$errors = $this->GetErrors($responseArray);

		// --

		$result = new ExpressCheckoutDetailResult();

		if( $response===FALSE )
		{
			$ce = curl_error($curl);
			$result->error = "Fehler '$ce' beim PayPal-Aufruf von 'GetExpressCheckoutDetails'.";
		}
		else
		{
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if( $httpCode==200 )
			{
				if(isset($responseArray['TOKEN']) && $responseArray['TOKEN'] != '' &&
					isset($responseArray['PAYERID']) && $responseArray['PAYERID'] != '')
				{
					$result->payerID = $responseArray['PAYERID'];
				}
				else
				{
					$result->error = $this->DisplayErrors($this->GetErrors($responseArray));
				}
			}
			else
			{
				$result->error = "HTTP-Fehler $httpCode beim PayPal-Aufruf von 'GetExpressCheckoutDetails' ($response).";
			}
		}

		// --

		curl_close($curl);

		return $result;
	}

	/**
	* Get details after successful initial call PayPal.
	* https://developer.paypal.com/webapps/developer/docs/classic/express-checkout/ht_ec-singleItemPayment-curl-etc/
	* 
	* @param Array $request
	* @param string $token The token returned from the SetExpressCheckout call.
	*
	* @return ExpressCheckoutPaymentResult type. 
	*/
	public function DoExpressCheckoutPayment(
		$request,
		$token,
		$payerID)
	{
		SetTimeZone();

		$fullUrl = 
			$this->makeFullUrl( sprintf(
				"{PayPalApiCallUrl}" . 
				"?USER=%s" .								// User ID of the PayPal caller account
				"&PWD=%s" .									// Password of the caller account
				"&SIGNATURE=%s" .							// Signature of the caller account
				"&METHOD=DoExpressCheckoutPayment" .
				"&VERSION=93" .
				"&TOKEN=%s" .								// URL redirect if customer cancels payment
				"&PAYERID=%s" .                      		// customer's unique PayPal ID
				"&PAYMENTREQUEST_0_PAYMENTACTION=SALE" .	// type of payment
				"&PAYMENTREQUEST_0_AMT=%s" .				// amount of transaction
				"&PAYMENTREQUEST_0_CURRENCYCODE=%s",		// currency of transaction
				urlencode(Configuration::$conf_paypal_userid),
				urlencode(Configuration::$conf_paypal_password),
				urlencode(Configuration::$conf_paypal_signature),
				urlencode($token),
				urlencode($payerID),
				urlencode(Model::GetBasketTotalEndSum()),
				urlencode(Configuration::$conf_currency)));

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_URL, $fullUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($curl);		
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		$responseArray = $this->convertNvpToArray($response);
		$errors = $this->GetErrors($responseArray);

		// --

		$result = new ExpressCheckoutPaymentResult();

		if( $response===FALSE )
		{
			$ce = curl_error($curl);
			$result->error = "Fehler '$ce' beim PayPal-Aufruf von 'DoExpressCheckoutPayment'.";
		}
		else
		{
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if( $httpCode==200 )
			{
				$result->error = $this->DisplayErrors($this->GetErrors($responseArray));
			}
			else
			{
				$result->error = "HTTP-Fehler $httpCode beim PayPal-Aufruf von 'DoExpressCheckoutPayment' ($response).";
			}
		}

		// --

		curl_close($curl);

		return $result;
	}

	/**
	 * Get all errors returned from PayPal
	 *
	 * @access	public
	 * @param	array	PayPal NVP response
	 * @return	array
	 */
	function GetErrors($dataArray)
	{
		$errors = array();
		$n = 0;
		while(isset($dataArray['L_ERRORCODE' . $n . '']))
		{
			$LErrorCode = isset($dataArray['L_ERRORCODE' . $n . '']) ? $dataArray['L_ERRORCODE' . $n . ''] : '';
			$LShortMessage = isset($dataArray['L_SHORTMESSAGE' . $n . '']) ? $dataArray['L_SHORTMESSAGE' . $n . ''] : '';
			$LLongMessage = isset($dataArray['L_LONGMESSAGE' . $n . '']) ? $dataArray['L_LONGMESSAGE' . $n . ''] : '';
			$LSeverityCode = isset($dataArray['L_SEVERITYCODE' . $n . '']) ? $dataArray['L_SEVERITYCODE' . $n . ''] : '';
			
			$CurrentItem = array(
								'L_ERRORCODE' => $LErrorCode, 
								'L_SHORTMESSAGE' => $LShortMessage, 
								'L_LONGMESSAGE' => $LLongMessage, 
								'L_SEVERITYCODE' => $LSeverityCode
								);
								
			array_push($errors, $CurrentItem);
			$n++;
		}
		
		return count($errors)<=0?null:$errors;
	}
	
	/**
	 * Get errors as string.
	 *
	 * @access	public
	 * @param	array	Errors array returned from class
	 * @return	string with errors.
	 */
	function DisplayErrors($errors)
	{
		if($errors==null) return null;

		$result = '';
		
		foreach($errors as $errorKey => $errorValue)
		{
			$currentError = $errors[$errorKey];
			foreach($currentError as $currentErrorKey => $currentErrorValue)
			{
				if($currentErrorKey == 'L_ERRORCODE')
				{
					$currentVariableName = 'Error code';
				}
				elseif($currentErrorKey == 'L_SHORTMESSAGE')
				{
					$currentVariableName = 'Short message';
				}
				elseif($currentErrorKey == 'L_LONGMESSAGE')
				{
					$currentVariableName = 'Long message';
				}
				elseif($currentErrorKey == 'L_SEVERITYCODE')
				{
					$currentVariableName = 'Severity code';
				}
			
				$result .= $currentVariableName . ': ' . $currentErrorValue . '<br />';		
			}
			$result .= '<br />';
		}

		return $result==''?null:$result;
	}

	/**
	 * Convert an NVP string to an array with URL decoded values
	 *
	 * @access	public
	 * @param	string	NVP string
	 * @return	array
	 */
	function convertNvpToArray($nvpString)
	{
		$proArray = array();
		while(strlen($nvpString))
		{
			// name
			$keypos= strpos($nvpString,'=');
			$keyval = substr($nvpString,0,$keypos);
			// value
			$valuepos = strpos($nvpString,'&') ? strpos($nvpString,'&'): strlen($nvpString);
			$valval = substr($nvpString,$keypos+1,$valuepos-$keypos-1);
			// decoding the respose
			$proArray[$keyval] = urldecode($valval);
			$nvpString = substr($nvpString,$valuepos+1,strlen($nvpString));
		}

		return $proArray;
	}

	private function makeFullUrl($url)
	{
		$conf_paypal_apicall_url = DebugConfiguration::$debug ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
		$paypal_redirect_baseurl = DebugConfiguration::$debug ? 'https://www.sandbox.paypal.com' : 'https://www.paypal.com';

		$url = str_replace('{PayPalApiCallUrl}', $conf_paypal_apicall_url, $url);
		$url = str_replace('{PayPalRedirectBaseUrl}', $paypal_redirect_baseurl, $url);

		return $url;
	}
}

/**
 * Class ExpressCheckoutResult 
 *
 * Helper class for returning the result from PayPal::SetExpressCheckout.
 * 
 * $Id: paypal.inc.php 20608 2013-11-10 13:17:01Z ukeim $
 */
class ExpressCheckoutResult 
{
	public $token = null;   
	public $error = null;   
}

/**
 * Class ExpressCheckoutDetailResult 
 *
 * Helper class for returning the result from PayPal::GetExpressCheckoutDetails.
 * 
 * $Id: paypal.inc.php 20608 2013-11-10 13:17:01Z ukeim $
 */
class ExpressCheckoutDetailResult 
{
	public $payerID = null;   
	public $error = null;   
}

/**
 * Class ExpressCheckoutPaymentResult 
 *
 * Helper class for returning the result from PayPal::DoExpressCheckoutPayment.
 * 
 * $Id: paypal.inc.php 20608 2013-11-10 13:17:01Z ukeim $
 */
class ExpressCheckoutPaymentResult 
{
	public $error = null;   
}
?>