<div class="zpSO-OnlineShop-ordersent">
	<?php 
		
		$thankoutext = str_replace('\n', "\n", utf8_encode($this->_['conf_text_thankyoupage']));
		$shopname = utf8_encode($this->_['conf_shopname']);
		$request = $this->_['request'];
		
		$name = trim( $request['name'] );
		$email = trim( $request['email'] );
		$address = trim( $request['address'] );	
		
		// Handle some macro replacements.
		$thankoutext = str_replace("[SHOPNAME]", $shopname, $thankoutext);
		$thankoutext = str_replace("[NAME]", $name, $thankoutext);
		$thankoutext = str_replace("[ADRESSE]", nl2br($address), $thankoutext);
		$thankoutext = str_replace("[EMAIL]", $email, $thankoutext);
		$thankoutext = str_replace("[ZAHLUNGSWEISE]", ShopManager::GetPaymentMethodText($request['payment_method']), $thankoutext);
		
		echo $thankoutext; 
	?>
</div>