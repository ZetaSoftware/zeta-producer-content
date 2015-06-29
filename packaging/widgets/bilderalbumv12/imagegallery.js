/*! 
 * ZP Image-Gallery Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
 
 // This code depends on jquery.fancybox css and js being loded via the global "_Shared"-Widget.
 
$z(document).ready(function () {
	// initialize each zpImageGallery
	$z(".zpImageGallery[id]").each(function (){
		new zp.ImageGallery().init("#" + this.id.toString());
	});
	
	//play each zpSlideshow (part of zpImageGallery)
	$z(".zpSlideshow[id]").each(function (){
		new zp.Slideshow().init("#" + this.id.toString());
	});
});

zp.ImageGallery = function (){
	this.root = null;
	this.numbershow = true;
	this.titleshow = false;
	this.htmltitle = "";
	this.kind = "gallery";
	this.width = 200;
	this.height = 150;
	this.bordercolor = "silver";
	this.borderwidth = 0;
	this.margin = 10;
	this.overlaycolor = "black";
	this.titleposition = "over"; /* inside, over - not supported by us: outside*/
	this.transition = "elastic"; /* elastic, fade, none */
	this.slideshow = false;
	this.slideshowinterval = 5000;
	this.slideshowtimer = 0;
	this.lang = "de";
	//this.easing = "swing";
	//this.changefade = "fast";
	var igal = this;
	
	this.init = function (elemid){
		if(!($z.fancybox)){
			trace("Fehler: Fancybox ist nicht geladen");
		}
		
		igal.root = elemid;
		igal.numbershow = $z(igal.root).data("numbershow")!==0?true:false;
		igal.titleshow = $z(igal.root).data("titleshow")!==0?true:false;
		igal.htmltitle = $z(igal.root).data("htmltitle");
		igal.kind = $z(igal.root).data("kind");
		igal.width = $z(igal.root).data("width");
		if ( parseInt(igal.width) ){
			igal.width = igal.width + "px";
		}
		igal.height = $z(igal.root).data("height");
		if ( parseInt(igal.height) ){
			igal.height = igal.height + "px";
		}
		// make it more responsive, as it used to be always fill layout width and cut off by news columns
		if ( igal.height == "auto" && $z(igal.root).hasClass("zpSlideshow") ){
			igal.width = "auto";
			var factor = $z(igal.root).data("maxheight") / $z(igal.root).data("width");
			var newwidth = $z(igal.root).parent().parent().width();
			var newhight = Math.round(newwidth * factor);
			$z(igal.root).parent().width(newwidth + 2).height(newhight + 6);
			$z(igal.root).width(newwidth + 2).height(newhight + 6);
			$z(igal.root + " .slide").width(newwidth).height(newhight + 4).css({"paddingRight":"2px", "paddingBottom":"2px"});
			$z(igal.root + " .slide .caption").width(newwidth - 2);
		}
		igal.bordercolor = $z(igal.root).data("bordercolor");
		igal.borderwidth = $z(igal.root).data("borderwidth");
		igal.margin = $z(igal.root).data("margin");
		igal.titleposition = $z(igal.root).data("titleposition");
		igal.zoom = $z(igal.root).data("transition");
		igal.transition = $z(igal.root).data("inner-transition");
		
		if (igal.transition === "none") {
			igal.changeFade = 0;
		} else {
			igal.changeFade = 100;
		}

		igal.slideshow = $z(igal.root).data("slideshow")!==0?true:false;
		igal.slideshowinterval = $z(igal.root).data("slideshowinterval") * 1000;
		igal.slideshowtimer = 0;
		igal.lang = $z(igal.root).data("lang");
		
		$z(igal.root + " .fancybox").fancybox({
			'hideOnContentClick': true,
			'padding': 0,	//Space between FancyBox wrapper and content
			'margin': 30,	//Space between viewport and FancyBox wrapper
			'cyclic' : true, 
			'changeSpeed'		: 0, 
			'changeFade'		: igal.changeFade,
			'speedIn'	: 300,
			'speedOut'	: 300,
			'transitionIn'	: igal.zoom,
			'transitionOut'	: igal.zoom, 
			'easingIn'			: 'easeOutCubic', 
			'easingOut'			: 'easeInCubic', 
			'titleShow'			: igal.titleshow,
			'overlayColor'	: igal.overlaycolor,
			'overlayOpacity': 0.85,
			'titlePosition'	: igal.titleposition,
			'titleFormat'		: function(title, currentArray, currentIndex) {
				if ( typeof igal.htmltitle != 'undefined' && igal.htmltitle !== null && igal.htmltitle !== "" ){
					title = igal.htmltitle;
				}
				if ( currentArray.length > 1 && igal.numbershow ){
					if ( igal.lang === "en" ){
						return '<span id="fancybox-title-over">Image ' +  (currentIndex + 1) + ' of ' + currentArray.length + (title ? ": " + title : "") + '</span>';
					}
					else{
						return '<span id="fancybox-title-over">Bild ' +  (currentIndex + 1) + ' von ' + currentArray.length + (title ? ": " + title : "") + '</span>';
					}
					
				}
				else{
					return '<span id="fancybox-title-over">' +  title + '</span>';
				}
			},
			'onComplete'		: function(){
				//if user set slideshow = 1 then play slideshow
				if ( igal.slideshow ){
					igal.slideshowtimer = setInterval($z.fancybox.next, igal.slideshowinterval);
				}
			},
			'onCleanup'		: function(){
				//if user set slideshow = 1 then stop slideshow on close of fancybox
				if ( igal.slideshow ){
					clearInterval(igal.slideshowtimer);
				}
			}
		});
			
		// style the fancybox anchors
		var head = document.getElementsByTagName('head')[0],
		style = document.createElement('style'),
		mystyles = "	" + igal.root + "{overflow: hidden;";
		if (igal.kind === "singleimageleft"){
			mystyles += " width: " + igal.width + "; min-height: " + igal.height + "; float: left; margin-right: 20px; margin-bottom: 5px;";
		}
		else if (igal.kind === "singleimageright"){
			mystyles += " width: " + igal.width + "; min-height: " + igal.height + "; float: right; margin-left: 20px; margin-bottom: 5px;";
		}
		else if (igal.kind === "singleimagecenter"){
			mystyles += " width: " + igal.width + "; float: none !important; margin: 0 auto 5px auto;";
		}
		else if (igal.kind === "singleimage"){
			mystyles += " width: " + igal.width + "; float: none !important; margin-bottom: 5px;";
		}
		else if (igal.kind === "singleimager"){
			mystyles += " width: " + igal.width + "; float: none !important; margin: 0 0 5px auto;";
		}
		mystyles += "}";
		mystyles += igal.root + " > a, " + igal.root + " .slide > a { \
				width: " + igal.width + "; \
				height: " + igal.height + "; \
				box-sizing: border-box; \
				margin: 0 " + igal.margin + "px " + igal.margin + "px 0; ";
				
		if ( igal.bordercolor == "default" ){
			mystyles += "";
		}
		else if ( igal.bordercolor !== "transparent" ){
			mystyles += "	border: " + igal.borderwidth + "px solid " + igal.bordercolor + "; box-sizing: border-box;";
		}
		else{
			mystyles += "	border: none " + ";";
		}
		
		mystyles += "		padding: 0px; \
				display: block; \
				text-align: center; \
				vertical-align: middle; \
				float: left; \
				overflow: hidden; \
			}";
			//we need to switch borders off, since they would possibly be cut off anyway when portrait and landscape imgs are mixed and imgs will be cropped
			mystyles += igal.root +" > a img { \
				border: none !important;}";
							
		var rules = document.createTextNode(mystyles.replace(/\s+/g,' '));
		style.type = 'text/css';
		if(style.styleSheet){
				style.styleSheet.cssText = rules.nodeValue;
		}
		else{
			style.appendChild(rules);
		}
		head.appendChild(style);
	};
};

