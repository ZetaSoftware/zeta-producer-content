/*! 
 * ZP Tabs Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$(document).ready(function () {
	// initialize TABs
	$("div.zpTABs[id]").each(function (){
		new zp.Tabs().init("#" + this.id.toString());
	});
	
	// accordion widget
	$('div.zpAccordion .accordion-heading').click(function(){
		$(this).parent().find(".accordion-content").slideToggle("fast");
		$(this).parent().toggleClass("active");
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
		if ( $(window).width() > tabBreakpoint ){
			// we only calculate tabwidth if TABs are displayed normally (> tabBreakpoint)
			$("div" + tabs.root + ".zpTABs ul.zpTABs li.tab").each( function(){ tabwidth += $(this).outerWidth();});
		}
		$( window ).resize(function() {
		  tabs.beresponsive(tabBreakpoint, tabwidth);
		});
		tabs.beresponsive(tabBreakpoint, tabwidth);
	
		tabs.bgcoloractive = $(tabs.root).data("bgcoloractive") || tabs.bgcoloractive;
		tabs.bgcolorinactive = $(tabs.root).data("bgcolorinactive") || tabs.bgcolorinactive;
		tabs.textcoloractive = $(tabs.root).data("textcoloractive") || tabs.textcoloractive;
		tabs.textcolorinactive = $(tabs.root).data("textcolorinactive") || tabs.textcolorinactive;
		tabs.bordercolor = $(tabs.root).data("bordercolor") || tabs.bordercolor;
	
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
	
		$(elemid + ' ul.zpTABs').each(function(){
			var currenttab = this;
			var numtabs = $(this).find('li.tab a.zpTABs').length;
			var lastclass = "";
		
			$(this).find('li.tab a.zpTABs').each(function(i){
				if ( i === numtabs ){ // last iteration
					lastclass = " last";
				}
				$($(this).attr('href')).clone().insertAfter($(this).parent());
				$($(this).attr('href')).wrap('<li id="' + $(this).attr('href').substring(1) + clonedidprefix + '" class="touchonly acc' + lastclass + '" />');
				$(currenttab).find("li.acc > div" + $(this).attr('href')).children().first().unwrap();
				// suffix all id's in cloned element with m
				$(this).parent().next().find("[id]").each(function(){
					this.id = this.id + clonedidprefix;
				});
				// suffix all for attribs of labels in cloned element with m
				$(this).parent().next().find("[for]").each(function(){
					$(this).attr("for", $(this).attr("for")+clonedidprefix);
				});
			});
		
			// http://blog.ha-com.com/2012/06/09/ein-jquery-tab-tutorial/
			// Fuer jeden Satz Tabs wollen wir verfolgen welcher
			// Tab aktiv ist und der ihm zugeordnete Inhalt
			var $active, $content, $mobilecontent, $links = $(this).find('a.zpTABs');
		
			if ( $(this).find("a.zpTABs[href='" + window.location.hash + "']").length > 0 ){
				// wenn ein Anker in der URL übergeben wurde, machen wir die ID zum aktiven TAB
				$("div" + tabs.root + ".zpTABs *").removeClass('active');
				$active = $(this).find("a.zpTABs[href='" + window.location.hash + "']");
			}
			else{
				// Der erste Link ist der zu Anfang akitve Tab
				$active = $links.first();
			}
		
			$($active).parent().addClass('active');
			$content = $($active.attr('href'));
			$mobilecontent = $($active.attr('href')+clonedidprefix);
		
			// Zeige nur den Inhalt des 1. Tabs
			$content.addClass("active");
			$mobilecontent.addClass("active");
		
			// Binde den click event handler ein
			$("a.zpTABs", this).click(function(e){
				// Mache den alten Tab inaktiv
				$active.parent().removeClass('active');
				$content.removeClass('active');
	
				// this allows for accordion like toggling on smallscreens
				if ( $(window).width() > tabBreakpoint || $active.attr("href") !== $(this).attr("href") ){
					$mobilecontent.removeClass('active');
				}
				
				// Aktualisiere die Variablen mit dem neuen Link und Inhalt
				$active = $(this);
				$content = $($(this).attr('href'));
				$mobilecontent = $($(this).attr('href')+clonedidprefix);
	
				// Setze den Tab aktiv
				$active.parent().addClass('active');
				$content.addClass('active');
				$mobilecontent.toggleClass('active');
			
				// URL in Browser-Adressleiste aktualisieren, ohne dass der Browser die Seite lädt und zur Sprungmarke scrollt
				if (history.pushState){
					window.history.pushState("object or string", "Title", window.location.pathname + $(this).attr("href"));
				}
			
				// Verhindere die Anker standard click Aktion
				e.preventDefault();
			});
		});
	
	};

	this.beresponsive = function (tabBreakpoint, tabwidth){
		var containerwidth = $("div" + tabs.root + ".zpTABs").outerWidth();
		if ( $(window).width() > tabBreakpoint && tabwidth === 0 ){
			// tabwidth was never set, because page was loaded on a smallscreen (< tabBreakpoint), we need to recalculate tab-width while displayed normally (> tabBreakpoint sans .small class)
			$("div" + tabs.root + ".zpTABs").removeClass("small");
			$("div" + tabs.root + ".zpTABs ul.zpTABs li.tab").each( function(){ tabwidth += $(this).outerWidth();});
		}
		if ( $(window).width() > tabBreakpoint && containerwidth < tabwidth ){
			$("div" + tabs.root + ".zpTABs").addClass("small smallcontainer");
		}
		else if ( $(window).width() < tabBreakpoint ){
			$("div" + tabs.root + ".zpTABs").addClass("small");
		}
		else{
			$("div" + tabs.root + ".zpTABs").removeClass("small");
		}
	};


};
