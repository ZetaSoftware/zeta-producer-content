<$
	var shopsInProject = project.getArticlesWithStyle("warenkorb-onlineshop-konfiguration");
	var activeShopsInProject = 0;
	var pagesWithShops = [];
	var pageIDsWithShops = [];
	
	if (!Array.prototype.indexOf) {
		Array.prototype.indexOf = function(obj, start) {
			 for (var i = (start || 0), j = this.length; i < j; i++) {
				 if (this[i] === obj) { return i; }
			 }
			 return -1;
		}
	}

	for ( var i=0; i < shopsInProject.count; ++i ) {
		var shopArticle = shopsInProject.item(i);
		if ( shopArticle.active ){
			activeShopsInProject++;
			if ( pageIDsWithShops.indexOf(shopArticle.page.id) == -1 ){
				pagesWithShops.push(shopArticle.page.name); // unique list of page names containing shop configs
			}
			pageIDsWithShops.push(shopArticle.page.id);
		}
	}
	// Alert if user added more than 1 Shop-Configuration per Project
	if ( activeShopsInProject > 1 ){
		system.error("Es ist 1 \"" + article.widgetName + "\"-Widget pro Projekt zulässig. Ihr Projekt enthält " + activeShopsInProject + ". Bitte entfernen Sie überzählige \"" + article.widgetName + "\"-Widgets von den folgenden Seiten: " + pagesWithShops.join(", ") + ". \r\n\r\nDas Entfernen geht evtl. nur im Spaltenmodus (F8).");
	}
