/*! 
 * ZP Teaser Widget
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$z(document).ready(function () {
	// ******* ZP Teaser *******
	$z(".zpteasertext").toggle();
	$z(".zphidelink").toggle();
	$z(".zpteaserlink").click(function (event){
		event.preventDefault();
		$z(this).toggle();
		$z(this).next(".zpteasertext").slideDown("fast");
		$z(this).nextAll(".zphidelink").toggle();
		return false;
	});
	$z(".zphidelink").click(function (event){
		event.preventDefault();
		$z(this).toggle();
		$z(this).prevAll(".zpteasertext").slideUp("fast");
		$z(this).prevAll(".zpteaserlink").toggle();
		return false;
	});
});

