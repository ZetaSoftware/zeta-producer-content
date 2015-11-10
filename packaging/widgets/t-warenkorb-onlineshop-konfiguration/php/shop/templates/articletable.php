<?php

$no = 0;
$pos = 1;
$colspan = 3;
$articles = $this->_['articles'];
$total_sum = 0.0;	

?>

<table class="zpSO-OnlineShop-table zpSO-OnlineShop-baskettable">
	<tr class="zpSO-OnlineShop-baskettableheader">
	<?php if ($this->_['showPosNo']) { $colspan++; echo '<th class="zpSO-OnlineShop-th" style="background: ' . $this->_['conf_bgcolor_head'] . '; color: ' . $this->_['conf_textcolor_head'] .';">' . $this->_['conf_label_position'] . '</th>'; } ?>
	<?php if ($this->_['showArticleNo']) { $colspan++; echo '<th class="zpSO-OnlineShop-th" style="background: ' . $this->_['conf_bgcolor_head'] . '; color: ' . $this->_['conf_textcolor_head'] . ';">' . $this->_['conf_label_artikelnummer'] . '</th>'; } ?>
	<th class="zpSO-OnlineShop-th" style="text-align:left; background: <?php echo $this->_['conf_bgcolor_head'] ?>; color: <?php echo $this->_['conf_textcolor_head'] ?>;"><?php echo $this->_['conf_label_menge'] ?></th>
	<th class="zpSO-OnlineShop-th" align="left" style="background: <?php echo $this->_['conf_bgcolor_head'] ?>; color: <?php echo $this->_['conf_textcolor_head'] ?>;"><?php echo $this->_['conf_label_bezeichnung'] ?></th>
	<th class="zpSO-OnlineShop-th" style="text-align:right; background: <?php echo $this->_['conf_bgcolor_head'] ?>; color: <?php echo $this->_['conf_textcolor_head'] ?>;"><?php echo $this->_['conf_label_einzelpreis'] ?></th>
	<th class="zpSO-OnlineShop-th" style="text-align:right; background: <?php echo $this->_['conf_bgcolor_head'] ?>; color: <?php echo $this->_['conf_textcolor_head'] ?>;"><?php echo $this->_['conf_label_gesamtpreis'] ?></th>
	<?php if ($this->_['allowRemove']) echo '<th class="zpSO-OnlineShop-th" style="background: ' . $this->_['conf_bgcolor_head'] . '; color: ' . $this->_['conf_textcolor_head'] . ';"></th>'; ?>
		
<?php

foreach($articles as $article) 
{
?>
	<tr class="zpSO-OnlineShop-row1">
		<?php if ($this->_['showPosNo']) echo '<td style="text-align:right; vertical-align: top;">' . sprintf("%03s", $pos) . '</td>'; ?>
		<?php if ($this->_['showArticleNo']) echo '<td style="text-align:right; vertical-align: top;">' . $article->no . '</td>'; ?>
		<?php 
		if ($this->_['allowEdit'])
		{
		?>		
			<td align="right"><input id="aqty_<?php echo $article->dbId ?>" name="aqty_<?php echo $article->dbId ?>" type="text" value="<?php echo $article->quantity ?>" size="1" maxlength="3" style="width:25px; text-align:right;"  /></td>
			<?php
		} 
		else
		{
		?>
			<td align="right" style="vertical-align: top;"><?php echo $article->quantity ?></td>
			<?php
		}
		?>
		<td style="vertical-align: top;"><?php 
			echo $article->text;
			if ( !empty($article->afield1name) && !empty($article->afield1content) ){
				echo " / " .  $article->afield1name . ": " . $article->afield1content;
			}
			if ( !empty($article->afield2name) && !empty($article->afield2content) ){
				echo " / " .  $article->afield2name . ": " . $article->afield2content;
			}
		?></td>
		<td align="right" nowrap style="text-align:right; vertical-align: top;"><?php echo format_currency($article->unitPrice) ?></td>
		<td align="right" nowrap style="text-align:right; vertical-align: top;"><?php echo format_currency($article->sumPrice) ?></td>	
		<?php if ($this->_['allowRemove']) echo '<td><a href="' . $this->_['conf_basketUrl'] . '?action=remove&id=' . $article->dbId . '">' . $this->_['conf_label_entfernen'] . '</a></td>'; ?>
</tr>	
<?php 

$pos++;
$total_sum += $article->sumPrice; 
}

if ($this->_['conf_shippingcosts'] > 0)
{
	echo '<tr class="zpSO-OnlineShop-row1 zpSO-OnlineShop-baskettableshippingcosts">';
	echo '<td colspan="' . $colspan . '" align="right" nowrap>' . $this->_['conf_label_zwischensumme'] . ':</td>';
	echo '<td align="right" nowrap style="text-align:right">' . format_currency($total_sum) . '</td>';

	$total_sum += $this->_['conf_shippingcosts'];
	echo '<tr class="zpSO-OnlineShop-row1">';
	echo '<td colspan="' . $colspan . '" align="right" nowrap>' . $this->_['conf_label_versandkosten'] . ':</td>';
	echo '<td align="right" nowrap style="text-align:right">' . format_currency($this->_['conf_shippingcosts']) . '</td>';
	if ($this->_['allowRemove']) echo '<td></td>';
	echo '</tr>';
}

?>
	<tr class="zpSO-OnlineShop-row2">
    <?php echo '<td colspan="' . $colspan . '" align="right" nowrap style="background: ' . $this->_['conf_bgcolor_foot'] . '; color: ' . $this->_['conf_textcolor_foot'] . ';"><b>' . $this->_['conf_label_gesamtsumme'] . ':</b></td>'; ?>
	<td align="right" nowrap style="text-align:right; background: <?php echo $this->_['conf_bgcolor_foot'] ?>; color: <?php echo $this->_['conf_textcolor_foot'] ?>;"><b><?php echo format_currency($total_sum) ?></b></td>
	<?php if ($this->_['allowRemove']) echo '<td style="background: ' . $this->_['conf_bgcolor_foot'] . '; color: ' . $this->_['conf_textcolor_foot'] . ';"></td>'; ?>
	</tr>
</table>