/*! 
 * ZP Image-Gallery Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$(document).ready(function () {
	// initialize each zpImageGallery
	$(".zpImageGallery[id]").each(function (){
		new zp.ImageGallery().init("#" + this.id.toString());
	});
	
	//play each zpSlideshow (part of zpImageGallery)
	$(".zpSlideshow[id]").each(function (){
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
		// load fancybox js and css if it isn't loaded already
		var mySrc = $('script[src*="imagegallery.js"], script[src*="bundle.js"]').first().attr("src");
		// TODO: remove this: var jsReltivePath = mySrc.substr(0, mySrc.length - "bundle.js".length);
		var jsReltivePath = mySrc.substr(0, mySrc.lastIndexOf("/")+1);
		
		if (!$("link[href*='/js/fancybox/jquery.fancybox-1.3.4.css']").length){
			// append fancybox css link tag to head if it wasn't already in the document
			var headtg = document.getElementsByTagName('head')[0];
			var linktg = document.createElement('link');
			linktg.type = 'text/css';
			linktg.rel = 'stylesheet';
			linktg.href = jsReltivePath + 'js/fancybox/jquery.fancybox-1.3.4.css?v=1202';
			//linktg.title = 'Fancy';
			headtg.appendChild(linktg);
		}
		if(!($.fancybox)){
			// append after existing, known js if possible to keep loading order of CSS JS in correct order for improved loading speed
			if (!$('script[src*="app.js"], script[src*="bundle.js"]').first().length){
				$('<script type="text/javascript" src="' + jsReltivePath + 'js/fancybox/jquery.fancybox-1.3.4_easing-1.3.pack.js"></script>').appendTo("head");
			}
			else{
				$('script[src*="app.js"], script[src*="bundle.js"]').first().after('<script type="text/javascript" src="' + jsReltivePath + 'js/fancybox/jquery.fancybox-1.3.4_easing-1.3.pack.js"></script>');
			}
		}
		
		igal.root = elemid;
		igal.numbershow = $(igal.root).data("numbershow")!==0?true:false;
		igal.titleshow = $(igal.root).data("titleshow")!==0?true:false;
		igal.htmltitle = $(igal.root).data("htmltitle");
		igal.kind = $(igal.root).data("kind");
		igal.width = $(igal.root).data("width");
		if ( parseInt(igal.width) ){
			igal.width = igal.width + "px";
		}
		igal.height = $(igal.root).data("height");
		if ( parseInt(igal.height) ){
			igal.height = igal.height + "px";
		}
		// make it more responsive, as it used to be always fill layout width and cut off by news columns
		if ( igal.height == "auto" && $(igal.root).hasClass("zpSlideshow") ){
			igal.width = "auto";
			var factor = $(igal.root).data("maxheight") / $(igal.root).data("width");
			var newwidth = $(igal.root).parent().parent().width();
			var newhight = Math.round(newwidth * factor);
			$(igal.root).parent().width(newwidth + 2).height(newhight + 6);
			$(igal.root).width(newwidth + 2).height(newhight + 6);
			$(igal.root + " .slide").width(newwidth).height(newhight + 4).css({"paddingRight":"2px", "paddingBottom":"2px"});
			$(igal.root + " .slide .caption").width(newwidth - 2);
		}
		igal.bordercolor = $(igal.root).data("bordercolor");
		igal.borderwidth = $(igal.root).data("borderwidth");
		igal.margin = $(igal.root).data("margin");
		igal.titleposition = $(igal.root).data("titleposition");
		igal.zoom = $(igal.root).data("transition");
		igal.transition = $(igal.root).data("inner-transition");
		
		if (igal.transition === "none") {
			igal.changeFade = 0;
		} else {
			igal.changeFade = 100;
		}

		igal.slideshow = $(igal.root).data("slideshow")!==0?true:false;
		igal.slideshowinterval = $(igal.root).data("slideshowinterval") * 1000;
		igal.slideshowtimer = 0;
		igal.lang = $(igal.root).data("lang");
		
		$(igal.root + " .fancybox").fancybox({
			'hideOnContentClick': true,
			'padding': 0,	//Space between FancyBox wrapper and content
			'margin': 12,	//Space between viewport and FancyBox wrapper
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
			'overlayOpacity': 0.8,
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
					igal.slideshowtimer = setInterval($.fancybox.next, igal.slideshowinterval);
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
		sshow.slideshowinterval = $(sshow.root).data("slideshowinterval") * 1000;
		sshow.pauseonhover = $(sshow.root).data("pauseonhover")!==0?true:false;
		// hide all slides except first
		$(elemid + ' div.slide:not(:first)').css("opacity", "0");
		// start the slideshow
		sshow.slideshow = setInterval( function() { sshow.slideSwitch(elemid); }, sshow.slideshowinterval );
		// handle arrow keys
		$(sshow.root).hover(function() {
			if (sshow.pauseonhover){
				clearInterval(sshow.slideshow);
			}
			$(document).keydown(function(event) {
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
			 $(document).unbind("keydown");
			// restart the slideshow
			if (sshow.pauseonhover){
				sshow.slideshow = setInterval( function() { sshow.slideSwitch(elemid); }, sshow.slideshowinterval );
			}
		});
	};
	
	this.slideSwitch = function (elemid, direction) {
		direction = typeof direction !== 'undefined' ? direction : 'next';
	
		var $active = $(elemid + ' div.slide.active');
		if ( $active.length === 0 ){ $active = $(elemid + ' div.slide:first');}
		var $next;
		if ( direction === "next" ){
			$next = $active.next(".slide").length ? $active.next(".slide") : $(elemid + ' div.slide:first');
		}
		else if ( direction === "prev" ){
			$next = $active.prev(".slide").length ? $active.prev(".slide") : $(elemid + ' div.slide:last');
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
