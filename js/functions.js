"use strict";


// ===================================================================
// Global variables
// ===================================================================

var DEFAULT_NOTEPAD_HEIGHT = '200';   // px
var DEFAULT_NOTEPAD_WIDTH = '750';    // px

var AUTOSAVE_SECONDS = 60;
var AUTOSAVE_MAX_SIZE = 32000;   // Don't autosave over 32K.

// How long the status indicator (ie, color of the box) lasts.
var INDICATOR_FEEDBACK_SECONDS = 5;

// Not currently used: var PROGRESS_IMG_URL = 'img/waiting.gif';

// Keep database queries below the max. Account for query overhead.
var MAX_NOTEPAD_SIZE = 900000;   // = 900K

// Globals.
var globalUsername = '';
var globalPassword = '';
var autosaveTimer;
var statusTimer;
var progressBar;
var apiMutex = false;


// ===================================================================
// Function declarations
// ===================================================================


// -------------------------------------------------------------------
// Misc
// -------------------------------------------------------------------

function resetStatusIndicator() {
	// First remove the inline border color or it will persist.
	$('#status-box').css('border-color', '');
	$('#status-box').removeClass('status-box-update').addClass('status-box-normal');
}

// Give a visual feedback on the state of the last status.
// Use recursively to restore the original color.
// statusColor = hex str, resetColor = bool
function setStatusIndicator(statusColor, resetColor) {
	// Explicitly set the border attributes because the border may not exist
	// before the first time the color is explicitly set.
	$('#status-box').removeClass('status-box-normal').addClass('status-box-update');
	$('#status-box').css('border-color', statusColor);

	clearTimeout(statusTimer);
	if (resetColor == true) {
		statusTimer = setTimeout(function(){ resetStatusIndicator(); },
			INDICATOR_FEEDBACK_SECONDS * 1000);   // Milliseconds
	}
}

// Have less padding when a status is displayed.
function showStatusBox() {
	// The appearence of the status box should be smooth, so have the growing of the box match the
	// shrinking of the top padding.
	$('#status-box').slideDown('fast', 'linear');
	$('#page-content').css('padding-top', '10px').animate('fast', 'linear');
}

function hideStatusBox() {
	// The disappearence of the status box doesn't need to be smooth right now.
	$('#status-box').slideUp('fast', 'linear');
	$('#page-content').css('padding-top', '25px');
}

// displayStatus = {'none', 'success', 'failure', 'alert'}.
function setStatusText(displayStatus, statusText) {
	showStatusBox();

	switch (displayStatus) {
		// No change.
		case 'none':
		break;

		case 'success':
			setStatusIndicator('#22bb22', true);
		break;

		case 'failure':
			setStatusIndicator('#bb2222', true);
		break;
		case 'alert':
			setStatusIndicator('#aa4000', true);
		break;
	}

	$('#status-text').html(statusText);

	// Optional: Make it disappear after a certain time.
}

// Sizes are integers and in pixels.
// The notepad element has it's "box-sizing" property set to "border-box".
function adjustNotepadSize(method, newHeight, newWidth) {
	var notepadElmt, finalHeight = 0, finalWidth = 0;

	// Sanity checks and special case handling.
	if (newHeight > 3000) {
		newHeight = 3000;
	}
	if (newWidth > 3000) {
		newWidth = 3000;
	}

	// Adjust the size of the notepad. Use the 'method' to explicitly set the absolute new size
	// or to merely increment the current size by the specified amount.
	switch (method) {
		case 'set':
			// Special case of 0 means to reset to default.
			if (newHeight === 0) {
				finalHeight = DEFAULT_NOTEPAD_HEIGHT;
			}
			else {
				finalHeight = newHeight;
			}

			if (newWidth === 0) {
				finalWidth = DEFAULT_NOTEPAD_WIDTH;
			}
			else {
				finalWidth = newWidth;
			}

			$('#notepad').outerHeight(finalHeight.toString() + 'px');
			$('#notepad').outerWidth(finalWidth.toString() + 'px');
		break;
		case 'increment':
			// Special case of 0 means do not increment.
			if (newHeight !== 0) {
				finalHeight = newHeight + parseInt($('#notepad').outerHeight());
				$('#notepad').outerHeight(finalHeight.toString() + 'px');
			}
			if (newWidth !== 0) {
				finalWidth = newWidth + parseInt($('#notepad').outerWidth());
				$('#notepad').outerWidth(finalWidth.toString() + 'px');
			}
		break;
	}

}

// Input in hex.
function setNotepadFontColor(color) {
	$('#notepad').css('color', color);
	$('#font-colors').val(color);
}

