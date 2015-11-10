<$ 
	if ( project.url == "" ) { 
		system.error("Bitte geben Sie die Web-Adresse (URL) Ihres Internet-Auftritts an. Diese Einstellung finden Sie unter \"Website->FTP\".");
	} 

	var cmsesfound = 0;
	var pagename = page.name;
	var cmses = page.articles;
	
	for ( var i=0; i < cmses.count; ++i ) {
		var cms = cmses.item(i);
		if ( cms.active && cms.StyleName == "Online-Artikel" ) {
			++cmsesfound;
		}
	}
	
	// Don't allow more than one online cms per page
	if ( cmsesfound > 1 ) { 
		system.error("Die Seite \"" + pagename + "\" enthält mehr als ein \"Online-CMS\". Pro Seite ist nur ein \"Online-CMS\" erlaubt. Bitte entfernen Sie überzählige Online-CMS-Artikel.");
	} 
 $>

<$= system.partial("article-begin.html") $>
	<$= system.partial("article-headline.html") $>
	<$= system.partial("php-widget-begin.html") $>
		 
	<?php
		$editPagePassword = "<$= article.value('password') $>";
		$dataDomain = "<$= article.id $>";
		$OutsideDataStoragePathWeb = '<$= page.pathToRoot $>assets/php/CMS_DATA';
		$pageUrl ="<$= system.removeInlineEditing(page.absoluteUrl) $>"; 
		$siteUrl ="<$= project.url $>";

		include( dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . "<$= page.pathToRoot $>assets/php/cms/cms.inc.php"); 
	?>
	
	<$= system.partial("php-widget-end.html") $>
<$= system.partial("article-end.html") $>

<$
// Special workaround to get out some http headers before content is sent to the client

var text = "<?php \n";
text += "ob_start();\n";
text += "header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); \n";
text += "header('Last-Modified: ' . Gmdate('D, d M Y H:i:s') . ' GMT'); \n";
text += "header('Cache-Control: no-store, no-cache, must-revalidate'); \n";
text += "header('Cache-Control: post-check=0, pre-check=0', false); \n";
text += "header('Pragma: no-cache'); \n";
text += "$u='" + project.url + "'; \n";
text += "$h=parse_url($u, PHP_URL_HOST); \n";
text += "$p=parse_url($u, PHP_URL_PATH); \n";
text += "$prefix = 'www.'; \n";
text += "if (substr(strtolower($h), 0, strlen($prefix)) == $prefix) { \n";
text += "    $h = '.' . substr($h, strlen($prefix)); \n";
text += "} \n";
text += "if (empty($p)) { \n";
text += "	$p = '/'; \n";
text += "} \n";
text += "if (!empty($h)) { \n";
text += "	session_set_cookie_params(0, $p, $h); \n";
text += "} \n";
text += "session_start(); ?>\n";

system.addFinishScript(text);

 $>
