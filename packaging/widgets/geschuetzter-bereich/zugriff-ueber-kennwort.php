<$= system.partial("article-begin.html") $>

	<$= system.partial("article-headline.html") $>
	
	<$= system.partial("php-widget-begin.html", "5.3.0") $>

	<?php 
		 $uiUrl = $siteUrl . "/assets/php/pa/etc/jquery/jquery-ui-1.8.16.custom.min.js";
		 echo '<script  type="text/javascript" src="' . $uiUrl . '"></script>';
		 echo '<div style="padding:0 10px 10px 0; position:fixed; bottom:0; right:0; z-index:9999;"><input type="button" id="paLogout" class="button" onClick="javascript:window.location = \'?action=palogout\'; return false;" value="Abmelden"  title="Von diesem Bereich abmelden" /></div><script type="text/javascript">$z( "#paLogout" ).button();</script>';
	?>
	
	<$= system.partial("php-widget-end.html") $>
			 
	<$
	var myPath = page.pathToRoot + "assets/php/pa/pa.main.php";
	var password = article.value("password");
	var area = article.value("area");
	var areaTitle = article.headline;
	
	var text = '<?php \n';
	text += "ob_start(); \n";
	text += "$displayPagePassword = '" + password.toString().replace("'", "\'") + "'; $areaName = '" + area.toString().replace("'", "\'") + "'; $areaTitle = '" + areaTitle.toString().replace("'", "\'") + "';\n";
	text += "$pageUrl ='" + system.removeInlineEditing(page.absoluteUrl) + "';\n";
	text += "$siteUrl ='" + project.url + "';\n";
	text += "require_once(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . '" + myPath + "');\n";
	text += '?>\n\n';

	system.addFinishScript( text );
	 $>

<$= system.partial("article-end.html") $>
