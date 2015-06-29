/*! 
 * ZP Tabs Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$z(document).ready(function () {
	// initialize TABs
	$z("div.zpTABs[id]").each(function (){
		new zp.Tabs().init("#" + this.id.toString());
	});
	
	// accordion widget
	$z('div.zpAccordion .accordion-heading').click(function(){
		$z(this).parent().find(".accordion-content").slideToggle("fast");
		$z(this).parent().toggleClass("active");
	});
});

zp.Tabs = function (){
	this.root = null;
	this.bgcoloractive = "#ffffff";
	this.bgcolorinactive = "#f7f7f7";
	this.textcoloractive = "#5e5e5e";
	this.textcolorinactive = "#777777";
	this.bordercolor = "#e7e7e7";

	var tabs = this;

	this.init = function (elemid){
		tabs.root = elemid;
		var clonedidprefix = "m";
		var tabBreakpoint = 768;
	
		// add class if container width is below tabBreakpoint, so the element is also responsive to its own width and not only the viewport
		var tabwidth = 0;
		if ( $z(window).width() > tabBreakpoint ){
			// we only calculate tabwidth if TABs are displayed normally (> tabBreakpoint)
			$z("div" + tabs.root + ".zpTABs ul.zpTABs li.tab").each( function(){ tabwidth += $z(this).outerWidth();});
		}
		$z( window ).resize(function() {
		  tabs.beresponsive(tabBreakpoint, tabwidth);
		});
		tabs.beresponsive(tabBreakpoint, tabwidth);
	
		tabs.bgcoloractive = $z(tabs.root).data("bgcoloractive") || tabs.bgcoloractive;
		tabs.bgcolorinactive = $z(tabs.root).data("bgcolorinactive") || tabs.bgcolorinactive;
		tabs.textcoloractive = $z(tabs.root).data("textcoloractive") || tabs.textcoloractive;
		tabs.textcolorinactive = $z(tabs.root).data("textcolorinactive") || tabs.textcolorinactive;
		tabs.bordercolor = $z(tabs.root).data("bordercolor") || tabs.bordercolor;
	
		// define some styles based on settings received in data-attribute
		var head = document.getElementsByTagName('head')[0],
		style = document.createElement('style'),
		mystyles =  "    div" + tabs.root + ".zpTABs > ul.zpTABs > li{ color: " + tabs.textcolorinactive + "; background-color: " + tabs.bgcolorinactive +"; border-color: " + tabs.bordercolor + ";}";
		mystyles += "    div" + tabs.root + ".zpTABs > ul.zpTABs > li a.zpTABs{ color: " + tabs.textcolorinactive + " !important;}";
		mystyles += "    div" + tabs.root + ".zpTABs > ul.zpTABs > li a.zpTABs:hover{ color: " + tabs.textcolorinactive + " !important;}";
		mystyles += "    div" + tabs.root + ".zpTABs > ul.zpTABs > li.active{ color: " + tabs.textcoloractive + "; background-color: " + tabs.bgcoloractive +"; border-color: " + tabs.bordercolor +";}";
		mystyles += "    div" + tabs.root + ".zpTABs > ul.zpTABs > li.active a.zpTABs{ color: " + tabs.textcoloractive + " !important;}";
		mystyles += "    div" + tabs.root + ".zpTABs > ul.zpTABs > li.active a.zpTABs:hover{ color: " + tabs.textcoloractive + " !important;}";
		mystyles += "    div" + tabs.root + ".zpTABs > ul.zpTABs > li.tab.active::after{ border-color: " + tabs.bordercolor + ";}";
		mystyles += "    div" + tabs.root + ".zpTABs > ul.zpTABs > li.tab.active::before{ border-color: " + tabs.bordercolor + ";}";
		mystyles += "    div" + tabs.root + ".zpTABs > ul.zpTABs > li.acc *:not(a){ color: " + tabs.textcoloractive + ";}";
	
		mystyles += "    div" + tabs.root + ".zpTABs > div.active{ border-color: " + tabs.bordercolor + "; background-color: " + tabs.bgcoloractive +";}";
		mystyles += "    div" + tabs.root + ".zpTABs > div.active *:not(a){ color: " + tabs.textcoloractive + ";}";
	
	
		// inject styles 
		var rules = document.createTextNode(mystyles.replace(/\s+/g,' '));
		style.type = 'text/css';
		if(style.styleSheet){
				style.styleSheet.cssText = rules.nodeValue;
		}
		else{
			style.appendChild(rules);
		}
		head.appendChild(style);
	
		$z(elemid + ' ul.zpTABs').each(function(){
			var currenttab = this;
			var numtabs = $z(this).find('li.tab a.zpTABs').length;
			var lastclass = "";
		
			$z(this).find('li.tab a.zpTABs').each(function(i){
				if ( i === numtabs ){ // last iteration
					lastclass = " last";
				}
				$z($z(this).attr('href')).clone().insertAfter($z(this).parent());
				$z($z(this).attr('href')).wrap('<li id="' + $z(this).attr('href').substring(1) + clonedidprefix + '" class="touchonly acc' + lastclass + '" />');
				$z(currenttab).find("li.acc > div" + $z(this).attr('href')).children().first().unwrap();
				// suffix all id's in cloned element with m
				$z(this).parent().next().find("[id]").each(function(){
					this.id = this.id + clonedidprefix;
				});
				// suffix all for attribs of labels in cloned element with m
				$z(this).parent().next().find("[for]").each(function(){
					$z(this).attr("for", $z(this).attr("for")+clonedidprefix);
				});
			});
		
			// http://blog.ha-com.com/2012/06/09/ein-jquery-tab-tutorial/
			// Fuer jeden Satz Tabs wollen wir verfolgen welcher
			// Tab aktiv ist und der ihm zugeordnete Inhalt
			var $active, $content, $mobilecontent, $links = $z(this).find('a.zpTABs');
		
			if ( $z(this).find("a.zpTABs[href='" + window.location.hash + "']").length > 0 ){
				// wenn ein Anker in der URL übergeben wurde, machen wir die ID zum aktiven TAB
				$z("div" + tabs.root + ".zpTABs *").removeClass('active');
				$active = $z(this).find("a.zpTABs[href='" + window.location.hash + "']");
			}
			else{
				// Der erste Link ist der zu Anfang akitve Tab
				$active = $links.first();
			}
		
			$z($active).parent().addClass('active');
			$content = $z($active.attr('href'));
			$mobilecontent = $z($active.attr('href')+clonedidprefix);
		
			// Zeige nur den Inhalt des 1. Tabs
			$content.addClass("active");
			$mobilecontent.addClass("active");
		
			// Binde den click event handler ein
			$z("a.zpTABs", this).click(function(e){
				// Mache den alten Tab inaktiv
				$active.parent().removeClass('active');
				$content.removeClass('active');
	
				// this allows for accordion like toggling on smallscreens
				if ( $z(window).width() > tabBreakpoint || $active.attr("href") !== $z(this).attr("href") ){
					$mobilecontent.removeClass('active');
				}
				
				// Aktualisiere die Variablen mit dem neuen Link und Inhalt
				$active = $z(this);
				$content = $z($z(this).attr('href'));
				$mobilecontent = $z($z(this).attr('href')+clonedidprefix);
	
				// Setze den Tab aktiv
				$active.parent().addClass('active');
				$content.addClass('active');
				$mobilecontent.toggleClass('active');
			
				// URL in Browser-Adressleiste aktualisieren, ohne dass der Browser die Seite lädt und zur Sprungmarke scrollt
				if (history.pushState){
					window.history.pushState("object or string", "Title", window.location.pathname + $z(this).attr("href"));
				}
			
				// Verhindere die Anker standard click Aktion
				e.preventDefault();
			});
		});
	
	};

	this.beresponsive = function (tabBreakpoint, tabwidth){
		var containerwidth = $z("div" + tabs.root + ".zpTABs").outerWidth();
		if ( $z(window).width() > tabBreakpoint && tabwidth === 0 ){
			// tabwidth was never set, because page was loaded on a smallscreen (< tabBreakpoint), we need to recalculate tab-width while displayed normally (> tabBreakpoint sans .small class)
			$z("div" + tabs.root + ".zpTABs").removeClass("small");
			$z("div" + tabs.root + ".zpTABs ul.zpTABs li.tab").each( function(){ tabwidth += $z(this).outerWidth();});
		}
		if ( $z(window).width() > tabBreakpoint && containerwidth < tabwidth ){
			$z("div" + tabs.root + ".zpTABs").addClass("small smallcontainer");
		}
		else if ( $z(window).width() < tabBreakpoint ){
			$z("div" + tabs.root + ".zpTABs").addClass("small");
		}
		else{
			$z("div" + tabs.root + ".zpTABs").removeClass("small");
		}
	};


};
