/*! 
 * ZP Teaser Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$(document).ready(function () {
	// ******* ZP Teaser *******
	$(".zpteasertext").toggle();
	$(".zphidelink").toggle();
	$(".zpteaserlink").click(function (event){
		event.preventDefault();
		$(this).toggle();
		$(this).next(".zpteasertext").slideDown("fast");
		$(this).nextAll(".zphidelink").toggle();
		return false;
	});
	$(".zphidelink").click(function (event){
		event.preventDefault();
		$(this).toggle();
		$(this).prevAll(".zpteasertext").slideUp("fast");
		$(this).prevAll(".zpteaserlink").toggle();
		return false;
	});
});

