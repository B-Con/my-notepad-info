"use strict";


// ===================================================================
// Global variables
// ===================================================================

var DEFAULT_NOTEPAD_HEIGHT = '200';   // px
var DEFAULT_NOTEPAD_WIDTH = '750';   // px

var AUTOSAVE_SECONDS = 60;
var AUTOSAVE_MAX_SIZE = 32000;   // Don't autosave over 32K.

// How long the status indicator (ie, color of the box) lasts.
var INDICATOR_FEEDBACK_SECONDS = 6;

var PROGRESS_IMG_URL = 'img/waiting.gif';

// Keep database queries below the max. Account for added escape sequences and query overhead.
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
	var topColor = '#f8f8f8';   // Match the background.
	var bottomColor = '#d8d8d8';

	document.getElementById('status-box').style.borderTop = '2px solid ' + topColor;
	document.getElementById('status-box').style.borderBottom = '2px solid ' + bottomColor;
}

// Give a visual feedback on the state of the last status.
// Use recursively to restore the original color.
// statusColor = hex str, resetColor = bool
function setStatusIndicator(statusColor, resetColor) {
	// Explicitly set the border attributes because the border may not exist
	// before the first time the color is explicitly set.
	document.getElementById('status-box').style.borderTop = '2px solid ' + statusColor;
	document.getElementById('status-box').style.borderBottom = '2px solid ' + statusColor;

	clearTimeout(statusTimer);
	if (resetColor == true) {
		statusTimer = setTimeout(function(){ resetStatusIndicator(); },
			INDICATOR_FEEDBACK_SECONDS * 1000);   // Milliseconds
	}
}

// Have less padding when a status is displayed.
function showStatusBox() {
	document.getElementById('status-box').style.display = 'block';
	document.getElementById('page-content').style.paddingTop = '10px';
}

function hideStatusBox() {
	document.getElementById('status-box').style.display = 'none';
	document.getElementById('page-content').style.paddingTop = '25px';
}

// displayStatus = 'none', 'success', 'failure', or 'alert'.
function setStatusText(displayStatus, statusText) {
	if (document.getElementById('status-box').style.display == 'none') {
		showStatusBox();
	}

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

	document.getElementById('status-text').innerHTML = statusText;

	// Optional: Make it disappear after a certain time.
}

// setOn = bool
function setWaitingIndicator(setOn) {
	var progressBarElmt = document.getElementById('progress-indicator');

	if (progressBarElmt) {
		if (setOn === true) {
			progressBarElmt.innerHTML = "<img src='" + PROGRESS_IMG_URL + "' height='16' width='16' alt='Processing request' title='Processing request' />";
		}
		else {
			progressBarElmt.innerHTML = '';
		}
	}
}

// Sizes are integers and in pixels.
function adjustNotepadSize(method, newHeight, newWidth) {
	var notepadElmt, finalHeight, finalWidth;

	// Sanity checks and special case handling.
	if (newHeight > 3000) {
		newHeight = 3000;
	}
	if (newWidth > 3000) {
		newWidth = 3000;
	}

	// Adjust the size of the notepad. Use the 'method' to explicitly set the absolute new size
	// or to merely increment the current size by the specified amount.
	notepadElmt = document.getElementById('notepad');
	switch (method) {
		case 'set':
			//console.debug('h = ' + newHeight);
			//console.debug('w = ' + newWidth);

			// Absolute value of 0 means to reset to default.
			if (newHeight === 0) {
				finalHeight = DEFAULT_NOTEPAD_HEIGHT;
			}
			else {
				finalHeight = newHeight;
			}
			notepadElmt.style.height = finalHeight + 'px';

			if (newWidth === 0) {
				finalWidth = DEFAULT_NOTEPAD_WIDTH;
			}
			else {
				finalWidth = newWidth;
			}
			notepadElmt.style.width = finalWidth + 'px';

		break;
		case 'increment':
			if (newHeight !== 0) {
				newHeight += parseInt((notepadElmt.style.height).replace('px', ''), 10);
				notepadElmt.style.height = newHeight + 'px';
			}
			if (newWidth !== 0) {
				newWidth += parseInt((notepadElmt.style.width).replace('px', ''), 10);
				notepadElmt.style.width = newWidth + 'px';
			}
		break;
	}
}

