/*! 
 * ZP Smooth Scroll to Top
 * $Id: gototop.js 31433 2015-07-09 11:35:56Z ukeim $
 * Copyright Zeta Software GmbH 2015
 */

$z(document).ready(function () {
	$z("a.zpSO-Uplink").click(function(e){
		e.preventDefault();
		$z('html,body').animate({
				scrollTop: $z(this.hash).offset().top - parseInt($z("body").css("padding-top"))
		}, 500);
		window.location.hash = this.hash;
	});
});
