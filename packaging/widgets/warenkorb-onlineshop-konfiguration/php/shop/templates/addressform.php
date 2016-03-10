<?php
if ( !empty($this->_['error']) )
{
	echo '<div class="zpSO-OnlineShop-error">' . $this->_['error'] . '</div>';
}

$agblabel = $this->_['conf_label_agb'];

$agblabel = str_replace("[LINK]", '<a target="_blank" href="' . $this->_['conf_page_agb'] . '">', $agblabel);
$agblabel = str_replace("[/LINK]", '</a>', $agblabel);

$agblabel = str_replace("[WLINK]", '<a target="_blank" href="' . $this->_['conf_page_widerruf'] . '">', $agblabel);
$agblabel = str_replace("[/WLINK]", '</a>', $agblabel);
?>
<table class="zpSO-OnlineShop-table zpSO-OnlineShop-addresstable">
	<tr>
		<td class="zpSO-OnlineShop-addresslabel"><label for="name"><?php echo $this->_['conf_label_name'] ?>:</label></td>
		<td class="zpSO-OnlineShop-addressvalue"><input type="text" name="name" id="name" size="50" value="<?php echo $this->_['form_input']['name'] ?>" /></td>
	</tr>
	<tr>
		<td class="zpSO-OnlineShop-addresslabel"><label for="email"><?php echo $this->_['conf_label_email'] ?>:</label></td>
		<td class="zpSO-OnlineShop-addressvalue"><input type="text" name="email" id="email" size="50" value="<?php echo $this->_['form_input']['email'] ?>" /></td>
	</tr>
	<tr>
		<td class="zpSO-OnlineShop-addresslabel"><label for="address"><?php echo $this->_['conf_label_address'] ?>:</label></td>
		<td class="zpSO-OnlineShop-addressvalue"><textarea name="address" id="address" rows="5" cols="40"><?php echo $this->_['form_input']['address'] ?></textarea></td>
	</tr>
	<?php 
	if ($this->_['conf_showCommentField']) {
		echo '<tr>';
			echo '<td class="zpSO-OnlineShop-addresslabel"><label for="comment">' . $this->_['conf_label_comment'] . ':</label></td>';
			echo '<td class="zpSO-OnlineShop-addressvalue"><textarea name="comment" id="comment" rows="5" cols="40">' . $this->_['form_input']['comment'] . '</textarea></td>';
		echo '</tr>';
	} ?>
	<tr>
		<td class="zpSO-OnlineShop-addresslabel"><?php echo $this->_['conf_label_payment'] ?>:</td>
		<td class="zpSO-OnlineShop-addressvalue">
			<?php 
				if($this->_['conf_payment_advance']) 
				{
					echo('<input type="radio" name="payment_method" id="payment_advance" value="advance" '); 
					if ($this->_['form_input']['payment_method'] == 'advance') echo 'checked="checked"'; 
					echo('><label for="payment_advance">' . $this->_['conf_label_payment_advance'] . '</label><br>'); 
				}
				if($this->_['conf_payment_bill'])
				{ 
					echo('<input type="radio" name="payment_method" id="payment_bill" value="bill" '); 
					if ($this->_['form_input']['payment_method'] == 'bill') echo 'checked="checked"'; 
					echo('><label for="payment_bill">' . $this->_['conf_label_payment_bill'] . '</label><br>'); 
				}		
				if($this->_['conf_payment_delivery'])
				{
					echo('<input type="radio" name="payment_method" id="payment_delivery" value="delivery" '); 
					if ($this->_['form_input']['payment_method'] == 'delivery') echo 'checked="checked"'; 
					echo('><label for="payment_delivery">' . $this->_['conf_label_payment_delivery'] . '</label><br>'); 
				}
				if($this->_['conf_payment_paypal'])
				{ 
					echo('<input type="radio" name="payment_method" id="payment_paypal" value="paypal" '); 
					if ($this->_['form_input']['payment_method'] == 'paypal') echo 'checked="checked"'; 
					echo('><label for="payment_paypal">' . $this->_['conf_label_payment_paypal'] . '</label><br>'); 
				}		
			?>
		</td>
	</tr>
</table>

<div class="zpSO-OnlineShop-agbarea">
	<input type="checkbox" name="acceptagb" id="acceptagb" value="-1" <?php if ($this->_['form_input']['acceptagb']) echo 'checked="checked"'; ?>/>
	<label for="acceptagb"><?php echo $agblabel ?></label>
</div>

<div class="zpSO-OnlineShop-buttonarea">
	<span id="normal-order">
		<a href="javascript:__doPostBack('sendOrder'); " class="button order"><span><?php echo $this->_['conf_buttontext'] ?></span></a>
	</span>
	<span id="paypal-order" style="display:none">
		<a href="javascript:__doPostBack('sendOrderPayPal'); " class="button order"><span><?php echo $this->_['conf_buttontext_paypal'] ?></span></a>
	</span>
</div>