// Input in hex.
function setNotepadFontColor(color) {
	document.getElementById('notepad').style.color = color;
	document.getElementById('font-colors').value = color;
}

// Input in hex.
function setNotepadBackgroundColor(color) {
	document.getElementById('notepad').style.backgroundColor = color;
	document.getElementById('background-colors').value = color;
}

// A pair of functions to set which display block is shown on the home page.
// Adjust the element that is not shown first to avoid any brief flickers of multiple boxes.
function setBodyToLogin() {
	document.getElementById('notepad-block').style.display = 'none';
	document.getElementById('login-block').style.display = 'block';
}

function setBodyToNotepad() {
	document.getElementById('login-block').style.display = 'none';
	document.getElementById('notepad-block').style.display = 'block';
}

function resetNotepad() {
	adjustNotepadSize('set', DEFAULT_NOTEPAD_HEIGHT, DEFAULT_NOTEPAD_WIDTH);
	/*set_notepad_font_color();
	set_notepad_background_color();*/
	document.getElementById('autosave').checked = false;
}

function autosaveNotepad(async) {
	// Only issue the save if auto-save is selected.
	// This implicitly means that the only notepad modification that is not auto-saved
	// is the act of turning auto-save off. This is a good thing, because it
	// allows for it to be turned off temporarily. We turn it off automatically for
	// large notepads, but want it to be temporary by default when we do so.
	if (document.getElementById('autosave').checked === true) {
		// Don't auto-save gigantic notepad contents.
		if (document.getElementById('notepad').value.length > AUTOSAVE_MAX_SIZE) {
			document.getElementById('autosave').checked = false;
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
	setBodyToLogin();
	resetNotepad();
	hideStatusBox();

	document.getElementById('front-page-header').innerHTML = 'Login to My Notepad Info';
	document.getElementById('username').focus();   // Focus for immediate typing.
	window.location.hash = '';   // Reset the window to the top of the page.
}

function logout() {
	var oldNotepad, newNotepad, parentDiv;

	// Clear the autosave counter first so that it doesn't try to save a split-second too late
	// after other variables have been unset.
	autosaveThread('stop');

	// Part of auto-save is auto-saving on logout.
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
}

function onPageUnload() {
	// Part of autosave is saving when the user leaves the page.
	if (globalUsername !== '') {
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

	setWaitingIndicator(false);
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
			              'greater.');
			return false;
		}
	}

	//console.debug('About to send API query: ' + data);

	xmlHttp.open('POST', '/api/api.php', async);
	xmlHttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlHttp.setRequestHeader('Content-length', data.length);
	xmlHttp.setRequestHeader('Connection', 'close');
	xmlHttp.send(data);

	setWaitingIndicator(true);
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
			// Accessing an element that doesn't exist or is empty throws an exception.
			try {
				/*
				 * Set the globals.
				 */
				globalUsername = Base64.decode(getXmlTagNode(xmlData, 'username'));
				globalPassword = document.getElementById('password').value;

				document.getElementById('username').value = '';
				document.getElementById('password').value = '';

				document.getElementById('front-page-header').innerHTML =
				                                                   'Hello, ' + globalUsername + '!';

				/*
				 * Adjust the notepad for the user.
				 */
				adjustNotepadSize('set',
				                  getXmlTagNode(xmlData, 'height'),
				                  getXmlTagNode(xmlData, 'width'));
				setNotepadFontColor(getXmlTagNode(xmlData, 'font_color'));
				setNotepadBackgroundColor(getXmlTagNode(xmlData, 'background_color'));
				if (getXmlTagNode(xmlData, 'autosave') === 'true') {
					document.getElementById('autosave').checked = true;
				}
				else {
					document.getElementById('autosave').checked = false;
				}

				// Responses longer than 4096 can get broken up into seperate child nodes.
				var nodeCount = xmlData.getElementsByTagName('notepad_data')[0].childNodes.length;
				for (var idx = 0; idx < nodeCount; idx++) {
					notepadBase64 += xmlData.getElementsByTagName('notepad_data')[0]
					                 .childNodes[idx].nodeValue;
				}
				document.getElementById('notepad').value = Base64.decode(notepadBase64);

				// Display the notepad and fit it to the window as best as possible.
				setBodyToNotepad();
				window.location.hash = 'form-top';
				document.getElementById('notepad').focus();

				// TODO: Do we want to set the cursor in the notepad somewhere specific? Top/bottom?

				// Start the autosave counter.
				autosaveThread('start');
			}
			catch(e) {
				setStatusText('failure', 'Sorry, a server error occured while logging you in.');
			}
		break;

		case 'failure':
			document.getElementById('username').value = '';
			document.getElementById('password').value = '';
			document.getElementById('username').focus();
		break;
	}

	setStatusText(getXmlTagNode(xmlData, 'response_code'), getXmlTagNode(xmlData, 'status'));
}

