/*! 
 * ZP Eventcalendar Widget
 * Copyright Zeta Software GmbH
 */
$z(document).ready(function () {
	// initialize each EventCalendar
	$z(".eventCalendar[id]").each(function (){
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
		var mySrc = $z('script[src*="eventcalendar.js"], script[src*="bundle.js"]').first().attr("src");
		var jsReltivePath = mySrc.substr(0, mySrc.lastIndexOf("/")+1);
		
		if (!$z("link[href*='/js/jqueryui/jquery-ui-1.8.19.custom.css']").length){
				// append after existing, known style if possible to keep loading order of CSS JS in correct order for improved loading speed
				if (!$z('link[href*="/bundle.css"], link[href*="/styles.css"]').first().length){
					$z('<link rel="stylesheet" type="text/css" href="' + jsReltivePath +'js/jqueryui/jquery-ui-1.8.19.custom.css" media="screen" />').appendTo("head");
				}
				else{
					$z('link[href*="/bundle.css"], link[href*="/styles.css"]').first().after('<link rel="stylesheet" type="text/css" href="' + jsReltivePath +'js/jqueryui/jquery-ui-1.8.19.custom.css" media="screen" />');
				}
			}
			
			if (!($z.ui)) {
				// append after existing, known js if possible to keep loading order of CSS JS in correct order for improved loading speed
				if (!$z('script[src*="app.js"], script[src*="bundle.js"]').first().length){
					$z('<script type="text/javascript" src="' + jsReltivePath + 'js/jqueryui/jquery-ui-1.8.19.custom.min.js"></script>').appendTo("head");
				}
				else{
					$z('script[src*="app.js"], script[src*="bundle.js"]').first().after('<script type="text/javascript" src="' + jsReltivePath + 'js/jqueryui/jquery-ui-1.8.19.custom.min.js"></script>');
				}
			}
			
			if (!($z.datepicker.regional['de'])) {
				if (!$z('script[src*="app.js"], script[src*="bundle.js"]').first().length){
					$z('<script type="text/javascript" src="' + jsReltivePath + 'js/jqueryui/i18n/jquery.ui.datepicker-de.js"></script>').appendTo("head");
				}
				else{
					$z('script[src*="app.js"], script[src*="bundle.js"]').first().after('<script type="text/javascript" src="' + jsReltivePath + 'js/jqueryui/i18n/jquery.ui.datepicker-de.js"></script>');
				}
			}
			
			ecal.root = elemid;
			ecal.showfilter = $z(ecal.root).data("showfilter");
			
			if($z(ecal.root).data("filterbackgroundcolor") !== ""){
				ecal.filterbackgroundcolor = $z(ecal.root).data("filterbackgroundcolor");
			}
			ecal.hide_passed = $z(ecal.root).data("hide_passed");
			ecal.venuelabel = $z(ecal.root).data("venuelabel");
			ecal.genrelabel = $z(ecal.root).data("genrelabel");
			ecal.selalloption = $z(ecal.root).data("selall");
			ecal.rowcolor = $z(ecal.root).data("rowcolor");
			ecal.rowspacing = $z(ecal.root).data("rowspace");
			
			// remove passed events if user set pref to hide these
			if (ecal.hide_passed){
				// set now to the current Date without any time, so we can easily compare dates
				var now = new Date().setHours(0,0,0,0);
				$z(ecal.root + " .eventOverview div.event").each(function() {
					var tmp = $z(this).data("evdate").split(".");
					var tmpdate = new Date(tmp[2], tmp[1]-1, tmp[0]); // WTF: JS counts months from 0-11!!!
					// if user pref is set to hide passed events, hide anything < today
					if (tmpdate < now){
						$z(this).remove();
					}
				});
			}
			
			// init datepickers
			$z(ecal.root + ' .datepicker').datepicker({
				showButtonPanel: true,
				changeMonth: true,
				changeYear: true,
				currentText: "Heute",
				closeText: "Schließen"
			});
			
			// filter if date inputs or venue/genre pulldowns change
			$z(ecal.root + " input#datefrom-filter, " + ecal.root + " input#dateto-filter, " + ecal.root + " #evvenue-filter, " + ecal.root + " #evgenre-filter").change(function () { 
				ecal.filter(); 
				ecal.initselects();
			});
			// handle the "Show all" button
			$z(ecal.root + " .resetfilters").click(function () { 
				// First show all events
				$z(ecal.root + " .event").show();
				$z(ecal.root + " select").children().remove(); //reset the selects, so first option is selected again
				$z(ecal.root + " input#datefrom-filter").val("");
				$z(ecal.root + " input#dateto-filter").val("");
				$z(ecal.root + " .eventOverview div.error").remove();
				ecal.initselects();
			});
			
			// style the table with user defined bgcolor
			var head = document.getElementsByTagName('head')[0],
			style = document.createElement('style'),
			mystyles = " \n\
			" + ecal.root + " div.filter { \
				background-color: " + ecal.filterbackgroundcolor + "; \
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
			$z(ecal.root + " .eventOverview div.event").sort(function(a,b){
				var aDateA = $z(a).attr("data-evdate").split(".");
				var aDateB = $z(b).attr("data-evdate").split(".");
				var x = new Date(aDateA[2], aDateA[1], aDateA[0]),
				y = new Date(aDateB[2], aDateB[1], aDateB[0]);
				return ((x < y) ? 1 : ((x > y) ?  -1 : 0));
			}).each(function(){
				$z(ecal.root + " .eventOverview").prepend(this);
			});
			
			// set labels in individual events according to user configuration
			$z(ecal.root + " span.venuelabel").html(ecal.venuelabel + ":");
			$z(ecal.root + " span.genrelabel").html(ecal.genrelabel + ":");
			
			// hide the filter if set by userpref
			if ( ecal.showfilter !== 1 ){
				$z(ecal.root + " div.filter").hide();
			}
			
			ecal.initselects();
			ecal.filter();
		};
		this.filter = function () {
			// filter the event list based on filter set by user
			$z(ecal.root + " .eventOverview div.event").show();

			var filter = ecal.root + " .eventOverview div.event";
			if ( $z(ecal.root + " select#evvenue-filter").val() !== ecal.selalloption && $z(ecal.root + " select#evvenue-filter").is(":visible") ){
				filter += '[data-evvenue="' + $z(ecal.root + " select#evvenue-filter").val().replace(/"/g, '\\"') +'"]';
			}
			if ( $z(ecal.root + " select#evgenre-filter").val() !== ecal.selalloption && $z(ecal.root + " select#evgenre-filter").is(":visible") ){
				filter += '[data-evgenre="'+ $z(ecal.root + " select#evgenre-filter").val().replace(/"/g, '\\"') +'"]';
			}
			
			$z(ecal.root + " .eventOverview div.event").hide();
			// filter on venue and genre
			$z(filter).show();
			
			// filter on date range from/to
			var fromdate = new Date($z(ecal.root + " input#datefrom-filter").val().split(".")[2], $z(ecal.root + " input#datefrom-filter").val().split(".")[1]-1, $z(ecal.root + " input#datefrom-filter").val().split(".")[0]);
			var todate   = new Date($z(ecal.root + " input#dateto-filter").val().split(".")[2], $z(ecal.root + " input#dateto-filter").val().split(".")[1]-1, $z(ecal.root + " input#dateto-filter").val().split(".")[0]);

			$z(ecal.root + " .eventOverview div.event").each(function() {
				var tmp = $z(this).data("evdate").split(".");
				var tmpdate = new Date(tmp[2], tmp[1]-1, tmp[0]); // WTF: JS counts months from 0-11!!!
				if (tmpdate < fromdate || tmpdate > todate){
					$z(this).hide();
				}
			});
			
			// show message if no results found
			$z(ecal.root + " .eventOverview div.error").remove();
			if ( $z(ecal.root + " .eventOverview div.event:not(:hidden)").length < 1){
				if (!ecal.hide_passed && ecal.showfilter){
					$z(ecal.root + " .eventOverview").append('<div class="error" style="padding: 10px; background: red; color:white; font-weight: bold; font-size: larger;">Es wurden keine Veranstaltungen gefunden.</div>');
				}
				else{
					$z(ecal.root + " .eventOverview").append('<div class="error" style="background: transparent; color:inherit;">Es wurden keine Veranstaltungen gefunden.</div>');
				}
			}
		};
		this.initselects = function () {
			// fill the select pulldowns with content based on what is shown on screen (not what is in DOM)
			if ( $z(ecal.root + " .event:hidden").length === 0 ){
				$z(ecal.root + " .resetfilters").hide();
			}else{
				$z(ecal.root + " .resetfilters").show();
			}
			// map() gets all results into an array, unique() removes duplicates and grep() removes empty results - unique() only works on sorted arrays
			ecal.populateSelect($z(ecal.root + " #evvenue-filter"), $z.grep($z.unique($z(ecal.root + " .event:not(:hidden)").map(function() { return $z(this).data("evvenue");}).sort()), function(n){return(n);}), ecal.selalloption);
			ecal.populateSelect($z(ecal.root + " #evgenre-filter"), $z.grep($z.unique($z(ecal.root + " .event:not(:hidden)").map(function() { return $z(this).data("evgenre");}).sort()), function(n){return(n);}), ecal.selalloption);
		};
		
		this.populateSelect = function (el, items, firstopt){
			// helper - populates select form element with options based
			var prevselected = $z(el).val();
			if (items.length > 0){
				el.children().remove(); // remove existing options
				el.append('<option>' + firstopt + '</option>');
				$z.each(items, function () {
					if ( this == prevselected){
						el.append('<option selected="selected">' + this + '</option>');
					}
					else{
						el.append('<option>' + this + '</option>');
					}
				});
			}
			if ( items.length < 1 ){ 									// The Only Option in the Select would be "All", so remove it alltogether, 
				if ( $z(ecal.root + " .event:hidden").length === 0 ){ 	// but only if results aren't filtered
					$z(el).parent().css('display','none');
			}
		}
			else if ( items.length < 2 ){ //nothing to select here, so make label Grey
				$z(el).parent().css('font-style','italic');
				$z(el).parent().css('display','');
			}
			else{
				$z(el).parent().css('font-style','');
				$z(el).parent().css('display','');
			}
		};

	};
