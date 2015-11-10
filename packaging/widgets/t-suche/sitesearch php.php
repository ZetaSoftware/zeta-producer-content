<$
function encode_html(astring) //helps prevent XSS e.g. when printing search strings
{
	astring = typeof astring !== 'undefined' ? astring : "";

	astring = astring.replace(/</g, "&lt;");
	astring = astring.replace(/>/g, "&gt;");
	astring = astring.replace(/"/g, "&quo" + "t;");
	astring = astring.replace(/'/g, "&#x27;");
	astring = astring.replace(/\//g, "&#x2F;");
	
	// "[ $" and "$ ]" are reserved chars for ZP Scripting, so we need to encode them to be able to print them out later
	astring = astring.replace(/\[\$/g, "");
	astring = astring.replace(/\$\]/g, "");
	astring = astring.replace(/<\$/g, "");
	astring = astring.replace(/\$\>/g, "");
	return astring;
}
 $>

<$= system.partial("article-begin.html") $>
	<$= system.partial("article-headline.html") $>
	<$= system.partial("php-widget-begin.html") $>
	
	<$ if ( page.extension == ".php" ) {
	
		var pages = page.projectFolder.allPages;
	 $>
	
		<?php
			<$ if ( !page.attribute("search_field_visible") ) { $>
				echo ( "<form name=\"searchengine\" method=\"get\" class=\"SO-SiteSearchForm  zp-form\" style=\"margin-bottom: 20px;\">" );
				echo ( "<label class=\"field\" for=\"q2\">#attribute(search_script_label_keyword)</label> <input placeholder=\"#attribute(search_script_label_keyword)\" class=\"typetext\" type=\"text\" id=\"q2\" name=\"q\" size=\"20\" style=\"margin: 0;\" /> <input type=\"submit\" style=\"margin: 0; display: inline;\" class=\"button\" value=\"#attribute(search_script_button_search_title)\" />" );
				echo ( "</form>" );
			<$ } $>
	
			$c="";
	
			if ( !empty($_POST['q']) ) {
				$c = $_POST['q'];
				#echo "<b>Keyword1</b>";
			}
			elseif ( !empty($_POST['keyword1']) ) {
				$c = $_POST['keyword1'];
				#echo "<b>Keyword1</b>";
			}
			elseif ( !empty($_POST['keyword']) ) {
				$c = $_POST['keyword'];
				#echo "<b>Keyword</b>";
			}
			elseif ( !empty($_GET['q']) ) {
				$c = $_GET['q'];
				#echo "<b>QUERY_STRING</b>";
			}
			elseif ( !empty($_SERVER["QUERY_STRING"]) ) {
				$c = urldecode($_SERVER["QUERY_STRING"]);
				#echo "<b>Query String</b>";
			}
	
			$c = strtolower($c);
			$c = trim ($c);
			# da im index " ' [ und ] entfernt/ersetzt sind, müssen wir im Suchstring das selbe machen damit wir korrekte Matches erhalten
			$c = str_replace('"', "", $c);
			$c = str_replace("'", "", $c);
	
			if ( !empty($c) ) { 
				<$
				for ( var index=0; index < pages.count; ++index ) {
	 
					var p = pages.item(index);
	 
					if ( p.active && !p.attribute("noindex") ) {
	 
						system.log("Erstelle Suchindex für Seite '" + p.name + "'.");
						system.pump();
	  
						aname = p.name || "";
						adescription = p.description || "";
						akeys = p.searchTerms || "";
				
						aname = aname.replace(/\\/g, '\\\\');
						adescription = adescription.replace(/\\/g, '\\\\');
						akeys = akeys.replace(/\\/g, '\\\\');
				
						// filter out #attributes from all strings
						aname = aname.replace(/#(attribute|text|page|html|web|system)\([^\)]+\)\s*/gi, "");
						adescription = adescription.replace(/#(attribute|text|page|html|web|system)\([^\)]+\)\s*/gi, "");
						akeys = akeys.replace(/#(attribute|text|page|html|web|system)\([^\)]+\)\s*/gi, "");
		
						aname = encode_html(aname);
						adescription = encode_html(adescription);
						akeys = encode_html(akeys);
				 $>
						$aName[ <$= index $>] = "<$= aname $>";
						$aDescription[ <$= index $>] = "<$= adescription $>";
						$aURL[ <$= index $>] = "<$= p.url $>";
						$aKeys[ <$= index $>] = "<$= akeys $>";
				<$
					}
					else {
				 $>
						$aName[ <$= index $>] = "";
						$aDescription[ <$= index $>] = "";
						$aURL[ <$= index $>] = "";
						$aKeys[ <$= index $>] = "";
				<$
					}
				}
				 $>
		 
				echo( "<p>#attribute(search_script_result_txt_for) &quot;<span style=\"font-weight:bold\">" . htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . "</span>&quot;:</p>" );
				echo( "<ol class=\"SO-SiteSearchList\">" );
				$field = explode(" ",$c);
				$nFound = 0;
				$number = count($field);
				for($a=0; $a<<$= pages.count $>; $a++) { //Felder hochzählen
					$foundWords = 0;
					for ($y=0; $y < $number; $y++) { //jedes Wort testen
						if (strpos($aKeys[ $a], $field[ $y]) !== false) { // Wenn Wort vorhanden..
							$foundWords++;
						}
					}
					if ($foundWords == $number) { // wenn alle Wörter in dem Feld gefunden wurden
						$nFound++;
						echo("<li>");
						echo( "<a href=\"" . $aURL[ $a] . "\" target=\"_top\"><span style=\"font-weight:bold\">" . $aName[ $a] . "</span></a><br /><span class=\"search-description\">" . $aDescription[ $a] . "</span><br />" );
						if ($aDescription[ $a] != "") {
							echo("<br />");
						}
						echo("</li>");
					}
				}
				echo( "</ol>" );
				if ($nFound == 0) {
					echo( "#attribute(search_script_result_txt_nohits)" );
				}
				else {
					echo( "<span style=\"font-weight:bold\">$nFound #attribute(search_script_result_label_hits).</span>" );
				}
			}
			else { //(!empty($c))
				echo ("<p>#attribute(search_script_msg_required)</p>");
			}
		?>
	
	<$
	} else {
		system.pump();
		system.error("Falsche Dateiendung vergeben. Bitte vergeben Sie in den Seiteneigenschaften dieser Seite die Dateiendung \".php\"");
	}
	 $>
	
	<$= system.partial("php-widget-end.html") $>
	
<$= system.partial("article-end.html") $>