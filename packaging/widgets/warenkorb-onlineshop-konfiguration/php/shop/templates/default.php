<form name="basketForm" id="basketForm" action="?view=default" method="post" class="zpSO-OnlineShop-basketform">

<script type="text/javascript"> 
	//<![CDATA[
	var theForm = document.forms['basketForm'];
	if (!theForm) {
		theForm = document.basketForm;
	}

	function __doPostBack(targetAction) {
		if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
			theForm.__basketAction.value = targetAction;
			theForm.submit();
		}
	}

	function showHidePayPalOrderButton() {
		var val = $("input[name='payment_method']:checked").val();

		if( val=='paypal') {
			$('#normal-order').hide();
			$('#paypal-order').show();
		} else {
			$('#normal-order').show();
			$('#paypal-order').hide();
		}
	}

	$(document).ready(function() {
		showHidePayPalOrderButton();

		$("input[name='payment_method']").change(function() {
			showHidePayPalOrderButton();
		});
	});
	//]]>
</script>

<input id="__basketAction" name="action" type="hidden" value="<?php echo $this->_['nextAction']; ?>" />

<?php

	if ($this->_['action'] == "generateOrder")
	{
		echo '<h3 class="zpSO-OnlineShop-headline">' . $this->_['conf_head_orderpage'] . '</h3>';

		if (!empty($this->_['error']))
		{
			echo '<p>' . $this->_['conf_help_fillform'] . '</p>';
		}
	}
	else{
		echo '<h3 class="zpSO-OnlineShop-headline">' . $this->_['conf_head_basketpage'] . '</h3>';
	}

	if( count( $this->_['articles'] ) > 0 )
	{
		echo $this->_['articletable'];
	}
	else
	{
		echo '<p class="zpSO-OnlineShop-emptybasket">' . $this->_['conf_help_emptybasket'] . '</p>';
	}
	  
	if ( !empty($this->_['error']) )
	{
		echo '<div class="zpSO-OnlineShop-error">' . $error . '</div>';
	}
	
	if ( $this->_['action'] == "generateOrder" ||
		$this->_['action'] == "sendOrder" ||
		$this->_['action'] == "sendOrderPayPal" || 
		$this->_['action'] == "paypalCancel" )
	{
		echo $this->_['addressForm'];
	}
	elseif( count( $this->_['articles'] ) > 0 )
	{
?>
		<div class="zpSO-OnlineShop-basketactions">
			<div class="zpSO-OnlineShop-basketaction"><a href="javascript:__doPostBack('refreshBasket'); " ><?php echo $this->_['conf_label_updatebasket'] ?></a></div> 
			<div class="zpSO-OnlineShop-basketaction"><a href="javascript:__doPostBack('generateOrder'); " class="button"><span><?php echo $this->_['conf_label_goaddressform'] ?></span></a></div> 
		</div>
		
<?php
	} 
?>
</form>

<?php
	if ( $this->_['action'] == "generateOrder" ||
		$this->_['action'] == "sendOrder"	|| 
		$this->_['action'] == "sendOrderPayPal" || 
		$this->_['action'] == "paypalCancel" )
	{
?>
		<script type="text/javascript">
			$(document).ready(function() {
				$('html,body').animate({
						scrollTop: $("#address").offset().top - parseInt($("body").css("padding-top"))
				}, 1000);
			});
		</script>
<?php
	}
	else{
?>
		<script type="text/javascript">
			$(document).ready(function() {
				$('html,body').animate({
						scrollTop: $("<?php echo $this->_['conf_basket_anchor'];?>").offset().top - parseInt($("body").css("padding-top"))
				}, 1000);
			});
		</script>
<?php	
	}
?>	