/*!
 * $Id: app-src.js 27448 2014-08-14 08:38:17Z sseiz $
 * Copyright Zeta Software GmbH
 */
/* jshint strict: false, multistr: true, smarttabs:true, jquery:true, devel:true */

var nualc = navigator.userAgent.toLowerCase();

$z.support.placeholder = (function(){
    var i = document.createElement('input');
    return 'placeholder' in i;
})();

function trace(s) {
  try { console.log(s); } catch (e) { alert(s); }
}

// test for touch device
function is_touch_device() {
	return !!('ontouchstart' in window);
}

// Helper for Nav-Menues with hover effects to make them work via alternating clicks
// 1st Click will open the Submenues, 2nd Click will load the link associated with the clicked element
function hoverToClickMenu(element, breakpointMobileMenu, instancenumber) { 
	var listenEvent = 'ontouchend' in document.documentElement ? "touchend" : "click";
	var triangleMode = true;
	
	// The stock browser on Android 4.X can't cancel touchend events and will thus always fire an additional click event, so we need to revert to click events StS 2015-02-24
	if ( nualc.indexOf("android 4") > -1 && nualc.indexOf("chrome") === -1 ) {
		listenEvent = "click";
	}
	
	/* clean up added styles after the user resizes the browser window and might reach desktop resolution */
	if ( instancenumber == 1 && triangleMode && breakpointMobileMenu !== undefined  ) {
			var menuResizeTimer;
			
			var clearAddedStyles = function(){
				if (  $z(window).width() > parseInt(breakpointMobileMenu) ){					
					$z(".hoverToClickMenuAdded").children("ul").css({'display' : '', 'visibility' : ''});
					$z(".hoverToClickMenuAdded").removeClass("clicked").removeClass("open").removeClass("hoverToClickMenuAdded");
				}
			};
			
			$z(window).resize(function(e) {
				clearTimeout(menuResizeTimer);
				menuResizeTimer = setTimeout(clearAddedStyles, 250);
			});
	}

	var firstClick = function(e) {
		if ( breakpointMobileMenu !== undefined && listenEvent == "click" && $z(window).width() > parseInt(breakpointMobileMenu) ) {
			// we're NOT displaying a mobile menu, so return and don't modify the click behavior
			return true;
		}
				
		if ( triangleMode ){
			if ( (event.pageX - $z(element).offset().left) <= parseInt($z(element).css("padding-left")) || (event.pageX - $z(element).offset().left) > (parseInt($z(element).css("padding-left")) + $z(element).width() -10) ){
				// user clicked on triangle to the left or right of a link
				var hasVisibleChilds = $z(e).parent().children("ul").css("display") == "block" && $z(e).parent().children("ul").css("visibility") == "visible";
				if ( $z(e).parent().hasClass("open") ||  ($z(e).parent().hasClass("active") && hasVisibleChilds) ){
					$z(e).parent().removeClass("clicked").removeClass("open");
					$z(e).parent().children("ul").css({'display' : 'none', 'visibility' : ''});
				}
				else{
					$z(e).parent().addClass("hoverToClickMenuAdded").addClass("clicked").addClass("open");
					$z(e).parent().children("ul").css({'display' : 'block', 'visibility' : 'visible'});
				}
				event.preventDefault();
				return false;
			}	
			else{
				// user clicked directly on link, so we load the url immediately
				return true;
			}
		}
		else{
			var otherMenus = $z(e).parent().prevAll(".clicked").add($z(e).parent().nextAll(".clicked"));
			otherMenus.removeClass("clicked").removeClass("open");
			otherMenus.find("ul").css({'display' : '', 'visibility' : ''});
			otherMenus.find(".clicked").removeClass("clicked");
			otherMenus.find(".open").removeClass("open");
			var hasVisibleChilds = $z(e).parent().children("ul").css("display") == "block" && $z(e).parent().children("ul").css("visibility") == "visible";
		
			if ( $z(e).parent().hasClass("clicked") || hasVisibleChilds ){ // TODO ZP13 check layouts for incompatibilities due to commenting out this: || ($z(e).parent().children("xul").css("display") == "block" && $z(e).parent().children("xul").css("visibility") == "visible") ) {
				// element has been clicked before, so now we fire a click
				return true;
			}
			// element has been clicked for the first time, so we do not fire a click and only show submenues
		
			// add ".open" classname to parent li element so we can style it if we want
			$z(e).parent().addClass("clicked").addClass("open");
			// in case suckerfish is used
			$z(e).parent().children("ul").css({'display' : 'block', 'visibility' : 'visible'});
			return false;
		}
	};
	$z(element).on( listenEvent , function(e) {
		var firstClickResult = firstClick($z(this));
		if ( !firstClickResult ){
			event.preventDefault();
		}
		return firstClickResult;
	});
}

// For IE8 and earlier version which don't have native support for Date.now.
if (!Date.now) {
  Date.now = function() {
    return new Date().valueOf();
  };
}

