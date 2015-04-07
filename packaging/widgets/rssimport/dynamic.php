<$= system.partial("article-begin.html") $>
	<$= system.partial("article-headline.html") $>
	<$= system.partial("php-widget-begin.html") $>

	<$
	var feed = article.value("url");
	var titleSize = article.value("titlesize");
	var maxArticles = article.value("maxarticles", 0);
	var showTitle = article.value("showtitle");
	var linkTitle = article.value("linktitle");
	var showDate = article.value("showdate", true);
	var showBody = article.value("showcontent", true);
	var showBodyFullHtml = article.value("showcontentfullhtml", true);
	var bodyLength = article.value("contentexcerptlength", 0);
	$>

	<?php
	require_once(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . "<$= page.pathToRoot $>assets/php/rssimport/debug.inc.php");
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

	if ( DebugConfiguration::$debug )
	{
		error_reporting(E_ALL);
		ini_set('display_startup_errors', 'On');
		ini_set('display_errors', 'On');
	}

	define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
	require_once(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . "<$= page.pathToRoot $>assets/php/rssimport/magpierss/rss_fetch.inc");

	function getBody($item)
	{
		$key = 'content';
		if ( array_key_exists($key, $item) ) 
		{
			$key2 = 'encoded';
			if( array_key_exists($key2, $item[/**/$key]) ) return $item[/**/$key][/**/$key2];

			return $item[/**/$key];
		}
		
		$key = 'description';
		if ( array_key_exists($key, $item) ) return $item[/**/$key];
		
		$key = 'atom_content';
		if ( array_key_exists($key, $item) ) return $item[/**/$key];
		
		return "";
	}
	
	$rss = fetch_rss('<$=feed$>');

	$maxArticles = <$=maxArticles$>;
	$loopCount = 1;

	foreach ($rss->items as $item) 
	{
		if ( $maxArticles <=0 || $loopCount <= $maxArticles )
		{
			$loopCount = $loopCount + 1;

			$link = $item['link'];
			$date = strtotime($item['pubdate']);
			$title = $item['title'];
			$body = getBody($item);

			// Body vorbereiten.
			$maxLen = <$=bodyLength$>;
			$printBody = strip_tags($body);
			if ( $maxLen > 0 && strlen($printBody) > $maxLen )
			{
				$printBody = substr($printBody, 0, $maxLen) . "…";
			}
	?>

		<div class="zp-rss-import-article">

			<$ if ( showTitle ) { $>
				<div class="zp-rss-import-headline">
					<$ if ( linkTitle ) { $>
						<<$=titleSize$>><a href='<?=$link?>'><?=$title?></a></<$=titleSize$>>
					<$ } else { $>
						<<$=titleSize$>><?=$title?></<$=titleSize$>>
					<$ } $> 
				</div>
			<$ } $> 

			<$ if ( showDate ) { $>
				<div class="zp-rss-import-date">
					<?=date("d.m.Y H:i", $date)?>
				</div>
			<$ } $> 

			<$ if ( showBody ) { $>
				<div class="zp-rss-import-body">
					<$ if ( showBodyFullHtml ) { $>
						<?=$body?>
					<$ } else { $>
						<?=$printBody?>
					<$ } $> 
				</div>
			<$ } $> 

		</div>

	<?php
		}
	}
	?>

	<$= system.partial("php-widget-end.html") $>
<$= system.partial("article-end.html") $>