// Input in hex.
function setNotepadBackgroundColor(color) {
	$('#notepad').css('background-color', color);
	$('#background-colors').val(color);
}

// A pair of functions to set which display block is shown on the home page.
// Adjust the element that is not shown first to avoid any brief flickers of multiple boxes.
function setBodyToLogin() {
	window.location.hash = '';
	$('#notepad-block').slideUp('fast', 'linear');
	$('#login-block').show();
}

function setBodyToNotepad() {
	$('#login-block').hide();
	$('#notepad-block').slideDown('fast', 'linear', function(){
			window.location.hash = 'form-top';
		});
}

function resetNotepad() {
	adjustNotepadSize('set', DEFAULT_NOTEPAD_HEIGHT, DEFAULT_NOTEPAD_WIDTH);
	/*set_notepad_font_color();
	set_notepad_background_color();*/
	$('#autosave').prop('checked', false);
}

function autosaveNotepad(async) {
	// Only issue the save if auto-save is selected.
	// This implicitly means that the only notepad modification that is not auto-saved
	// is the act of turning auto-save off. This is a good thing, because it
	// allows for it to be turned off temporarily. We turn it off automatically for
	// large notepads, but want it to be temporary by default when we do so.
	if ($('#autosave').prop('checked') === true) {
		// Don't auto-save gigantic notepad contents.
		if ($('#notepad').val().length > AUTOSAVE_MAX_SIZE) {
			$('#autosave').prop('checked', false);
			setStatusText('alert', "Looks like you're making good use of your notepad. :-) " +
			              "You're welcome to store lots of text, but auto-save is disabled for " +
			              "large notepads. You can continue to save manually and re-enable " +
			              "auto-save once the notepad is smaller.");
		}
		else {
			saveNotepadApi('auto', async);
		}
	}
}

// This thread runs regardless of whether auto-saving is enabled.
// mode = 'start' or 'stop'
function autosaveThread(mode) {
	// Start/restart the wait.
	if (mode === 'start') {
		clearInterval(autosaveTimer);
		autosaveTimer = setInterval(function(){ autosaveNotepad(); }, AUTOSAVE_SECONDS * 1000);
	}
	// Destroy the event.
	else if (mode === 'stop') {
		clearInterval(autosaveTimer);
	}
}

// Resets the front page to its original setting. Unfortunately, we don't use
// this to actually initialize the front page, so we have the default values in
// a few places. But it keeps execution simple and allows JS to load async
// without holding up the page.
function resetFrontPage() {
	hideStatusBox();
	setBodyToLogin();
	resetNotepad();

	$('#front-page-header').html('Login to My Notepad Info');
	$('#username').focus();      // Focus for immediate typing.
}

function logout() {
	var oldNotepad, newNotepad, parentDiv;

	// Clear the autosave counter first so that it doesn't try to save a split-second too late
	// after other variables have been unset.
	autosaveThread('stop');

	// Part of auto-save is auto-saving on logout. Perform synchronously.
	autosaveNotepad(false);

	globalUsername = '';
	globalPassword = '';

	// Attempt to clear the "undo" history of the notepad textarea for security reasons. Create a
	// new notepad element to replace the old one.
	oldNotepad = document.getElementById('notepad');
	oldNotepad.value = '';
	newNotepad = document.createElement('textarea');
	newNotepad.value = '';
	parentDiv = document.getElementById('notepad-block');
	parentDiv.replaceChild(newNotepad, oldNotepad);
	newNotepad.id = 'notepad';

	// Reset the option/preference controls.
 	resetFrontPage();
}

function onPageLoad() {
	// We use HTML5's autofocus, but isn't widely supported. Fallback to this.
	// Any page with a username has it autofocused.
	$('#username').focus();
}

function onPageUnload() {
	// Part of autosave is saving when the user leaves the page.
	if (globalUsername !== '') {
		// Make the call synchronously.
		autosaveNotepad(false);
	}
}

// This is a very popular XML parse.
function getXmlTagNode(xmlData, nodeName) {
	try {
		return xmlData.getElementsByTagName(nodeName)[0].childNodes[0].nodeValue;
	}
	catch (e) {
		console.error('Failed to extract XML node: ' + nodeName);
	}
}

// This validation check is for the user's sake, not mine. It only needs to be minimally competent.
function validateEmailAddress(email) {
	var validationRe = /\S+@\S+\.\S+/;
	console.debug('Email validate: ' + email);
    return validationRe.test(email);
}


// -------------------------------------------------------------------
// API handling
// -------------------------------------------------------------------

