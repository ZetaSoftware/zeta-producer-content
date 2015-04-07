/*!
 * $Id: app-src.js 27448 2014-08-14 08:38:17Z sseiz $
 * Copyright Zeta Software GmbH
 */
/* jshint strict: false, multistr: true, smarttabs:true, jquery:true, devel:true */

var nualc = navigator.userAgent.toLowerCase();

jQuery.support.placeholder = (function(){
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
function hoverToClickMenu() { 
	var listenEvent = 'ontouchend' in document.documentElement ? "touchend" : "click";
	// The stock browser on Android 4.X can't cancel touchend events and will thus always fire an additional click event, so we need to revert to click events StS 2015-02-24
	if ( nualc.indexOf("android 4") > -1 && nualc.indexOf("chrome") === -1 ) {
		listenEvent = "click";
	}
	var onClick; // this will be a function
	var firstClick = function(e) {
		onClick = secondClick;
		if ( $(e).parent().hasClass("clicked") || $(e).parents("div.nav-collapse.in").length > 0 || ($(e).parent().children("ul").css("display") == "block" && $(e).parent().children("ul").css("visibility") == "visible") ) {
			return true;
		}
		// add ".open" classname to parent li element so we can style it if we want
		$(e).parent().addClass("clicked");
		// in case suckerfish is used
		$(e).parent().children("ul").css({'display' : 'block', 'visibility' : 'visible'});
		return false;
	};
	var secondClick = function(e) {
		onClick = firstClick;
		return true;
	};
	onClick = firstClick;
	$(this).on( listenEvent , function() {
		return onClick($(this));
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
	var viewportmeta = $('meta[name=viewport]'); //document.querySelector('meta[name="viewport"]');
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
$(window).load(function(){
	$("body").addClass("loaded");
});
				
$(document).ready(function () {
	//define some helper css classes
		$("html").removeClass("no-js");
		$("body").addClass("js");
		// recognize IE (since IE10 doesn't support conditional comments anymore)
		// removed in jQuery 1.9, so be careful!
		if ($.browser.msie) {
			$("html").removeClass("notie");
			$("html").addClass("ie");
			$("html").addClass("ie" + parseInt($.browser.version, 10));
		}
		else if ($.browser.mozilla){
			$("html").addClass("mozilla");
		}
		
		// add a top-padding to html5 audio because firefox has a time indicator implemented as a bubble which would be cut off due to our overflow hidden grid system
		if ($.browser.mozilla){
			$("audio").animate({paddingTop: '+=12px'}, 0); // we only use .animate() here, as that is a convenient way to be able to add values to (possibly) existing values
		}
		
		if(is_touch_device()) {
			// add .touch class to body if we run on a touch device, so we can use the class in css (used e.g. in ONLINE-CMS)
			$("body").removeClass("notouch");
			$("body").addClass("touch");
			
			// fix for hover menues (which contain submenues) to make them work on touch devices
			$(".touchhovermenu li:has(li) > a").each(hoverToClickMenu);
		}
		else{
			// In case we want to substitute hover with click menues on non touch devices too
			$("body").removeClass("touch");
			$("body").addClass("notouch");
			$(".clickhovermenu li:has(li) > a").each(hoverToClickMenu);
		}
});

// define zp Namespace for later use in individual widgets
var zp = {  
}; // end zp

// make $.unique also work on arrays and not only DOM-Elements (without this, we have a problem with the EventCalendars in Chrome)
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
})(jQuery);

/*!
 * END $Id: app-src.js 27448 2014-08-14 08:38:17Z sseiz $ 
 */


