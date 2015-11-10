<?php
/**
 * Class Article
 *
 * An article object in the basket
 * 
 * $Id: article.php 32282 2015-09-16 11:06:43Z sseiz $
 */
class Article
{
	var $dbId;
	var $id;
	var $no;
	var $text;
	var $quantity;
	var $quantityUnit;
	var $unitPrice;
	var $afield1name;
	var $afield2name;
	var $afield1content;
	var $afield2content;
	var $sumPrice;
		
	/**
	* Constructor
	*/
	function Article(
		$dbId,
		$id,
		$no,
		$text,
		$quantity,
		$quantityUnit,
		$unitPrice,
		$afield1name,
		$afield2name,
		$afield1content,
		$afield2content) 
	{
		$this->dbId = $dbId;
		$this->id = $id;
		$this->no = $no;
		$this->quantity = $quantity;
		$this->quantityUnit = $quantityUnit;
		$this->text = $text;
		$this->unitPrice = $unitPrice;
		
		$this->sumPrice = $unitPrice * $quantity;
		
		$this->afield1name = $afield1name;
		$this->afield2name = $afield2name;
		$this->afield1content = $afield1content;
		$this->afield2content = $afield2content;
	}
	
	/**
	* Changes the quantity and calculates the sum
	* @param $newQuantity The new quantity
	*/
	function ChangeQuantity(
		$newQuantity)
	{
		$this->quantity = $newQuantity;
		$this->sumPrice = $newQuantity * $this->unitPrice;
	}
}

?>