function setApiMutex() {
	apiMutex = true;
}

function clearApiMutex() {
	apiMutex = false;
}

function isApiMutexOn() {
	return apiMutex;
}

function handleApiResponse(xmlHttp, responseCallback) {
	var validResponse, xmlData;

	clearApiMutex();

	// Successful HTTP reply.
	if (xmlHttp.status == 200) {
		// Validate that the reply is valid, ie, has required XML structure.
		// (If it is not, an error may have output incorrectly, borking up the XML.)
		validResponse = true;
		xmlData = xmlHttp.responseXML;

		try {
			xmlData.getElementsByTagName('response_code')[0].childNodes[0].nodeValue;
		}
		catch(e) {
			validResponse = false;
		}

		if (validResponse) {
			responseCallback(xmlData);
		}
		else {
			setStatusText('failure', 'Sorry, we could not complete your request due ' +
						  'to a server error.');
			console.error('Malformed response from server: ' + xmlHttp.responseText);
		}
	}
	else {
		setStatusText('failure', '<b>HTTP error, status =  ' + xmlHttp.status +
					  '</b> - ' + xmlHttp.responseText +  "<br />* Please " +
					  " <a href='about.php'>report</a> this error status to the " +
					  "admin staff.");
	}
}

// Send any arbitrary quries to the server. First parameter is the data to send,
// second is the 'waiting' message to show while the status is not yet complete,
// the third is callback to handle the API response.
function sendApiQuery(data, waitingMessage, responseCallback, async) {
	var xmlHttp, xmlData;

	// Only allow one Api request at a time. If they stockpile, silently ignore
	// the late ones.
	if (isApiMutexOn() === true)
		return false;
	setApiMutex();

	// "async" is an optional argument, provide a default value.
	if (typeof async === "undefined") {
		async = true;
	}

	/* Open the connection. */
	try {
		xmlHttp = new XMLHttpRequest();
	}
	catch(e) {
		try {
			xmlHttp = new ActiveXObject('Msxml2.XMLHTTP');
		}
		catch(e) {
			setStatusText('failure', "Sorry, your browser doesn't support AJAX so this site " +
			              "won't work for you. Try using a browser such as Firefox 4 or IE 7 or " +
			              'later.');
			return false;
		}
	}

	xmlHttp.open('POST', '/api/api.php', async);
	xmlHttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlHttp.setRequestHeader('Content-length', data.length);
	xmlHttp.setRequestHeader('Connection', 'close');
	xmlHttp.send(data);

	if (waitingMessage !== '') {
		setStatusText('none', waitingMessage);
	}

	// Async calls require a callback, sync calls just continue with execution.
	if (async === true) {
		xmlHttp.onreadystatechange = function() {
			// State 4 is when we're done waiting.
			if (xmlHttp.readyState == 4) {
				handleApiResponse(xmlHttp, responseCallback);
			}
		}
	}
	else {
		handleApiResponse(xmlHttp, responseCallback);
	}
}

// Handle the login API request response.
function loginApiResponseHandler(xmlData) {
	var notepadBase64 = '';

	switch (getXmlTagNode(xmlData, 'response_code')) {
		case 'success':

			// The master status box is only for errors at this point.
			hideStatusBox();

			// Accessing an element that doesn't exist or is empty throws an exception.
			try {
				/*
				 * Set the globals.
				 */
				globalUsername = Base64.decode(getXmlTagNode(xmlData, 'username'));
				globalPassword = $('#password').val();

				$('#username').val('');
				$('#password').val('');

				$('#front-page-header').html('Hello, ' + globalUsername + '!');

				/*
				 * Adjust the notepad for the user.
				 */
				adjustNotepadSize('set',
				                  getXmlTagNode(xmlData, 'height'),
				                  getXmlTagNode(xmlData, 'width'));
				setNotepadFontColor(getXmlTagNode(xmlData, 'font_color'));
				setNotepadBackgroundColor(getXmlTagNode(xmlData, 'background_color'));
				$('#autosave').prop('checked', (getXmlTagNode(xmlData, 'autosave') === 'true') ?
				                    true : false);

				// Responses longer than 4096 can get broken up into seperate child nodes.
				var nodeCount = xmlData.getElementsByTagName('notepad_data')[0].childNodes.length;
				for (var idx = 0; idx < nodeCount; idx++) {
					notepadBase64 += xmlData.getElementsByTagName('notepad_data')[0]
					                 .childNodes[idx].nodeValue;
				}
				$('#notepad').val(Base64.decode(notepadBase64));

				// Display the notepad and fit it to the window as best as possible.
				setBodyToNotepad();
				$('#notepad').focus();

				// TODO here: Do we want to set the cursor in the notepad somewhere specific? Top/bottom?

				// Start the autosave check.
				autosaveThread('start');
			}
			catch(e) {
				setStatusText('failure', 'Sorry, an error occured while logging you in.');
			}
		break;

		case 'failure':
			setStatusText(getXmlTagNode(xmlData, 'response_code'), getXmlTagNode(xmlData, 'status'));

			$('#username').val('');
			$('#password').val('');
			$('#username').focus();
		break;
	}
}

