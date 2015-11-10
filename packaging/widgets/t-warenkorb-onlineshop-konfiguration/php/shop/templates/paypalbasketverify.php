<script type="text/javascript"> 
	//<![CDATA[
	function __doPostBack() {
		var theForm = document.forms['basketForm'];
		if (!theForm) {
			theForm = document.basketForm;
		}

		if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
			theForm.submit();
		}
	}
	//]]>
</script>

<form name="basketForm" id="basketForm" action="?view=paypalbasketverify&action=order" method="post" class="zpSO-OnlineShop-basketform">
	<?php
		echo '<h3 class="zpSO-OnlineShop-headline">' . $this->_['conf_head_paypalbasketverify'] . '</h3>';

		if ( !empty($this->_['error']) ) echo '<div class="zpSO-OnlineShop-error">' . $error . '</div>';

		echo $this->_['articletable'];
	?>

	<table class="zpSO-OnlineShop-addresstable">
		<tr>
			<td class="zpSO-OnlineShop-addresslabel"><?php echo $this->_['conf_label_name'] ?>:</td>
			<td class="zpSO-OnlineShop-addressvalue"><?php echo $this->_['name'] ?></td>
		</tr>
		<tr>
			<td class="zpSO-OnlineShop-addresslabel"><?php echo $this->_['conf_label_email'] ?>:</td>
			<td class="zpSO-OnlineShop-addressvalue"><?php echo $this->_['email'] ?></td>
		</tr>
		<tr>
			<td class="zpSO-OnlineShop-addresslabel"><?php echo $this->_['conf_label_address'] ?>:</td>
			<td class="zpSO-OnlineShop-addressvalue"><?php echo nl2br($this->_['address']) ?></td>
		</tr>
		<tr>
			<td class="zpSO-OnlineShop-addresslabel"><?php echo $this->_['conf_label_payment'] ?>:</td>
			<td class="zpSO-OnlineShop-addressvalue"><?php echo ShopManager::GetPaymentMethodText($this->_['payment_method']) ?></td>
		</tr>
	</table>

	<div class="zpSO-OnlineShop-buttonarea">
		<a href="javascript:__doPostBack(); " class="button order"><span><?php echo $this->_['conf_buttontext'] ?></span></a>
	</div>
</form>