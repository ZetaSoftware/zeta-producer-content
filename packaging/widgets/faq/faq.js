/*! 
 * ZP FAQ Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
 $z(document).ready(function () {
	// initialize TABs
	$z("p.zpSO-faq-question[id]").each(function (i){
		new zp.Faq().init("#" + this.id.toString(), i);
	});
	
	// show faq if we deep-linked to it
	if ( window.location.hash ){
	 $z("#" + window.location.hash.substring(2)).toggleClass("zpSO-faq-question-selected");
	 $z("#zpSO-faq-answer" + window.location.hash.substring(2)).slideToggle('fast');
	}
});

zp.Faq = function (){
	this.root = null;

	var faq = this;

	this.init = function (elemid, elementindex){
		faq.root = elemid;
	
		// define some styles based on settings received in data-attribute
		var head = document.getElementsByTagName('head')[0],
			style = document.createElement('style'),
			mystyles =  '.zpSO-faq-question span:before { content:"+"; display:inline-block; width:20px; }'
					  + '.zpSO-faq-question-selected a { font-weight:bold; }'
					  + '.zpSO-faq-question-selected span:before { content:"-"; }';	
	
		// inject styles 
		var rules = document.createTextNode(mystyles.replace(/\s+/g,' '));
		style.type = 'text/css';
		if(style.styleSheet){
				style.styleSheet.cssText = rules.nodeValue;
		}
		else{
			style.appendChild(rules);
		}
		if ( elementindex == 0 ){ //only append styles once
			head.appendChild(style);
		}
		
		$z(faq.root + " > a").click(function(e){
			e.preventDefault();
			var myID = $z(faq.root).attr("id");
			$z(faq.root).toggleClass('zpSO-faq-question-selected');
			$z('#zpSO-faq-answer' + myID).slideToggle('fast');
			// URL in Browser-Adressleiste aktualisieren, ohne dass der Browser die Seite lädt und zur Sprungmarke scrollt
			if (history.pushState){
				window.history.pushState("object or string", "Title", window.location.pathname + "#a" + myID);
			}
		});
	
	};
};
