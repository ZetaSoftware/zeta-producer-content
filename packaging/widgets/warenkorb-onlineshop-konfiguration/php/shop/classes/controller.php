<?php
/**
 * Class Controller
 *
 * Controls the inclusion of the Zeta Producer online shop.
 * 
 * $Id: controller.php 20608 2013-01-11 13:17:01Z sseiz $
 */

include_once('helper.inc.php');
include_once('shopmanager.php');

class Controller
{   
	private $request = null;   
	private $action = null;   
	private $template = '';
	private $view = null;
	private $shopmanager = null;
	private $orderdate = null;
	
	/**  
	 * Constructor
	 *  
	 * @param Array $request Array of $_GET & $_POST.  
	 */  
	public function Controller(
		$request)
	{   
		$this->request = $request;   
		$this->template = !empty($request['view']) ? $request['view'] : 'default';   
		$this->action = !empty($request['action']) ? $request['action'] : 'default';

		$this->shopmanager = new ShopManager();
	}
	
	/**
	* Displays the content / processes input
	* 
	* @return String Content. 
	*/
	public function Display()
	{
		SetTimeZone();

//		echo $this->template . " - " . $this->action;
//		die;

		$error = '';
		
		$view = new View();
		$view->Assign('action', $this->action);
		
		$this->shopmanager->AssignShopConfiguration($view);
		
		switch($this->template)
		{   		           
			case 'basketsummary':
				if( Model::IsBasketEmpty() )
				{
					$view->SetTemplate('basketempty');
				}
				else
				{
					$view->SetTemplate('basketsummary');
					$this->AssignBasketSummary($view);
				}
				break;
			
			case 'paypalbasketverify':
				if( Model::IsBasketEmpty() )
				{
					$view->SetTemplate('basketempty');
				}
				else
				{
					$view->SetTemplate('paypalbasketverify');
					$this->AssignBasketSummary($view);

					switch ($this->action)
					{
						case 'order':
							// checking for active session prevents order emails from being sent upon browser reloads
							if ( !empty($_SESSION) && !empty($_SESSION['PayPalToken']) )
							{
								// Jetzt bestellen.
								$paypal = new PayPal();

								$detailsResult = $paypal->GetExpressCheckoutDetails( 
									$this->request, 
									$_SESSION['PayPalToken'] );

								if( !$detailsResult->error )
								{
									$payResult = $paypal->DoExpressCheckoutPayment(
										$this->request,
										$_SESSION['PayPalToken'],
										$detailsResult->payerID );

									if( !$detailsResult->error )
									{
										SetTimeZone();
										$orderdate = date( 'd.m.Y, H:i' );
										$articles = Model::GetArticles();
										
										ShopManager::SendOrderEmail(
											$this->request,
											$articles,
											$orderdate);

										$view->Assign('request', $this->request);
										
										$view->SetTemplate('ordersent');   
										session_unset();
										$_SESSION = array();	
									}
									else
									{
										$this->AttachAddressFormView(
											$this->request,
											$view,
											$payResult->error);
									
										$this->DisplayBasket(
											$this->request,
											$view,
											false,
											false,
											'sendOrderPayPal');						
									}
								}
								else
								{
									$this->AttachAddressFormView(
										$this->request,
										$view,
										$detailsResult->error);
								
									$this->DisplayBasket(
										$this->request,
										$view,
										false,
										false,
										'sendOrderPayPal');						
								}
							}
							
							/*
							$view->Assign('request', $this->request);
							
							$view->SetTemplate('ordersent');   
							session_unset();
							$_SESSION = array();	
							*/
							break;

						default:
							$this->DisplayBasketAndAddressVerification(
								$this->request,
								$view);						
							break;
					}
				}
				break;

			default: // Default-View ist die Anzeige des Warenkorbs.
				switch($this->action)
				{
					case 'add':
						Model::AddArticle(
							$this->request['aid'],
							$this->request['ano'],
							$this->request['aqty'],
							$this->request['aqtyunit'],
							$this->request['atext'],
							$this->request['aprice'],
							$this->request['field1name'],
							$this->request['field2name'],
							$this->request['afield1'],
							$this->request['afield2']
							);
							
						$url = Configuration::$conf_basketurl;
						header("Location: $url");
						exit;
										
					case 'remove':
						if( Model::IsBasketEmpty() )
						{
							$view->SetTemplate('basketempty');
						}
						else
						{
							Model::RemoveArticle(
								$this->request['id']);
							
							if( Model::IsBasketEmpty() )
							{
								$view->SetTemplate('basketempty');
							}
							else
							{
								$this->DisplayBasket(
									$this->request,
									$view,
									true,
									true,
									'sendOrder');
							}
						}
						break;
					
					case 'generateOrder':
						if( Model::IsBasketEmpty() )
						{
							$view->SetTemplate('basketempty');
						}
						else
						{
							$_SESSION['PayPalToken'] = null; // Falls zurück von PayPal kommt.

							$this->UpdateBasket(
								$this->request);
							
							$this->AttachAddressFormView(
								$this->request,
								$view,
								$error);
								
							$this->DisplayBasket(
								$this->request,
								$view,
								false,
								false,
								'sendOrder');
						}							
						break;
					
					case 'sendOrder':
						if( Model::IsBasketEmpty() )
						{
							$view->SetTemplate('basketempty');
						}
						else
						{
							$error = ShopManager::ValidateAddressFormInput($this->request);
							
							// If no error occurred send the order	
							if(empty($error))
							{	
								// checking for active session prevents order emails from being sent upon browser reloads
								if ( !empty($_SESSION) ){
									SetTimeZone();
									$orderdate = date( 'd.m.Y, H:i' );
									$articles = Model::GetArticles();
									
									ShopManager::SendOrderEmail(
										$this->request,
										$articles,
										$orderdate);
								}
								
								$view->Assign('request', $this->request);
								
								$view->SetTemplate('ordersent');   
								session_unset();
								$_SESSION = array();	
							}
							else
							{								
								$this->AttachAddressFormView(
									$this->request,
									$view,
									$error);
								
								$this->DisplayBasket(
									$this->request,
									$view,
									false,
									false,
									'sendOrder');						
							}						  
						}
						break;
					
					case 'sendOrderPayPal':
						if( Model::IsBasketEmpty() )
						{
							$view->SetTemplate('basketempty');
						}
						else
						{
							$error = ShopManager::ValidateAddressFormInput($this->request);

							// Remember user's address settings in session
							// to later generate e-mail.
							ShopManager::PutRequestToSession($this->request);
							
							// If no error occurred continue with PayPal.	
							if(empty($error))
							{	
								$paypal = new PayPal();

								$exResult = $paypal->SetExpressCheckout(
									$this->request,
									Model::GetArticles() );

								if( !$exResult->error )
								{
									$_SESSION['PayPalToken'] = $exResult->token;
									$paypal->Redirect( $exResult->token );
								}
								else
								{
									$this->AttachAddressFormView(
										$this->request,
										$view,
										$exResult->error);
								
									$this->DisplayBasket(
										$this->request,
										$view,
										false,
										false,
										'sendOrderPayPal');						
								}
							}
							else
							{								
								$this->AttachAddressFormView(
									$this->request,
									$view,
									$error);
								
								$this->DisplayBasket(
									$this->request,
									$view,
									false,
									false,
									'sendOrderPayPal');						
							}						  
						}
						break;

					case 'refreshBasket':
						if( Model::IsBasketEmpty() )
						{
							$view->SetTemplate('basketempty');
						}
						else
						{
							$this->UpdateBasket(
								$this->request);
										
							$this->DisplayBasket(
								$this->request,
								$view,
								true,
								true,
								'generateOrder');
						}
						break;
					
					default:
						if( Model::IsBasketEmpty() )
						{
							$view->SetTemplate('basketempty');
						}
						else
						{
							$this->DisplayBasket(
								$this->request,
								$view,
								true,
								true,
								'generateOrder');
						}
				}
		}
		
		return $view->LoadTemplate();   
	}  
	
