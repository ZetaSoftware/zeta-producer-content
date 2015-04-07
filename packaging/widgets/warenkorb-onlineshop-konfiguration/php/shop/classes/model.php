<?php
/**
 * Class Model
 *
 * Provides data access to the basket
 * 
 * $Id: model.php 20608 2013-01-11 13:17:01Z sseiz $
 */

require_once('entities/article.php');

class Model{   
	
	public static function IsBasketEmpty()
	{
		return count(Model::GetArticles())<=0;
	}

	/**  
	 * Returns all articles in the session basket
	 *  
	 * @return Array Array BasketItems 
	 */  
	public static function GetArticles()
	{   
		$id = 1;
		$articles = array();
		
		if(isset($_SESSION['article_count']) &&
			$_SESSION['article_count'] > 0)
		{
			while ($id <= $_SESSION['article_count'])
			{
				if (isset($_SESSION['aitem_'.$id]))
				{
					$articles[$id] = $_SESSION['aitem_'.$id];
				}
				
				$id++;
			}	
		}
		
		return $articles;
	}   

	/**  
	 * Returns the sum of all articles in the basket
	 *  
	 * @return Sum
	 */  
	public static function GetBasketTotal()
	{   
		$sum = 0.0;
		$articles = Model::GetArticles();
		
		foreach($articles as $article) 
		{	
			$sum = $sum + $article->sumPrice;
		}
		
		return $sum;
	}

	/**  
	 * Returns the end sum to be paid by the user. This includes 
	 * the sum of all articles in the basket plus any shipping costs.
	 *  
	 * @return Sum
	 */  
	public static function GetBasketTotalEndSum()
	{
		$total_sum = Model::GetBasketTotal();	

		if( Configuration::$conf_shippingcosts > 0 )
		{
			$total_sum += Configuration::$conf_shippingcosts;
		}

		return $total_sum;
	}
	
	/**  
	 * Returns the article by the given id
	 *  
	 * @param int $id Article id
	 * @return Article Article The article. If not found returns null.
	 */  
	public static function GetArticle($id){ 
		  
		if ( array_key_exists('aitem_'.$id, $_SESSION ))
		{   
			$_SESSION['aitem_'.$id];
		}
		else
		{   
			return null;   
		}   
	}   
	
	/**
	* Adds an article to the session basket
	* 
	* @param $articleId
	* @param $articleNo
	* @param $articleQuantity
	* @param $articleQuantityUnit
	* @param $articleText
	* @param $articlePrice
	*/
	public static function AddArticle(
		$articleId,
		$articleNo,
		$articleQuantity,
		$articleQuantityUnit,
		$articleText,
		$articlePrice,
		$afield1name,
		$afield2name,
		$afield1content,
		$afield2content)
	{
		@ob_start();

		if (isset($_SESSION['article_count']))
		{
			$_SESSION['article_count']++;
		}
		else
		{
			$_SESSION['article_count'] = 1;
		}
		$id = $_SESSION['article_count'];
		
		if(!is_numeric($articleQuantity) ||
			$articleQuantity <= 0)
		{
			$articleQuantity = 1;
		}
		
		if ( empty($afield1name) ){
			$afield1name = "";
		}
		if ( empty($afield2name) ){
			$afield2name = "";
		}
		if ( empty($afield1content) ){
			$afield1content = "";
		}
		if ( empty($afield2content) ){
			$afield2content = "";
		}
		
		$article = new Article(
			$id,
			$articleId,
			$articleNo,
			$articleText,
			$articleQuantity,
			$articleQuantityUnit,
			$articlePrice,
			$afield1name,
			$afield2name,
			$afield1content,
			$afield2content);
		
		$_SESSION['aitem_'.$id] = $article;
	}
	
	/**
	* Removes an article from the session basket
	* 
	* @param $id The internal id in the basket
	*/
	public static function RemoveArticle(
		$id)
	{
		unset( $_SESSION['aitem_'.$id] );
	}
}   
?>