// Log a user in and set up their notepad.
function loginApi() {
	var apiQuery;

	apiQuery = "action=login&username=" + Base64.encode(document.getElementById('username').value) +
	           "&password=" + Base64.encode(document.getElementById('password').value);

	sendApiQuery(apiQuery, 'Logging in.', loginApiResponseHandler);
}

function registerUserApiResponseHandler(xmlData) {
	var response;

	// Reset the password fields by default, but not the username/email ones.
	document.getElementById('password').value = '';
	document.getElementById('password2').value = '';

	response = getXmlTagNode(xmlData, 'response_code');
	if (response === 'failure') {
		document.getElementById('username').focus();
	}

	setStatusText(response, getXmlTagNode(xmlData, 'status'));
}

function registerUserApi() {
	var apiQuery;

	if (document.getElementById('username').value === '') {
		setStatusText('failure', 'You must specify a username.');
		return;
	}

	// Double check that their passwords are the same. (Doesn't matter that it's being done
	// client-side since it's for their sake, not mine.)
	if (document.getElementById('password').value !== document.getElementById('password2').value) {
		setStatusText('failure', 'Error: Your passwords do not match.');
		document.getElementById('password').value = '';
		document.getElementById('password2').value = '';
		document.getElementById('password').focus();
		return;
	}

	apiQuery = 'action=register' +
	           '&username=' + Base64.encode(document.getElementById('username').value) +
	           '&password=' + Base64.encode(document.getElementById('password').value) +
	           '&email=' + Base64.encode(document.getElementById('email').value);
	sendApiQuery(apiQuery, 'Processing registration.', registerUserApiResponseHandler);
}

function resetPasswordApiResponseHandler(xmlData) {
	setStatusText(getXmlTagNode(xmlData, 'response_code'), getXmlTagNode(xmlData, 'status'));
}

function resetPasswordApi() {
	var apiQuery;

	if (document.getElementById('username').value === '') {
		setStatusText('failure', 'Please specify a username to reset their password.');
		return;
	}

	apiQuery = 'action=reset_pwd' +
	           '&username=' + Base64.encode(document.getElementById('username').value);
	sendApiQuery(apiQuery, 'Resetting password.', resetPasswordApiResponseHandler);
}

function changeProfileApiResponseHandler(xmlData) {
	var responseCode = getXmlTagNode(xmlData, 'response_code');

	switch (responseCode) {
		case 'success':
			document.getElementById('old_password').value = '';
			document.getElementById('new_password').value = '';
			document.getElementById('new_password2').value = '';
		break;
		case 'failure':
			document.getElementById('old_password').value = '';
			document.getElementById('username').focus();
		break;
	}

	setStatusText(responseCode,	getXmlTagNode(xmlData, 'status'));
}