zp.Slideshow = function (){
	this.root = null;
	this.slideshowinterval = 5000;
	this.pauseonhover = true;
	var sshow = this;
	
	this.init = function (elemid){
		sshow.root = elemid;
		sshow.slideshowinterval = $z(sshow.root).data("slideshowinterval") * 1000;
		sshow.pauseonhover = $z(sshow.root).data("pauseonhover")!==0?true:false;
		// hide all slides except first
		$z(elemid + ' div.slide:not(:first)').css("opacity", "0");
		// start the slideshow
		sshow.slideshow = setInterval( function() { sshow.slideSwitch(elemid); }, sshow.slideshowinterval );
		// handle arrow keys
		$z(sshow.root).hover(function() {
			if (sshow.pauseonhover){
				clearInterval(sshow.slideshow);
			}
			$z(document).keydown(function(event) {
				// 37 = left arrow - 39 = right arrow
				if ( event.which === 37 ){
					sshow.slideSwitch(elemid, "prev");
				}
				else if ( event.which === 39 ){
					sshow.slideSwitch(elemid, "next");
				}
			});
		}, function() {
			// unbind the keydown handler on mouseleave
			 $z(document).unbind("keydown");
			// restart the slideshow
			if (sshow.pauseonhover){
				sshow.slideshow = setInterval( function() { sshow.slideSwitch(elemid); }, sshow.slideshowinterval );
			}
		});
	};
	
	this.slideSwitch = function (elemid, direction) {
		direction = typeof direction !== 'undefined' ? direction : 'next';
	
		var $active = $z(elemid + ' div.slide.active');
		if ( $active.length === 0 ){ $active = $z(elemid + ' div.slide:first');}
		var $next;
		if ( direction === "next" ){
			$next = $active.next(".slide").length ? $active.next(".slide") : $z(elemid + ' div.slide:first');
		}
		else if ( direction === "prev" ){
			$next = $active.prev(".slide").length ? $active.prev(".slide") : $z(elemid + ' div.slide:last');
		}

		$active.addClass('last-active')
			.css({"z-index": 101});

		$next.css({"opacity": "0.0"})
				.addClass('active')
				.css({"z-index": 102}) 
				.animate({"opacity": "1.0"}, 500, function() {
						$active.removeClass('active last-active')
						.css({"z-index": "100", "opacity": "0"});
		});
	};
};
