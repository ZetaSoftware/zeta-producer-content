<$= system.partial("article-begin.html") $>

	<$= system.partial("article-headline.html") $>
	
	<$= system.partial("php-widget-begin.html", "5.3.0") $>
			 
		<$
		var myPath = page.pathToRoot + "assets/php/filebrowser/filebrowser.main.php";
		var serverfolderpath = article.value("serverfolderpath");
		var includefilter = article.value("includefilter");
		var unique = article.id;
		
		var text = "<?php \n";
		text += "ob_start(); \n";
		text += "$serverBaseFolderPath = '" + serverfolderpath + "'; \n";
		text += "$includeFilter = '" + includefilter + "'; \n";
		text += "$unique = 'a" + unique + "'; \n";
		text += "?> \n";

		system.addFinishScript( text );
		$>

		<div class="filebrowser-container">
			<div class="filebrowser-top">
				<div id="breadcrumb-<?php echo $unique ?>">&nbsp;</div>
			</div>
			<div class="filebrowser-table">
				<table class="filebrowser-table">
					<thead>
						<tr>
							<th class="h-name">Name</th>
							<th class="h-size">Größe</th>
							<th class="h-date">Geändert</th>
						</tr>
					</thead>
					<tbody id="list-<?php echo $unique ?>">
					</tbody>
				</table>
			</div>
		<?php
		require_once(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . 'assets/php/filebrowser/filebrowser.main.php');
		?>
	<$= system.partial("php-widget-end.html") $>

<$= system.partial("article-end.html") $>
