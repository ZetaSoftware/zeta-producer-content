/*! 
 * ZP Form Validation
 * Copyright $Date:: 2014#$ Zeta Software GmbH
 */
$(document).ready(function () {
	// initialize Templating - simple Templating (replace template-string with URL parameter)
	// The template-string {@name} will be replaced with the value of the URLs Query-String named "name" or else be removed - templating is enabled for: input and textarea
	new zp.Templating().init();
		
	// initialize form validation	
	$("form.zp-form").each(function (){
		var formID = "#" + $(this).attr("id");
		
		$(this).submit(function (e){
			zpValidateForm(formID, e);
		});
		
	});
	
	// apply some global form Styling
	
	// hide the as field - this is already done in styles.css but old custom layouts might not have an up-to-date css
	$(".zp-form input.asfield").css("display", "none");
	
	// Hide form labels for browsers which support the placeholder attribute and if it is set
	if ($.support.placeholder){
		$(".zp-form input:text, .zp-form .typetext, .zp-form textarea").each(function (){
			if ( $(this).attr("placeholder") !== "" && $(this).attr("placeholder") !== undefined ){
				$(".zp-form label[for='"+$(this).attr('id')+"']").css({"height":"0px", "overflow":"hidden", "opacity":"0"});
			}
		});
	}		

	// globally style recaptcha tables in Forms and give them a description in the title
	$("#recaptcha_table").css({"border" : "1px solid #dfdfdf !important", "background-color" : "#ffffff"});
	$("#recaptcha_table").attr('title', 'Um sicherzustellen, dass dieser Formularservice nicht missbräuchlich verwendet wird, geben Sie bitte die 2 Wörter im Feld unten ein.');
		
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
		   // var encodedString = $("<div/>").text(decode(match[2])).html();
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
		$("form input[value*='{@'], form textarea:contains('{@')").each(function(){
			// iterate trough each placeholder pattern (search)
			var search = /({\@([^}]+)})/g;
			while ( match = search.exec($(this).val()) ){
				var re = new RegExp(match[1], "gi"); //sets a new pattern (i.e.: /{@name}/gi ) based on the found teplate string
		
				if ( urlParams[match[2]] ){ 
					// if the query string contains a value for the currently matched template, replace template with value
					$(this).val( $(this).val().replace(re, urlParams[match[2]]) );
				}
				else{
					// else, remove template (incl. trailing whitespace) string
					re = new RegExp(match[1] + "\\s*", "gi");
					$(this).val( $(this).val().replace(re, "") );
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
	var invalidEmailMsg = $("form"+ formID + " input[name='f_invalidEmailMsg']").val();
	if ( !invalidEmailMsg ){
		invalidEmailMsg = "keine gültige E-Mail";
	}
	
	function isValidEmail(email){
		var regex = /^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,63})$/i;
		return regex.test(email);
	}
	
	function ValidateField (formID,theField){
		if ( $("form" + formID + " *[name='F" + theField +"']").length === 0 ){ // it's probably a checkbox or select which has [] appended to the name to allow for multi values via a php array
			// reset error css styles
			$("form" + formID + " label[for='F" + theField + "']").css("color", "");
			$("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "");
				
			if ( $("form" + formID + " input:checkbox[name='F" + theField + "[]']").length && $("form" + formID + " input:checkbox[name='F" + theField + "[]']:checked").val() === undefined ) {
				fieldstofill += $("form" + formID + " #NAME" + theField).val() + ", ";
				returncode = false;
				$("form" + formID + " label[for='F" + theField + "']").css("color", "red");
				$("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "1px 1px 0 #ffffff");
			}
			
			if ( $("form" + formID + " select[name='F" + theField + "[]']").length && !$("form" + formID + " select[name='F" + theField + "[]']").val() ) {
				fieldstofill += $("form" + formID + " #NAME" + theField).val() + ", ";
				returncode = false;
				$("form" + formID + " label[for='F" + theField + "']").css("color", "red");
				$("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "1px 1px 0 #ffffff");
			}
		}
		else{
			// it's not checkbox but a textfield or radio
			if ( (
				  	$("form" + formID + " *[name='F" + theField +"']").attr('type') !== "radio" && 
				  	$("form" + formID + " *[name='F" + theField +"']").val() === ""
				 ) || 
				 (
					$("form" + formID + " *[name='F" + theField +"']").attr('type') == "email" && 
					!isValidEmail( $("form" + formID + " *[name='F" + theField +"']").val() )
				 ) ||
				 ( 
					$("form" + formID + " *[name='F" + theField +"']").attr('type') == "radio" && 
					$("form" + formID + " input:radio[name='F" + theField + "']").filter(":checked").val() === undefined
				 ) 
				) {
				
				var focusableFieldTypes = ["text","number","email","tel","url","date","time","color","search"];
				if ( focusableFieldTypes.indexOf($("form" + formID + " #F" + theField).attr('type')) !== -1 || $("form" + formID + " #F" + theField).is("textarea") ){
					focusablefields.push($("form" + formID + " #F" + theField)); 
				}
				if ( $("form" + formID + " #F" + theField).attr('type') == "email" && $("form" + formID + " *[name='F" + theField +"']").val() !== "" ){
					fieldstofill += $("form" + formID + " #NAME" + theField).val() + " (" + invalidEmailMsg + "), ";
				}
				else{
					fieldstofill += $("form" + formID + " #NAME" + theField).val() + ", ";
				}
				returncode = false;

				$("form" + formID + " label[for='F" + theField + "']").css("color", "red");
				$("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "1px 1px 0 #ffffff");
				$("form" + formID + " #F" + theField).css("border", "1px solid red");
			}
			else{
				// reset error css styles
				$("form" + formID + " label[for='F" + theField + "']").css("color", "");
				$("form" + formID + " label[for='F" + theField + "']").css("text-shadow", "");
				$("form" + formID + " #F" + theField).css("border", "");
			}
		}
	}

	// call ValidateField(339,1); -> "ValidateField(FormID,FieldINDEX);" for every required field AND for fields with input type="email" (regardless if required or not)
	$("form" + formID + " .required, " + "form" + formID + " input[type=email]").each(function (){
		var fieldName = $(this).attr("name");
		fieldName = fieldName.replace("F", "").replace("[]", "");
		ValidateField(formID, fieldName);
	});

	// do captcha validation if captcha is included in form
	if ( $("#recaptcha_challenge_field").length > 0 ){
		$.ajaxSetup({
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
		
		var action_url = $("form" + formID).attr("action");
		$.post(action_url, { verifycaptcha: "yes", recaptcha_challenge_field: $("#recaptcha_challenge_field").val(), recaptcha_response_field: $("#recaptcha_response_field").val() },
			function(data) {				
				if ( data !== "OK"){
					returncode = false;
					fieldstofill += "Spam-Schutz, ";
					$("form" + formID + " #recaptchalabel").css("color", "red");
					$("form" + formID + " #recaptchalabel").css("text-shadow", "1px 1px 0 #ffffff");
					$("form" + formID + " #recaptcha_widget_div").css("border", "1px solid red");
				}
				else{
					$("form" + formID + " #recaptchalabel").css("color", "");
					$("form" + formID + " #recaptchalabel").css("text-shadow", "");
					$("form" + formID + " #recaptcha_widget_div").css("border", "");
				}
			}
		);
	}

	fieldstofill = fieldstofill.substr(0, fieldstofill.length - 2); //delete last comma and blank
	if (fieldstofill !== ""){
		var alertPrefix = $("form"+ formID + " input[name='f_alertPrefix']").val();
		if ( !alertPrefix ){
			alertPrefix = "Bitte füllen Sie die rot markierten Felder aus:";
		}
		$("form"+ formID+ " .formvalidateerror").remove();
		$("form"+ formID).prepend('<div class="formvalidateerror" style="color: #fff; background-color: red; padding: 6px 12px;"><p>' + alertPrefix + '<br/><strong>' + fieldstofill + '</strong></p></div>');
		$('html, body').animate({
			scrollTop: $("form"+ formID+ " .formvalidateerror").first().offset().top - parseInt($("body").css("padding-top"))
		}, 500);
		
		$(focusablefields[0]).focus();
	}
	
	if ( !returncode ){
		e.preventDefault();
	}
	return returncode;
}
