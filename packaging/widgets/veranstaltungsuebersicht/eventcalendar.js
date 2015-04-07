/*! 
 * ZP Eventcalendar Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$(document).ready(function () {
	// initialize each EventCalendar
	$(".eventCalendar[id]").each(function (){
		new zp.EventCalendar().init("#" + this.id.toString());
	});
});

zp.EventCalendar = function (){
	this.root = null;
	this.showfilter = 0;
	this.filterbackgroundcolor = "#E7E7E7";
	this.hide_passed = 0;
	this.venuelabel = "Ort";
	this.genrelabel = "Art";
	this.selalloption = "Alle";
	this.rowcolor = "silver";
	this.rowspacing = "20px";
	var ecal = this;

	this.init = function (elemid){
		// load jqueryui css and js if not already loaded
		var mySrc = $('script[src*="eventcalendar.js"], script[src*="bundle.js"]').first().attr("src");
		var jsReltivePath = mySrc.substr(0, mySrc.lastIndexOf("/")+1);
		
		if (!$("link[href*='/js/jqueryui/jquery-ui-1.8.19.custom.css']").length){
				// append after existing, known style if possible to keep loading order of CSS JS in correct order for improved loading speed
				if (!$('link[href*="/bundle.css"], link[href*="/styles.css"]').first().length){
					$('<link rel="stylesheet" type="text/css" href="' + jsReltivePath +'js/jqueryui/jquery-ui-1.8.19.custom.css" media="screen" />').appendTo("head");
				}
				else{
					$('link[href*="/bundle.css"], link[href*="/styles.css"]').first().after('<link rel="stylesheet" type="text/css" href="' + jsReltivePath +'js/jqueryui/jquery-ui-1.8.19.custom.css" media="screen" />');
				}
			}
			
			if (!($.ui)) {
				// append after existing, known js if possible to keep loading order of CSS JS in correct order for improved loading speed
				if (!$('script[src*="app.js"], script[src*="bundle.js"]').first().length){
					$('<script type="text/javascript" src="' + jsReltivePath + 'js/jqueryui/jquery-ui-1.8.19.custom.min.js"></script>').appendTo("head");
				}
				else{
					$('script[src*="app.js"], script[src*="bundle.js"]').first().after('<script type="text/javascript" src="' + jsReltivePath + 'js/jqueryui/jquery-ui-1.8.19.custom.min.js"></script>');
				}
			}
			
			if (!($.datepicker.regional['de'])) {
				if (!$('script[src*="app.js"], script[src*="bundle.js"]').first().length){
					$('<script type="text/javascript" src="' + jsReltivePath + 'js/jqueryui/i18n/jquery.ui.datepicker-de.js"></script>').appendTo("head");
				}
				else{
					$('script[src*="app.js"], script[src*="bundle.js"]').first().after('<script type="text/javascript" src="' + jsReltivePath + 'js/jqueryui/i18n/jquery.ui.datepicker-de.js"></script>');
				}
			}
			
			ecal.root = elemid;
			ecal.showfilter = $(ecal.root).data("showfilter");
			
			if($(ecal.root).data("filterbackgroundcolor") !== ""){
				ecal.filterbackgroundcolor = $(ecal.root).data("filterbackgroundcolor");
			}
			ecal.hide_passed = $(ecal.root).data("hide_passed");
			ecal.venuelabel = $(ecal.root).data("venuelabel");
			ecal.genrelabel = $(ecal.root).data("genrelabel");
			ecal.selalloption = $(ecal.root).data("selall");
			ecal.rowcolor = $(ecal.root).data("rowcolor");
			ecal.rowspacing = $(ecal.root).data("rowspace");
			
			// remove passed events if user set pref to hide these
			if (ecal.hide_passed){
				// set now to the current Date without any time, so we can easily compare dates
				var now = new Date().setHours(0,0,0,0);
				$(ecal.root + " .eventOverview div.event").each(function() {
					var tmp = $(this).data("evdate").split(".");
					var tmpdate = new Date(tmp[2], tmp[1]-1, tmp[0]); // WTF: JS counts months from 0-11!!!
					// if user pref is set to hide passed events, hide anything < today
					if (tmpdate < now){
						$(this).remove();
					}
				});
			}
			
			// init datepickers
			$(ecal.root + ' .datepicker').datepicker({
				showButtonPanel: true,
				changeMonth: true,
				changeYear: true,
				currentText: "Heute",
				closeText: "Schließen"
			});
			
			// filter if date inputs or venue/genre pulldowns change
			$(ecal.root + " input#datefrom-filter, " + ecal.root + " input#dateto-filter, " + ecal.root + " #evvenue-filter, " + ecal.root + " #evgenre-filter").change(function () { 
				ecal.filter(); 
				ecal.initselects();
			});
			// handle the "Show all" button
			$(ecal.root + " .resetfilters").click(function () { 
				// First show all events
				$(ecal.root + " .event").show();
				$(ecal.root + " select").children().remove(); //reset the selects, so first option is selected again
				$(ecal.root + " input#datefrom-filter").val("");
				$(ecal.root + " input#dateto-filter").val("");
				$(ecal.root + " .eventOverview div.error").remove();
				ecal.initselects();
			});
			
			// style the table with user defined bgcolor
			var head = document.getElementsByTagName('head')[0],
			style = document.createElement('style'),
			mystyles = " \n\
			#ui-datepicker-div{z-index: 200 !important; background-color: #ffffff !important;}\n\
			div.eventOverview{\n\
				width: 100%;\
				margin: 0;\
				padding: 0;\
				border-collapse: collapse;\
			}\
			div.eventOverview div.event{\
				padding: 10px;\
				overflow: hidden;\
			}\
			div.eventOverview div.event div.eventdate {\
				width: 28%;\
				float: left;\
				font-weight: bolder;\
			}\
			div.eventOverview div.event div.eventinfo{\
				width: 67%;\
				float: right;\
			}\
			div.eventOverview div.event div.eventinfo a.title{\
				font-weight: bolder;\
			}\
			div.eventdetail h2{\
				font-family: #val(pagetitle-font-family);\
				margin: 0 0 #val(articlearea-spacing) 0;\
				padding: 0;\
				font-size: #val(pagetitle-font-size);\
				font-weight: #val(pagetitle-font-weight);\
				line-height: normal;\
				color: #val(pagetitle-font-color);\
			}\
			div.eventdetail .ecinfo{\
				font-weight: bold;\
			}\
			.eventCalendar div.filter button.resetfilters{\
				display: inline-block; margin-top: 1em;\
			}" + "\n\n  " + ecal.root + " div.filter { \
				padding: 10px; \
				background-color: " + ecal.filterbackgroundcolor + "; \
				overflow: hidden; \
				-webkit-border-top-left-radius: 8px; \
				-webkit-border-top-right-radius: 8px; \
				-moz-border-radius-topleft: 8px; \
				-moz-border-radius-topright: 8px; \
				border-top-left-radius: 8px; \
				border-top-right-radius: 8px; \
			} \
			" + ecal.root + " div.filteritem{ \
				float: left; \
				margin-right: 20px; \
			} \
			" + ecal.root + " div.filteritem select, div.filteritem input{ \
				width: 120px; \
			} \
			" + ecal.root + " div.event { background-color: " + ecal.rowcolor + "; margin-bottom: " + ecal.rowspacing + "; }",				
			rules = document.createTextNode(mystyles.replace(/\s+/g,' '));
			style.type = 'text/css';
			if(style.styleSheet){
				style.styleSheet.cssText = rules.nodeValue;
			}
			else{
				style.appendChild(rules);
			}
			head.appendChild(style);

			// sort eventlist by date ASC
			$(ecal.root + " .eventOverview div.event").sort(function(a,b){
				var aDateA = $(a).attr("data-evdate").split(".");
				var aDateB = $(b).attr("data-evdate").split(".");
				var x = new Date(aDateA[2], aDateA[1], aDateA[0]),
				y = new Date(aDateB[2], aDateB[1], aDateB[0]);
				return ((x < y) ? 1 : ((x > y) ?  -1 : 0));
			}).each(function(){
				$(ecal.root + " .eventOverview").prepend(this);
			});
			
			// set labels in individual events according to user configuration
			$(ecal.root + " span.venuelabel").html(ecal.venuelabel + ":");
			$(ecal.root + " span.genrelabel").html(ecal.genrelabel + ":");
			
			// hide the filter if set by userpref
			if ( ecal.showfilter !== 1 ){
				$(ecal.root + " div.filter").hide();
			}
			
			ecal.initselects();
			ecal.filter();
		};
		this.filter = function () {
			// filter the event list based on filter set by user
			$(ecal.root + " .eventOverview div.event").show();

			var filter = ecal.root + " .eventOverview div.event";
			if ( $(ecal.root + " select#evvenue-filter").val() !== ecal.selalloption && $(ecal.root + " select#evvenue-filter").is(":visible") ){
				filter += '[data-evvenue="' + $(ecal.root + " select#evvenue-filter").val().replace(/"/g, '\\"') +'"]';
			}
			if ( $(ecal.root + " select#evgenre-filter").val() !== ecal.selalloption && $(ecal.root + " select#evgenre-filter").is(":visible") ){
				filter += '[data-evgenre="'+ $(ecal.root + " select#evgenre-filter").val().replace(/"/g, '\\"') +'"]';
			}
			
			$(ecal.root + " .eventOverview div.event").hide();
			// filter on venue and genre
			$(filter).show();
			
			// filter on date range from/to
			var fromdate = new Date($(ecal.root + " input#datefrom-filter").val().split(".")[2], $(ecal.root + " input#datefrom-filter").val().split(".")[1]-1, $(ecal.root + " input#datefrom-filter").val().split(".")[0]);
			var todate   = new Date($(ecal.root + " input#dateto-filter").val().split(".")[2], $(ecal.root + " input#dateto-filter").val().split(".")[1]-1, $(ecal.root + " input#dateto-filter").val().split(".")[0]);

			$(ecal.root + " .eventOverview div.event").each(function() {
				var tmp = $(this).data("evdate").split(".");
				var tmpdate = new Date(tmp[2], tmp[1]-1, tmp[0]); // WTF: JS counts months from 0-11!!!
				if (tmpdate < fromdate || tmpdate > todate){
					$(this).hide();
				}
			});
			
			// show message if no results found
			$(ecal.root + " .eventOverview div.error").remove();
			if ( $(ecal.root + " .eventOverview div.event:not(:hidden)").length < 1){
				if (!ecal.hide_passed && ecal.showfilter){
					$(ecal.root + " .eventOverview").append('<div class="error" style="padding: 10px; background: red; color:white; font-weight: bold; font-size: larger;">Es wurden keine Veranstaltungen gefunden.</div>');
				}
				else{
					$(ecal.root + " .eventOverview").append('<div class="error" style="background: transparent; color:inherit;">Es wurden keine Veranstaltungen gefunden.</div>');
				}
			}
		};
		this.initselects = function () {
			// fill the select pulldowns with content based on what is shown on screen (not what is in DOM)
			if ( $(ecal.root + " .event:hidden").length === 0 ){
				$(ecal.root + " .resetfilters").hide();
			}else{
				$(ecal.root + " .resetfilters").show();
			}
			// map() gets all results into an array, unique() removes duplicates and grep() removes empty results - unique() only works on sorted arrays
			ecal.populateSelect($(ecal.root + " #evvenue-filter"), $.grep($.unique($(ecal.root + " .event:not(:hidden)").map(function() { return $(this).data("evvenue");}).sort()), function(n){return(n);}), ecal.selalloption);
			ecal.populateSelect($(ecal.root + " #evgenre-filter"), $.grep($.unique($(ecal.root + " .event:not(:hidden)").map(function() { return $(this).data("evgenre");}).sort()), function(n){return(n);}), ecal.selalloption);
		};
		
		this.populateSelect = function (el, items, firstopt){
			// helper - populates select form element with options based
			var prevselected = $(el).val();
			if (items.length > 0){
				el.children().remove(); // remove existing options
				el.append('<option>' + firstopt + '</option>');
				$.each(items, function () {
					if ( this == prevselected){
						el.append('<option selected="selected">' + this + '</option>');
					}
					else{
						el.append('<option>' + this + '</option>');
					}
				});
			}
			if ( items.length < 1 ){ 									// The Only Option in the Select would be "All", so remove it alltogether, 
				if ( $(ecal.root + " .event:hidden").length === 0 ){ 	// but only if results aren't filtered
					$(el).parent().css('display','none');
			}
		}
			else if ( items.length < 2 ){ //nothing to select here, so make label Grey
				$(el).parent().css('font-style','italic');
				$(el).parent().css('display','');
			}
			else{
				$(el).parent().css('font-style','');
				$(el).parent().css('display','');
			}
		};

	};
