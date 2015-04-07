/*! 
 * ZP Weather Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$(document).ready(function () {
	// initialize each weatherWidget
	$(".zpWeatherWidget[id]").each(function (){
		new zp.Weather().init("#" + this.id.toString());
	});
});

zp.Weather = function (){
	this.root = null;
	this.woeid = 0;
	this.temp = "c";
	this.color = "#ffffff";
	var weatherw = this;

	this.init = function (elemid){
		weatherw.root = elemid;
		weatherw.woeid = $(weatherw.root).data("woeid");
		weatherw.temp = $(weatherw.root).data("temp");
		weatherw.color = $(weatherw.root).css("color");
	
		var qurl = 'http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.forecast%20where%20woeid%3D%22' + weatherw.woeid + '%22%20and%20u%3D%22' + weatherw.temp + '%22&format=json';			
		$.ajax({ 
			type: "GET",
			url: qurl,
			dataType: "jsonp",
			cache: true,
			contentType: "application/json",
			success: function(data) {
				var w=data.query.results.channel;
				$(weatherw.root).attr("title", "Das Wetter in " + w.location.city + ", " + w.location.country);
				$(weatherw.root + ' #weatherIcon').css({"background-image" : "url(http://l.yimg.com/a/i/us/nws/weather/gr/"+w.item.condition.code+"d.png)", "background-repeat" : "no-repeat"});
				$(weatherw.root + ' #weatherTemp').html('<span class="temp" style="font-size: 200%;">' + w.item.condition.temp + "&deg;" + w.units.temperature + "" + '</span><br />' + w.item.forecast[0].low + "&deg;C – " + w.item.forecast[0].high + '&deg;C');
				$(weatherw.root + ' #weatherToday').html("Heute<br />" + w.item.forecast[0].low + "&deg;" + w.units.temperature + " – " + w.item.forecast[0].high + '&deg;' + w.units.temperature + '<br /><img style="margin: 0; padding: 0; border: none; background: transparent;" width="100" src="' + "http://l.yimg.com/a/i/us/nws/weather/gr/"+w.item.forecast[0].code+"d.png" + '" />');
				$(weatherw.root + ' #weatherTomorrow').html("Morgen<br />" + w.item.forecast[1].low + "&deg;" + w.units.temperature + " – " + w.item.forecast[1].high + '&deg;' + w.units.temperature + '<br /><img style="margin: 0; padding: 0; border: none; background: transparent;"width="100" src="' + "http://l.yimg.com/a/i/us/nws/weather/gr/"+w.item.forecast[1].code+"d.png" + '" />');
				$(weatherw.root + ' #weatherAttribution').html('<a target="_new" href="' + w.image.link + '" style="text-decoration: none; color: ' + weatherw.color +';">' + w.image.title + '</a>');
			},
			error: function(xhr, status, error) {
					trace(xhr.status + " " + status + " " + error);
			},
			async: false
		});
	};
};