// FIX Viewports for iOS Devices as they always report the short side for "device-width", even when in landscape
if (navigator.userAgent.match(/xxiPhone/i) || navigator.userAgent.match(/xxiPad/i)) {
	var viewportmeta = $z('meta[name=viewport]'); //document.querySelector('meta[name="viewport"]');
	if (viewportmeta) {
		// set viewport on first page load if in portrait orientation
		if ( window.orientation !== 0 && window.orientation !== 180 ){
			viewportmeta.attr("content", "width=device-height, initial-scale=1.0");
		}
		// add eventListener so viewport is set when device is rotated after the initial page load
		document.addEventListener('orientationchange', function () {
			if ( window.orientation !== 0 && window.orientation !== 180 ){
				viewportmeta.attr("content", "width=device-height, initial-scale=1.0");
			}
			else{
				viewportmeta.attr("content", "width=device-width, initial-scale=1.0");
			}
		}, false);
	}
}

// lets us define css classes which only apply after all assets are loaded
$z(window).load(function(){
	$z("body").addClass("loaded");
});
				
$z(document).ready(function () {
	if ( $z(".zpgrid").length === 0 ){
		// we have a layout that was not modified for ZP13 i.e. a copied Layout from Version 12.5 or earlier
		// layouts not modified have no div with class .zpgrid, so we append the class to the document body
		$z('.zparea[data-areaname="Standard"], .zparea[data-areaname="Banner"], .zparea[data-areaname="Footer"]').addClass("zpgrid copiedlayout");
	}

	//define some helper css classes
		$z("html").removeClass("no-js");
		$z("body").addClass("js");
		// recognize IE (since IE10 doesn't support conditional comments anymore)
		// removed in jQuery 1.9, so be careful!
		if ( $z.browser.msie || !!navigator.userAgent.match(/Trident.*rv\:11\./) ) {
			$z("html").removeClass("notie");
			$z("html").addClass("ie");
			$z("html").addClass("ie" + parseInt($z.browser.version, 10));
		}
		else if ($z.browser.mozilla){
			$z("html").addClass("mozilla");
		}
		
		// add a top-padding to html5 audio because firefox has a time indicator implemented as a bubble which would be cut off due to our overflow hidden grid system
		if ($z.browser.mozilla){
			$z("audio").animate({paddingTop: '+=12px'}, 0); // we only use .animate() here, as that is a convenient way to be able to add values to (possibly) existing values
		}
		
		if(is_touch_device()) {
			// add .touch class to body if we run on a touch device, so we can use the class in css (used e.g. in ONLINE-CMS)
			$z("body").removeClass("notouch");
			$z("body").addClass("touch");
			
			// fix for hover menues (which contain submenues) to make them work on touch devices
			$z(".touchhovermenu li:has(li) > a").each(function(i){
				hoverToClickMenu(this, undefined, i);
			});
		}
		else{
			// In case we want to substitute hover with click menues on non touch devices too
			$z("body").removeClass("touch");
			$z("body").addClass("notouch");
			var breakpointmobilemenu = $z(".clickhovermenu").data("breakpointmobilemenu");
			$z(".clickhovermenu li:has(li) > a").each(function(i){
				hoverToClickMenu(this, breakpointmobilemenu, i);
			});
		}
		
		// set correct dimensions for breakout elements which in CSS are only approximated due to problems with browsers handling scrollbars differently
		function setBreakout(){
			var bodyWidth = $z("body").outerWidth();
			$z(".supportsbreakout body:not(.withnews) .zpBreakout:not(.hasNews)").css("width",bodyWidth+"px").css("margin-left","calc(-" + bodyWidth/2 + "px + 50% )");
			$z("body.supportsbreakout:not(.withnews) .zpBreakout:not(.hasNews)").css("width",bodyWidth+"px").css("margin-left","calc(-" + bodyWidth/2 + "px + 50% )");
		}
		setBreakout();
		var resizeTimeout = null;
		$z(window).resize(function() {
			if (resizeTimeout) {
				clearTimeout(resizeTimeout);
			}
			// throttle the resize event
			resizeTimeout = setTimeout(function () {
				setBreakout();
			},100);
		});
});

// define zp Namespace for later use in individual widgets
var zp = {  
}; // end zp

// test HTML5 field-type support and store it in zp.html5support
zp.html5support = {number: false, email: false, tel: false, url: false, date: false, time: false, color: false, search: false};
var tester = document.createElement('input');
for(var i in zp.html5support){
	// Use try/catch because IE9 throws "Invalid Argument" when trying to use unsupported input types
	try {
		tester.type = i;
		if(tester.type === i){
			zp.html5support[i] = true;
		}
	} catch (e){}
}

// make $z.unique also work on arrays and not only DOM-Elements (without this, we have a problem with the EventCalendars in Chrome)
// http://stackoverflow.com/a/7366133
(function($){
    var _old = $.unique;
    $.unique = function(arr){
        // do the default behavior only if we got an array of elements
        if ( arr.length == 0  || !!arr[0].nodeType){
            return _old.apply(this,arguments);
        }
        else {
            // reduce the array to contain no dupes via grep/inArray
            return $.grep(arr,function(v,k){
                return $.inArray(v,arr) === k;
            });
        }
    };
})($z);

/*!
 * END $Id: app-src.js 27448 2014-08-14 08:38:17Z sseiz $ 
 */


