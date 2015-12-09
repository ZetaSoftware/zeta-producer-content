/*! 
 * ZP Form Validation
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$z(document).ready(function () {
	// helper function to figure out when to hide labels and siaplay them as placeholder instead
	function hideLabel(theField){
		// Check for HTML5-Fieldtypes which fill the field with widgets, so the placeholder text isn't visible
		var fieldsWithoutPlaceholder = ["date","time","color"];
		var fieldType = $z(theField).attr("type");
		if ( fieldsWithoutPlaceholder.indexOf(fieldType) !== -1 ){  
			// current field type was found in array of field types we want to ignore
			// check if the browser supports the current field type and if so, return false so we don't hide the label
			var i = document.createElement("input");
			i.setAttribute("type", fieldType);
			if (i.type !== "text"){
				return false;
			}	
		}
		
		if ( $z(theField).attr("placeholder") !== "" && $z(theField).attr("placeholder") !== undefined ){
			// placeholder attribute is set, so we hide the label
			return true;
		}
		return false;
	}
	
	// initialize Templating - simple Templating (replace template-string with URL parameter)
	// The template-string {@name} will be replaced with the value of the URLs Query-String named "name" or else be removed - templating is enabled for: input and textarea
	new zp.Templating().init();
		
	// initialize form validation	
	$z("form.zp-form").each(function (){
		var formID = "#" + $z(this).attr("id");
		
		$z(this).submit(function (e){
			zpValidateForm(formID, e);
		});
		
	});
	
	// apply some global form Styling
	
	// hide the as field - this is already done in styles.css but old custom layouts might not have an up-to-date css
	$z(".zp-form input.asfield").css("display", "none");
	
	// Hide form labels for browsers which support the placeholder attribute and if it is set
	if ($z.support.placeholder){
		$z(".zp-form input:text, .zp-form .typetext, .zp-form textarea").each(function (){
			if ( hideLabel($z(this)) ) {
			//if ( $z(this).attr("placeholder") !== "" && $z(this).attr("placeholder") !== undefined ){
				$z(".zp-form label[for='"+$z(this).attr('id')+"']").css({"height":"0px", "overflow":"hidden", "opacity":"0"});
			}
		});
	}		

	// globally style recaptcha tables in Forms and give them a description in the title
	$z("#recaptcha_table").css({"border" : "1px solid #dfdfdf !important", "background-color" : "#ffffff"});
	$z("#recaptcha_table").attr('title', 'Um sicherzustellen, dass dieser Formularservice nicht missbräuchlich verwendet wird, geben Sie bitte die 2 Wörter im Feld unten ein.');
		
});

// replace macros inseretd in Forms with query string parameters
zp.Templating = function (){
	var templating = this;
	var urlParams;
	this.init = function (){
		// parse the query string and store all name/value pairs in an associative array (object)
		var match,
			pl     = /\+/g,  // Regex for replacing addition symbol with a space
			search = /([^&=]+)=?([^&]*)/g,
			decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
			query  = window.location.search.substring(1);
		urlParams = {};
		while (match = search.exec(query)){
		   // for safety reasons, encode html in the query string using jquery (http://stackoverflow.com/questions/1219860/html-encoding-in-javascript-jquery) - seems not needed in this context
		   // var encodedString = $z("<div/>").text(decode(match[2])).html();
		   // urlParams[decode(match[1])] = encodedString;
		   urlParams[decode(match[1])] = decode(match[2]);
		}
		/*
		for(var index in urlParams) {
			console.log( index + " : " + urlParams[index]);
		}
		*/
		
		// run the actual template-string to query string-value replacements
		templating.doTemplate();
	};
	
	this.doTemplate = function(){
		$z("form input[value*='{@'], form textarea:contains('{@')").each(function(){
			// iterate trough each placeholder pattern (search)
			var search = /({\@([^}]+)})/g;
			while ( match = search.exec($z(this).val()) ){
				var re = new RegExp(match[1], "gi"); //sets a new pattern (i.e.: /{@name}/gi ) based on the found teplate string
		
				if ( urlParams[match[2]] ){ 
					// if the query string contains a value for the currently matched template, replace template with value
					$z(this).val( $z(this).val().replace(re, urlParams[match[2]]) );
				}
				else{
					// else, remove template (incl. trailing whitespace) string
					re = new RegExp(match[1] + "\\s*", "gi");
					$z(this).val( $z(this).val().replace(re, "") );
				}
			}
		});
	};
};