function changeProfileApi() {
	var apiQuery, newPassword, newPasswordConfirm;

	if (document.getElementById('username').value === '') {
		setStatusText('failure', 'You must specify a username.');
		return;
	}

	newPassword = document.getElementById('new_password').value;
	newPasswordConfirm = document.getElementById('new_password2').value;
	if (newPassword !== newPasswordConfirm) {
		setStatusText('failure', 'Your passwords do not match.');
		document.getElementById('new_password').value = '';
		document.getElementById('new_password2').value = '';
		document.getElementById('new_password').focus();
		return;
	}

	apiQuery = "action=change_profile" +
	           "&username=" + Base64.encode(document.getElementById('username').value) +
	           "&old_password=" + Base64.encode(document.getElementById('old_password').value) +
	           "&new_password=" + Base64.encode(document.getElementById('new_password').value) +
	           "&new_email=" + Base64.encode(document.getElementById('new_email').value);
	sendApiQuery(apiQuery, "Changing password.", changeProfileApiResponseHandler);
}

function submitFeedbackApiResponseHandler(xmlData) {
	setStatusText(getXmlTagNode(xmlData, 'response_code'), getXmlTagNode(xmlData, 'status'));
}

function submitFeedbackApi() {
	var apiQuery;

	// Make sure there's at least a message.
	if (document.getElementById('message').value === '') {
		setStatusText('failure', 'You need to enter a message. Just tell me what you think.');
		return;
	}

	// Send the data.
	apiQuery = "action=submit_feedback&name=" + Base64.encode(document.getElementById('name').value) +
	           "&email=" + Base64.encode(document.getElementById('email').value) +
	           "&subject=" + Base64.encode(document.getElementById('subject').value) +
	           "&message=" + Base64.encode(document.getElementById('message').value);
	sendApiQuery(apiQuery, 'Submitting feedback.', submitFeedbackApiResponseHandler);
}

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
			   strDate = (hour - 12) + ':' + minute + ' PM.';
			}
			else if (hour === 12) {
			   strDate = '12:' + minute + ' PM.';
			}
			else if (hour > 0 && hour < 12) {
				strDate = hour + ":" + minute + ' AM.';
			}
			else if (hour === 0) {
				strDate = '12:' + minute + ' AM.';
			}

			setStatusText('none', getXmlTagNode(xmlData, 'status') +
				          "<span style='white-space: nowrap;'>at " + strDate + "</span>");
		break;
		case 'failure':
			setStatusText('failure', getXmlTagNode(xmlData, 'status'));
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

	if (document.getElementById('notepad').value.length > MAX_NOTEPAD_SIZE) {
		setStatusText('failure', 'Sorry, your notepad is too long. Please keep it under ' +
		              (MAX_NOTEPAD_SIZE / 1000) + 'k.');
		return false;
	}

	// Send the data (trim hash marks off the front of color codes and add them later).
	apiQuery = "action=save" +
	           "&mode=" + mode +
	           "&username=" + Base64.encode(globalUsername) +
	           "&password=" + Base64.encode(globalPassword) +
	           "&notepad_data=" + Base64.encode(document.getElementById('notepad').value) +
	           "&height=" + document.getElementById('notepad').style.height.replace('px', '') +
	           "&width=" + document.getElementById('notepad').style.width.replace('px', '') +
	           "&autosave=" + document.getElementById('autosave').checked +
	           "&font_color=" + document.getElementById('font-colors').value.substr(1, 6) +
	           "&background_color=" + document.getElementById('background-colors').value.substr(1, 6);
	sendApiQuery(apiQuery, 'Saving notepad data.', saveNotepadApiResponseHandler, async);
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
		usernameElmt = document.getElementById('username');
		if (usernameElmt)
			username = document.getElementById('username').value;
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