// Log a user in and set up their notepad.
function loginApi() {
	var apiQuery;

	apiQuery = "action=login&username=" + Base64.encode($('#username').val()) +
	           "&password=" + Base64.encode($('#password').val());

	sendApiQuery(apiQuery, '', loginApiResponseHandler);
}

function registerUserApiResponseHandler(xmlData) {
	var response;

	// Reset the password fields by default, but not the username/email ones.
	$('#password').val('');
	$('#password2').val('');

	response = getXmlTagNode(xmlData, 'response_code');
	if (response === 'failure') {
		$('#username').focus();
	}

	setStatusText(response, getXmlTagNode(xmlData, 'status'));
}

function registerUserApi() {
	var apiQuery;

	if ($('#username').val().length == 0) {
		setStatusText('failure', 'You must specify a username.');
		return;
	}

	// Double check that their passwords are the same. (Doesn't matter that it's being done
	// client-side since it's for their sake, not mine.)
	if ($('#password').val() !== $('#password2').val()) {
		setStatusText('failure', 'Error: Your passwords do not match.');
		$('#password').val('');
		$('#password2').val('');
		$('#password').focus();
		return;
	}

	// Verify the e-mail looks valid.
	if (!validateEmailAddress($('#email').val())) {
		setStatusText('failure', 'Please specify a valid e-mail address. Remember, the email ' +
		                         'address is optional.');
		return;
	}

	apiQuery = 'action=register' +
	           '&username=' + Base64.encode($('#username').val()) +
	           '&password=' + Base64.encode($('#password').val()) +
	           '&email=' + Base64.encode($('#email').val());
	sendApiQuery(apiQuery, 'Processing registration.', registerUserApiResponseHandler);
}

function resetPasswordApiResponseHandler(xmlData) {
	setStatusText(getXmlTagNode(xmlData, 'response_code'), getXmlTagNode(xmlData, 'status'));
}

function resetPasswordApi() {
	var apiQuery;

	if ($('#username').val().length == 0) {
		setStatusText('failure', 'Please specify a username to reset their password.');
		return;
	}

	apiQuery = 'action=reset_pwd' +
	           '&username=' + Base64.encode($('#username').val());
	sendApiQuery(apiQuery, 'Resetting password.', resetPasswordApiResponseHandler);
}

function changeProfileApiResponseHandler(xmlData) {
	var responseCode = getXmlTagNode(xmlData, 'response_code');

	switch (responseCode) {
		case 'success':
			$('#old_password').val('');
			$('#new_password').val('');
			$('#new_password2').val('');
		break;
		case 'failure':
			$('#old_password').val('');
			$('#username').focus();
		break;
	}

	setStatusText(responseCode,	getXmlTagNode(xmlData, 'status'));
}

function changeProfileApi() {
	var apiQuery, newPassword, newPasswordConfirm;

	if ($('#username').val().length == 0) {
		setStatusText('failure', 'You must specify a username.');
		return;
	}

	newPassword = $('#new_password').val();
	newPasswordConfirm = $('#new_password2').val();
	if (newPassword !== newPasswordConfirm) {
		setStatusText('failure', 'Your passwords do not match.');
		$('#new_password').val('');
		$('#new_password2').val('');
		$('#new_password').focus();
		return;
	}

	console.debug('Email: ' + $('#new_email').val());
	if (!validateEmailAddress($('#new_email').val())) {
		setStatusText('failure', 'Please specify a valid e-mail address. Remember, the email ' +
		                         'address is optional.');
		return;
	}

	apiQuery = "action=change_profile" +
	           "&username=" + Base64.encode($('#username').val()) +
	           "&old_password=" + Base64.encode($('#old_password').val()) +
	           "&new_password=" + Base64.encode($('#new_password').val()) +
	           "&new_email=" + Base64.encode($('#new_email').val());
	sendApiQuery(apiQuery, 'Updating account info.', changeProfileApiResponseHandler);
}