// validate forms
function zpValidateForm(formID, e)
{
	var focusablefields = [];
	var fieldstofill = "";
	var returncode = true;
	var invalidEmailMsg = $z("form"+ formID + " input[name='f_invalidEmailMsg']").val();
	if ( !invalidEmailMsg ){
		invalidEmailMsg = "keine gültige E-Mail";
	}
	
	function isValidEmail(email){
		var regex = /^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,63})$/i;
		return regex.test(email);
	}
	
	function ValidateField (formID,theField){
		if ( $z("form" + formID + " *[name='F" + theField +"']").length === 0 ){ // it's probably a checkbox or select which has [] appended to the name to allow for multi values via a php array
			// reset error css styles
			$z("form" + formID + " label[for='F" + theField + "']").css("color", "");
			$z("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "");
				
			if ( $z("form" + formID + " input:checkbox[name='F" + theField + "[]']").length && $z("form" + formID + " input:checkbox[name='F" + theField + "[]']:checked").val() === undefined ) {
				fieldstofill += $z("form" + formID + " #NAME" + theField).val() + ", ";
				returncode = false;
				$z("form" + formID + " label[for='F" + theField + "']").css("color", "red");
				$z("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "1px 1px 0 #ffffff");
			}
			
			if ( $z("form" + formID + " select[name='F" + theField + "[]']").length && !$z("form" + formID + " select[name='F" + theField + "[]']").val() ) {
				fieldstofill += $z("form" + formID + " #NAME" + theField).val() + ", ";
				returncode = false;
				$z("form" + formID + " label[for='F" + theField + "']").css("color", "red");
				$z("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "1px 1px 0 #ffffff");
			}
		}
		else{
			// it's not checkbox but a textfield or radio
			if ( (
				  	$z("form" + formID + " *[name='F" + theField +"']").attr('type') !== "radio" && 
				  	$z("form" + formID + " *[name='F" + theField +"']").val() === ""
				 ) || 
				 (
					$z("form" + formID + " *[name='F" + theField +"']").attr('type') == "email" && 
					!isValidEmail( $z("form" + formID + " *[name='F" + theField +"']").val() )
				 ) ||
				 ( 
					$z("form" + formID + " *[name='F" + theField +"']").attr('type') == "radio" && 
					$z("form" + formID + " input:radio[name='F" + theField + "']").filter(":checked").val() === undefined
				 ) 
				) {
				
				var focusableFieldTypes = ["text","number","email","tel","url","date","time","color","search"];
				if ( focusableFieldTypes.indexOf($z("form" + formID + " #F" + theField).attr('type')) !== -1 || $z("form" + formID + " #F" + theField).is("textarea") ){
					focusablefields.push($z("form" + formID + " #F" + theField)); 
				}
				if ( $z("form" + formID + " #F" + theField).attr('type') == "email" && $z("form" + formID + " *[name='F" + theField +"']").val() !== "" ){
					fieldstofill += $z("form" + formID + " #NAME" + theField).val() + " (" + invalidEmailMsg + "), ";
				}
				else{
					fieldstofill += $z("form" + formID + " #NAME" + theField).val() + ", ";
				}
				returncode = false;

				$z("form" + formID + " label[for='F" + theField + "']").css("color", "red");
				$z("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "1px 1px 0 #ffffff");
				$z("form" + formID + " #F" + theField).css("border", "1px solid red");
			}
			else{
				// reset error css styles
				$z("form" + formID + " label[for='F" + theField + "']").css("color", "");
				$z("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "");
				$z("form" + formID + " #F" + theField).css("border", "");
			}
		}
	}

	// call ValidateField(339,1); -> "ValidateField(FormID,FieldINDEX);" for every required field 
	$z("form" + formID + " .required").each(function (){
		var fieldName = $z(this).attr("name");
		fieldName = fieldName.replace("F", "").replace("[]", "");
		ValidateField(formID, fieldName);
	});
	// validate not required E-Mail fields which are not empty
	// $z("form#form1630 input[type=email]:not(.required)")
	$z("form" + formID + " input[type=email]:not(.required)").each(function (){
		if ( $z(this).val() !== "" ){
			var fieldName = $z(this).attr("name");
			fieldName = fieldName.replace("F", "").replace("[]", "");
			ValidateField(formID, fieldName);
		}
	});
	
	// do captcha validation if captcha is included in form
	if ( $z("#recaptcha_challenge_field").length > 0 ){			// reCaptcha 1.0
		$z.ajaxSetup({
			error: function(jqXHR, exception) {
				if (jqXHR.status === 0) {
						alert('Not connected. Please verify your network.');
				} else if (jqXHR.status == 404) {
						alert('ERROR: Requested page not found. [404]');
				} else if (jqXHR.status == 500) {
						alert('ERROR: Internal Server Error [500].');
				} else if (exception === 'parsererror') {
						alert('ERROR: Requested JSON parse failed.');
				} else if (exception === 'timeout') {
						alert('ERROR: Time out error.');
				} else if (exception === 'abort') {
						alert('ERROR: Ajax request aborted.');
				} else {
						alert('ERROR: Uncaught Error.' + jqXHR.responseText);
				}
			},
			async: false
		});
		
		var action_url = $z("form" + formID).attr("action");
		$z.post(action_url, { verifycaptcha: "yes", recaptcha_challenge_field: $z("#recaptcha_challenge_field").val(), recaptcha_response_field: $z("#recaptcha_response_field").val() },
			function(data) {				
				if ( data !== "OK"){
					console.log(data);
					returncode = false;
					fieldstofill += $z("form" + formID + " #recaptchalabel").text() + ", ";
					$z("form" + formID + " #recaptchalabel").css("color", "red");
					$z("form" + formID + " #recaptchalabel").css("text-shadow", "1px 1px 0 #ffffff");
					$z("form" + formID + " #recaptcha_table").attr("style", "border: 1px solid red !important");
				}
				else{
					$z("form" + formID + " #recaptchalabel").css("color", "");
					$z("form" + formID + " #recaptchalabel").css("text-shadow", "");
					$z("form" + formID + " #recaptcha_table").removeAttr("style");
				}
			}
		);
	}
	
	if ( $z(".g-recaptcha").length > 0 ){ 				// reCaptcha 2.0
		var cr = grecaptcha.getResponse();
		if ( cr.length === 0 ){
			// captcha hasn't been filled/verified
			fieldstofill += $z("form" + formID + " #recaptchalabel").text() + ", ";
			$z("form" + formID + " #recaptchalabel").css("color", "red");
			$z("form" + formID + " #recaptchalabel").css("text-shadow", "1px 1px 0 #ffffff");
			$z("form" + formID + " .g-recaptcha").css("border", "1px solid red").css("width", "304px");
		}
		else{
			$z("form" + formID + " #recaptchalabel").css("color", "");
			$z("form" + formID + " #recaptchalabel").css("text-shadow", "");
			$z("form" + formID + " .g-recaptcha").css("border", "").css("width", "");
		}
	}

	fieldstofill = fieldstofill.substr(0, fieldstofill.length - 2); //delete last comma and blank
	if (fieldstofill !== ""){
		var alertPrefix = $z("form"+ formID + " input[name='f_alertPrefix']").val();
		if ( !alertPrefix ){
			alertPrefix = "Bitte füllen Sie die rot markierten Felder aus:";
		}
		$z("form"+ formID+ " .formvalidateerror").remove();
		$z("form"+ formID).prepend('<div class="formvalidateerror" style="color: #fff; background-color: red; padding: 6px 12px;"><p>' + alertPrefix + '<br/><strong>' + fieldstofill + '</strong></p></div>');
		$z('html, body').animate({
			scrollTop: $z("form"+ formID+ " .formvalidateerror").first().offset().top - parseInt($z("body").css("padding-top"))
		}, 500);
		
		$z(focusablefields[0]).focus();
	}
	
	if ( !returncode ){
		e.preventDefault();
	}
	return returncode;
}