// Pre-load the "loading" image.
progressBar = new Image(16, 16);
progressBar.src = PROGRESS_IMG_URL;

/*
 * data:image/gif,GIF89a%10%00%10%00%F2%00%00%FF%FF%FF%00%00%00%C2%C2%C2BBB%00%00%00bbb%82%82%82%92%92%92!%FF%0BNETSCAPE2.0%03%01%00%00%00!%FE%1DBuilt%20with%20GIF%20Movie%20Gear%204.0%00!%FE%15Made%20by%20AjaxLoad.info%00!%F9%04%09%0A%00%00%00%2C%00%00%00%00%10%00%10%00%00%033%08%BA%DC%FE0%CAIk%13c%08%3A%08%19%9C%07N%98f%09E%B11%C2%BA%14%99%C1%B6.%60%C4%C2q%D0-%5B%189%DD%A6%079%18%0C%07Jk%E7H%00%00!%F9%04%09%0A%00%00%00%2C%00%00%00%00%10%00%10%00%00%034%08%BA%DC%FEN%8C!%20%1B%84%0C%BB%B0%E6%8ADqBQT%601%19%20%60LE%5B%1A%A8%7C%1C%B5u%DF%EDa%18%07%80%20%D7%18%E2%86C%19%B2%25%24*%12%00!%F9%04%09%0A%00%00%00%2C%00%00%00%00%10%00%10%00%00%036%08%BA2%23%2B%CAA%C8%90%CC%94V%2F%06%85c%1C%0E%F4%19N%F1IBa%98%ABp%1C%F0%0A%CC%B3%BD%1C%C6%A8%2B%02Y%ED%17%FC%01%83%C3%0F2%A9d%1A%9F%BF%04%00!%F9%04%09%0A%00%00%00%2C%00%00%00%00%10%00%10%00%00%033%08%BAb%25%2B%CA2%86%91%EC%9CV_%85%8B%A6%09%85!%0C%041D%87a%1C%11%AAF%82%B0%D1%1F%03bR%5D%F3%3D%1F08%2C%1A%8F%C8%A4r9L%00%00!%F9%04%09%0A%00%00%00%2C%00%00%00%00%10%00%10%00%00%032%08%BAr'%2BJ%E7d%14%F0%18%F3L%81%0C%26v%C3%60%5CbT%94%85%84%B9%1EhYB)%CF%CA%40%10%03%1E%E9%3C%1F%C3%26%2C%1A%8F%C8%A4R%92%00%00!%F9%04%09%0A%00%00%00%2C%00%00%00%00%10%00%10%00%00%033%08%BA%20%C2%909%17%E3t%E7%BC%DA%9E0%19%C7%1C%E0!.B%B6%9D%CAW%AC%A21%0C%06%0B%14sa%BB%B05%F7%95%01%810%B0%09%89%BB%9Fm)J%00%00!%F9%04%09%0A%00%00%00%2C%00%00%00%00%10%00%10%00%00%032%08%BA%DC%FE%F0%09%11%D9%9CU%5D%9A%01%EE%DAqp%95%60%88%DDa%9C%DD4%96%85AF%C50%14%90%60%9B%B6%01%0D%04%C2%40%10%9B1%80%C2%D6%CE%91%00%00!%F9%04%09%0A%00%00%00%2C%00%00%00%00%10%00%10%00%00%032%08%BA%DC%FE0%CAI%ABeB%D4%9C)%D7%1E%08%08%C3%20%8E%C7q%0E%0410%A9%CA%B0%AEP%18%C2a%18%07V%DA%A5%02%20ub%18%82%9E%5B%11%90%00%00%3B%00%00%00%00%00%00%00%00%00
 * */

window.onbeforeunload = function(){ onPageUnload(); };