	/**
	* Displays the basket
	* 
	* @param Array
	* @param View
	* @param bool $allowRemove Allow the removal of articles.
	* @param bool $allowEdit Allow the editing of articles.
	*/
	public function DisplayBasket(
		$request,
		$view,
		$allowRemove,
		$allowEdit,
		$nextActionPage)
	{
		$view->SetTemplate('default');   
		$articles = Model::GetArticles();
		
		$articleTable = new View();
		$articleTable->SetTemplate('articletable');
		$articleTable->Assign('articles', $articles);
		$articleTable->Assign('allowRemove', $allowRemove);       
		$articleTable->Assign('allowEdit', $allowEdit);       
		$articleTable->Assign('showPosNo', false);    
		$articleTable->Assign('showArticleNo', false);    

		$this->shopmanager->AssignShopConfiguration($articleTable);
		
		$view->Assign('nextAction', $nextActionPage );       
		
		$view->Assign('articletable', $articleTable->LoadTemplate());  									   
		
		$view->Assign('articles', $articles);  
	}
	
	/**
	* Displays the basket plus address for PayPal verification.
	* 
	* @param Array
	* @param View
	* @param bool $allowRemove Allow the removal of articles.
	* @param bool $allowEdit Allow the editing of articles.
	*/
	public function DisplayBasketAndAddressVerification(
		$request,
		$view)
	{
		$view->SetTemplate('paypalbasketverify');

		$articles = Model::GetArticles();
		$view->Assign('articles', $articles);

		$view->Assign('name', $request['name']);
		$view->Assign('email', $request['email']);
		$view->Assign('address', $request['address']);
		$view->Assign('payment_method', $request['payment_method']);

		// --
		// Partial-View rendern und Ergebnis in Hauptview-Variable speichern.
		
		$articleTable = new View();
		$articleTable->SetTemplate('articletable');
		$articleTable->Assign('articles', $articles);
		$articleTable->Assign('allowRemove', false);       
		$articleTable->Assign('allowEdit', false);
		$articleTable->Assign('showPosNo', false);
		$articleTable->Assign('showArticleNo', false);

		$this->shopmanager->AssignShopConfiguration($articleTable);
		
		$view->Assign('articletable', $articleTable->LoadTemplate());
	}
	