$>
<$= system.partial("article-begin.html") $>
	<$= system.partial("article-headline.html") $>
	
	<$= system.partial("php-widget-begin.html", "5.3.0") $>

	<$ if ( page.extension == ".php" ) { $>
		<$		
		// BEGINN - Make shop configuration
	
		function append( appendTo, appendix ) {
			return appendTo + appendix + "\r\n";
		}

		function createShopConfig() {
	
			var configFileName = "config.inc.php";
			var configFilePath = project.outputFolder + "assets\\php\\shop\\" + configFileName;

			// --

			var content = "";
	
			content = append( content, "<?php");
			content = append( content, "class Configuration {");
			if ( article.value("buttontext") ) {
				content = append( content, "public static $conf_buttontext = '" + article.value("buttontext") + "';" );
			}
			else {
				content = append( content, "public static $conf_buttontext = 'Jetzt kaufen';" );
			}
			content = append( content, "public static $conf_shopemail = '" + article.value("shopemail") + "';" );
			content = append( content, "public static $conf_shopname = '" + system.htmlEncode(article.value("shopname")) + "';" );
		
			// -- NEW Text Template configs to replace text statically in PHP
			// TODO Add procedure to rpelace empty values with defaults in case of user opening an existing project 
			content = append( content, "public static $conf_email_subject_shop = '" + article.value("email_subject_shop","").toString().replace("[SHOPNAME]",article.value("shopname","")) + "';" );
			content = append( content, "public static $conf_email_subject_customer = '" + article.value("email_subject_customer","").toString().replace("[SHOPNAME]", article.value("shopname","")) + "';" );
	
			var email_to_shop = article.value("email_to_shop","");
			email_to_shop = email_to_shop.toString().replace("\r\n", "\\n");
			content = append( content, "public static $conf_email_to_shop = '" + system.htmlEncode(email_to_shop.toString().replace("[SHOPNAME]", article.value("shopname","")), true) + "';" );
	
			var email_to_customer = article.value("email_to_customer","");
			email_to_customer = email_to_customer.toString().replace("\r\n", "\\n");
			content = append( content, "public static $conf_email_to_customer = '" + system.htmlEncode(email_to_customer.toString().replace("[SHOPNAME]", article.value("shopname","")), true) + "';" );
		
			var tmp_val = article.value("label_payment_delivery","");
			content = append( content, "public static $conf_label_payment_delivery = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_payment_advance","");
			content = append( content, "public static $conf_label_payment_advance = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_payment_bill","");
			content = append( content, "public static $conf_label_payment_bill = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_name","");
			content = append( content, "public static $conf_label_name = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_email","");
			content = append( content, "public static $conf_label_email = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_address","");
			content = append( content, "public static $conf_label_address = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_payment","");
			content = append( content, "public static $conf_label_payment = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_agb","");
			content = append( content, "public static $conf_label_agb = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_position","");
			content = append( content, "public static $conf_label_position = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_artikelnummer","");
			content = append( content, "public static $conf_label_artikelnummer = '" + system.htmlEncode(tmp_val) + "';" );
	
			tmp_val = article.value("label_menge","");
			content = append( content, "public static $conf_label_menge = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_bezeichnung","");
			content = append( content, "public static $conf_label_bezeichnung = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_einzelpreis","");
			content = append( content, "public static $conf_label_einzelpreis = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_gesamtpreis","");
			content = append( content, "public static $conf_label_gesamtpreis = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_entfernen","");
			content = append( content, "public static $conf_label_entfernen = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_zwischensumme","");
			content = append( content, "public static $conf_label_zwischensumme = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_versandkosten","");
			content = append( content, "public static $conf_label_versandkosten = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_gesamtsumme","");
			content = append( content, "public static $conf_label_gesamtsumme = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_basket","");
			content = append( content, "public static $conf_label_basket = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_product","");
			content = append( content, "public static $conf_label_product = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("head_basketpage","");
			content = append( content, "public static $conf_head_basketpage = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("head_orderpage","");
			content = append( content, "public static $conf_head_orderpage = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("help_fillform","");
			content = append( content, "public static $conf_help_fillform = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("help_emptybasket","");
			content = append( content, "public static $conf_help_emptybasket = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_updatebasket","");
			content = append( content, "public static $conf_label_updatebasket = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("label_goaddressform","");
			content = append( content, "public static $conf_label_goaddressform = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("text_thankyoupage","");
			tmp_val = tmp_val.toString().replace("\r\n", "\n");
			content = append( content, "public static $conf_text_thankyoupage = '" + system.htmlEncode(tmp_val, true) + "';" );
		
			tmp_val = article.value("error_name","");
			content = append( content, "public static $conf_error_name = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("error_email","");
			content = append( content, "public static $conf_error_email = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("error_address","");
			content = append( content, "public static $conf_error_address = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("error_payment","");
			content = append( content, "public static $conf_error_payment = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("error_agb","");
			content = append( content, "public static $conf_error_agb = '" + system.htmlEncode(tmp_val) + "';" );
	
			tmp_val = article.value("bgcolor_head","");
			content = append( content, "public static $conf_bgcolor_head = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("textcolor_head","");
			content = append( content, "public static $conf_textcolor_head = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("bgcolor_foot","");
			content = append( content, "public static $conf_bgcolor_foot = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("textcolor_foot","");
			content = append( content, "public static $conf_textcolor_foot = '" + system.htmlEncode(tmp_val) + "';" );
		
			//tmp_val = article.value("XXX");
			//content = append( content, "public static $conf_XXX = '" + system.htmlEncode(tmp_val) + "';" );
	
			var shippingCosts = article.valueRaw("shippingCosts","").toString().replace(",",".");
			
			content = append( content, "public static $conf_shippingcosts = " + shippingCosts +";" )
			content = append( content, "public static $conf_currency = '" + article.value("currency","") + "';" )
			content = append( content, "public static $conf_curr_comma = '" + article.value("currency_comma","") + "';" )
			if ( article.value("currency_thousands","") == "'" ) {
				content = append( content, "public static $conf_curr_thousands = '\\" + article.value("currency_thousands","") + "';" );
			}
			else {
				content = append( content, "public static $conf_curr_thousands = '" + article.value("currency_thousands","") + "';" );
			}
			content = append( content, "public static $conf_curr_decimals = '2';" );
			content = append( content, "public static $conf_payment_advance = '" + article.value("allowPaymentInAdvance","") + "';" );
			content = append( content, "public static $conf_payment_bill = '" + article.value("allowPaymentPerBill","") + "';" );
			content = append( content, "public static $conf_payment_delivery = '" + article.value("allowPaymentOnDelivery","") + "';" );
			content = append( content, "public static $conf_payment_paypal = '" + article.value("allowPaymentPerPayPal","") + "';" );
		
			content = append( content, "public static $conf_paypal_userid = '" + article.value("paypal_userid","") + "';" );
			content = append( content, "public static $conf_paypal_password = '" + article.value("paypal_password","") + "';" );
			content = append( content, "public static $conf_paypal_signature = '" + article.value("paypal_signature","") + "';" );
		
			tmp_val = article.value("paypal_buttontext","");
			content = append( content, "public static $conf_buttontext_paypal = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("paypal_label_payment","");
			content = append( content, "public static $conf_label_payment_paypal = '" + system.htmlEncode(tmp_val) + "';" );
		
			tmp_val = article.value("paypal_head_basketverify","");
			content = append( content, "public static $conf_head_paypalbasketverify = '" + system.htmlEncode(tmp_val) + "';" );
	
			content = append( content, "public static $conf_page_agb = '" + system.removeInlineEditing(article.valueExpanded("AGBPage","")) + "';" );
			content = append( content, "public static $conf_page_widerruf = '" + system.removeInlineEditing(article.valueExpanded("WiderrufPage","")) + "';" );
			content = append( content, "public static $conf_text_ordermailfooter = '" + article.value("OrderMailFooterText","") + "';" );
			content = append( content, "public static $conf_basketurl = '" + system.removeInlineEditing(page.absoluteUrl) + "';" );
			content = append( content, "public static $conf_mail_attachment1 = '" + system.removeInlineEditing(article.valueExpanded('attachment1', '')) + "';" );
			content = append( content, "public static $conf_mail_attachment2 = '" + system.removeInlineEditing(article.valueExpanded('attachment2', '')) + "';" );
			content = append( content, "public static $conf_basket_anchor = '#a" + article.id + "';");
			content = append( content, "}");
			content = append( content, "?>");

			// Wegschreiben und intern in Publish-Queue hinzuf�gen.
			system.writeFile( configFilePath, content );
			
			// The path for the PHP is somewhat different.
			return page.pathToRoot + "assets/php/shop/shop.inc.php";
	
		}
		// END - Make shop configuration
	
		 $>
		
		<?php include( dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . "<$= createShopConfig() $>"); ?> 

	<$ 
	} else {
		system.error("Falschen Dateityp vergeben. Bitte vergeben Sie in den Seiteneigenschaften dieser Seite den Dateityp \".php\".");
	}
	$>
	
	<$= system.partial("php-widget-end.html") $>

<$= system.partial("article-end.html") $>

<$
// Special workaround to get out some http headers before content is sent to the client

var text = "<?php \n";
text += "ob_start();"
text += "header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');\n";
text += "header('Last-Modified: ' . Gmdate('D, d M Y H:i:s') . ' GMT');\n";
text += "header('Cache-Control: no-store, no-cache, must-revalidate');\n";
text += "header('Cache-Control: post-check=0, pre-check=0', false);\n";
text += "header('Pragma: no-cache');\n";
text += "?>\n";

system.addFinishScript( text );
$>