function submitFeedbackApiResponseHandler(xmlData) {
	setStatusText(getXmlTagNode(xmlData, 'response_code'), getXmlTagNode(xmlData, 'status'));
}

function submitFeedbackApi() {
	var apiQuery;

	// Make sure there's at least a message.
	if ($('#message').val().length == 0) {
		setStatusText('failure', 'You need to enter a message. Just tell me what you think.');
		return;
	}

	// Send the data.
	apiQuery = "action=submit_feedback&name=" + Base64.encode($('#name').val()) +
	           "&email=" + Base64.encode($('#email').val()) +
	           "&subject=" + Base64.encode($('#subject').val()) +
	           "&message=" + Base64.encode($('#message').val());
	sendApiQuery(apiQuery, 'Submitting feedback.', submitFeedbackApiResponseHandler);
}

// The saving status has its own spot. Once logged in to notead, the top bar is only for errors.
// Clear any of those errors whenever a successful save is issued.
function saveNotepadApiResponseHandler(xmlData) {
	var strDate, currentDate, hour, minute;

	switch (getXmlTagNode(xmlData, 'response_code')) {
		case 'success':
			/*
			 * Calculate the time, parse the hours to be 1-12.
			 */
			currentDate = new Date();
			hour = currentDate.getHours();
			minute = currentDate.getMinutes();

			if (minute < 10) {
				minute = '0' + minute;
			}

			if (hour > 12) {
			   strDate = (hour - 12) + ':' + minute + ' PM';
			}
			else if (hour === 12) {
			   strDate = '12:' + minute + ' PM';
			}
			else if (hour > 0 && hour < 12) {
				strDate = hour + ":" + minute + ' AM';
			}
			else if (hour === 0) {
				strDate = '12:' + minute + ' AM';
			}

			$('#notepad-save-status').html(getXmlTagNode(xmlData, 'status') + 'at ' + strDate + '.');
			hideStatusBox();
		break;
		case 'failure':
			setStatusText('failure', getXmlTagNode(xmlData, 'status'));
			$('#notepad-save-status').html('Error saving notepad.');
		break;
	}
}

// Save the notepad and associated settings.
// mode = 'save' or 'autosave'
function saveNotepadApi(mode, async) {
	var apiQuery;

	// "async" is an optional argument, provide a default value.
	if (typeof async === "undefined") {
		async = true;
	}

	// We check the length on the server too, but this is for the user's convenience.
	if ($('#notepad').val().length > MAX_NOTEPAD_SIZE) {
		setStatusText('failure', 'Sorry, your notepad is too long (' +
		              ($('#notepad').val().length / 1000) + 'k characters). Please keep it under ' +
		              (MAX_NOTEPAD_SIZE / 1000) + 'k characters.');
		return false;
	}

	// Send the data (trim hash marks off the front of color codes and add them later).
	apiQuery = "action=save" +
	           "&mode=" + mode +
	           "&username=" + Base64.encode(globalUsername) +
	           "&password=" + Base64.encode(globalPassword) +
	           "&notepad_data=" + Base64.encode($('#notepad').val()) +
	           "&height=" + $('#notepad').css('height').replace('px', '') +
	           "&width=" + $('#notepad').css('width').replace('px', '') +
	           "&autosave=" + $('#autosave').prop('checked') +
	           "&font_color=" + $('#font-colors').val().substr(1, 6) +
	           "&background_color=" + $('#background-colors').val().substr(1, 6);
	$('#notepad-save-status').html('Saving notepad...');
	sendApiQuery(apiQuery, '', saveNotepadApiResponseHandler, async);
}

function emailNotepadApiResponseCallback(xmlData) {
	setStatusText(getXmlTagNode(xmlData, 'response_code'), getXmlTagNode(xmlData, 'status'));
}

function emailNotepadApi() {
	var apiQuery, username = '', password = '', usernameElmt;

	if (globalUsername !== '') {
		username = globalUsername;
		password = globalPassword;
	}
	else {
		username = $('#username').val();
	}

	if (username === '') {
		setStatusText('failure', 'Please input your username so we can e-mail your notepad to ' +
		              'the e-mail address on record.');
		return;
	}

	apiQuery = "action=email_notepad" +
				"&username=" + Base64.encode(username) +
				(password ? ("&password=" + Base64.encode(password)) : '');
	sendApiQuery(apiQuery, 'Finding e-mail address.', emailNotepadApiResponseCallback);
}


// ===================================================================
// Auto-executed code
// ===================================================================

$(document).ready(function(){ onPageLoad(); });
$(window).on('beforeunload', function(){ onPageUnload(); });