	/**
	* Assigns the input of the order form 1
	* to the desired view as array
	* 
	* @param Array
	* @param View
	* @param String $errorMessage
	*/
	public function AttachAddressFormView(
		$request,
		$view,
		$errorMessage)
	{   
		$addressForm = new View();
		$addressForm->SetTemplate('addressform');
		
		$form_input = array();
		
		$form_input['name'] = '';
		$form_input['email'] = '';
		$form_input['address'] = '';
		$form_input['acceptagb'] = false;
		$form_input['payment_method'] = '';

		if ( isset($request['name']) ) $form_input['name'] = $request['name'];
		if ( isset($request['email']) )$form_input['email'] = $request['email'];
		if ( isset($request['address']) ) $form_input['address'] = $request['address'];
		if ( isset($request['acceptagb']) ) $form_input['acceptagb'] = $request['acceptagb'];
		if ( isset($request['payment_method']) ) $form_input['payment_method'] = $request['payment_method'];
		
		$addressForm->Assign('form_input', $form_input);
		$addressForm->Assign('error', $errorMessage);
		
		$this->shopmanager->AssignShopConfiguration($addressForm);
				
		$view->Assign('addressForm', $addressForm->LoadTemplate());
	}
	
	/**
	* Assigns the input of the order form 1
	* to the desired view as array
	* 
	* @param Array
	*/
	public function UpdateBasket(
		$request)
	{
		$articles = Model::GetArticles();
		
		foreach($articles as $article) 
		{	
			if(isset($request['aqty_'.$article->dbId]))
			{
				if(is_numeric($request['aqty_'.$article->dbId]) &&
					!($request['aqty_'.$article->dbId] <= 0))
				{
					$article->ChangeQuantity(
						$request['aqty_'.$article->dbId]);
				}
			}
		}
	}
		
	/**
	* Assigns the current article-count and the sum.
	* 
	* @param View
	*/
	public function AssignBasketSummary(
		$view)
	{
		$articles = Model::GetArticles();
		$total = Model::GetBasketTotal();
		
		$view->Assign('articlecount',  count($articles));
		$view->Assign('total',  format_currency($total));
	}
}
?>