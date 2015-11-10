<$= system.partial("article-begin.html") $>
	<$= system.partial("article-headline.html") $>
		 
	<$ 
		if ( project.url=='' ) { 
			system.error("Bitte geben Sie die URL Ihres Internet-Auftritts an. Diese Einstellung finden Sie unter \"Website > Veröffentlichen > FTP-Server konfigurieren\".");
		}
	 $>
	
	<$= system.partial("php-widget-begin.html", "5.3.0") $>
		 
	<?php
		$adminPagePasswordGb = "<$= article.value('password') $>";
		$dataDomainGb = "<$= article.id $>";
		$entriesCountPerPage = <$= article.value("entriesCountPerPage") $>; 
		$approveEntries = <$= article.value("approveEntries") $>;
		$notificationEmail = "<$= article.value('notificationEmail') $>"; 
		$pageUrl ="<$= system.removeInlineEditing(page.absoluteUrl) $>"; 
		$siteUrl ="<$= project.url $>";

		include( dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . "<$= page.pathToRoot $>assets/php/guestbook/gb.main.php"); 
	?>
	
	<$= system.partial("php-widget-end.html") $>
		 
	<$
	// Special workaround to get out some http headers before content is sent to the client
	
	var text = "<?php\n";
	text += "ob_start();\n"
	text += "header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');\n";
	text += "header('Last-Modified: ' . Gmdate('D, d M Y H:i:s') . ' GMT');\n";
	text += "header('Cache-Control: no-store, no-cache, must-revalidate');\n";
	text += "header('Cache-Control: post-check=0, pre-check=0', false);\n";
	text += "header('Pragma: no-cache');\n";
	text += "?>\n";
	
	system.addFinishScript( text );
	 $>
<$= system.partial("article-end.html") $>
