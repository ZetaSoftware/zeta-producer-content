<$
var shopIncPath = page.pathToRoot + "assets/php/shop/shop.inc.php";
$>
 
<$= system.partial("article-begin.html") $>
	<$= system.partial("php-widget-begin.html", "5.3.0") $>

	<div id="zpSO-OnlineShop-BasketSummary"></div>
	
	<script type="text/javascript"> 
		getHttpRequest();
	 
		function getHttpRequest() {
			var xmlhttp = null;
	 
			// Mozilla
			if (window.XMLHttpRequest)
			{
				xmlhttp = new XMLHttpRequest();
			}
	 
			// IE
			else if (window.ActiveXObject) 
			{
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
	   
			xmlhttp.open("GET", '<$= shopIncPath $>?view=basketsummary', true);
			xmlhttp.onreadystatechange = function() 
			{
				if(xmlhttp.readyState != 4) 
				{
					document.getElementById('zpSO-OnlineShop-BasketSummary').innerHTML = 'Warenkorb wird geladen …';
				}
	 
				if(xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
					document.getElementById('zpSO-OnlineShop-BasketSummary').innerHTML = xmlhttp.responseText;
				}
			}
			xmlhttp.send(null);
		}
	</script>

	<$= system.partial("php-widget-end.html") $>	
<$= system.partial("article-end.html") $>
