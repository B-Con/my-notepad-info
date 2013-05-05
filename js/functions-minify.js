var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789*-=",encode:function(c){var a="";var k,h,f,j,g,e,d;var b=0;c=Base64._utf8_encode(c);while(b<c.length){k=c.charCodeAt(b++);h=c.charCodeAt(b++);f=c.charCodeAt(b++);j=k>>2;g=((k&3)<<4)|(h>>4);e=((h&15)<<2)|(f>>6);d=f&63;if(isNaN(h)){e=d=64}else{if(isNaN(f)){d=64}}a=a+this._keyStr.charAt(j)+this._keyStr.charAt(g)+this._keyStr.charAt(e)+this._keyStr.charAt(d)}return a},decode:function(c){var a="";var k,h,f;var j,g,e,d;var b=0;c=c.replace(/[^A-Za-z0-9\*\-\=]/g,"");while(b<c.length){j=this._keyStr.indexOf(c.charAt(b++));g=this._keyStr.indexOf(c.charAt(b++));e=this._keyStr.indexOf(c.charAt(b++));d=this._keyStr.indexOf(c.charAt(b++));k=(j<<2)|(g>>4);h=((g&15)<<4)|(e>>2);f=((e&3)<<6)|d;a=a+String.fromCharCode(k);if(e!=64){a=a+String.fromCharCode(h)}if(d!=64){a=a+String.fromCharCode(f)}}a=Base64._utf8_decode(a);return a},_utf8_encode:function(b){b=b.replace(/\r\n/g,"\n");var a="";for(var e=0;e<b.length;e++){var d=b.charCodeAt(e);if(d<128){a+=String.fromCharCode(d)}else{if((d>127)&&(d<2048)){a+=String.fromCharCode((d>>6)|192);a+=String.fromCharCode((d&63)|128)}else{a+=String.fromCharCode((d>>12)|224);a+=String.fromCharCode(((d>>6)&63)|128);a+=String.fromCharCode((d&63)|128)}}}return a},_utf8_decode:function(a){var b="";var d=0;var e=c1=c2=0;while(d<a.length){e=a.charCodeAt(d);if(e<128){b+=String.fromCharCode(e);d++}else{if((e>191)&&(e<224)){c2=a.charCodeAt(d+1);b+=String.fromCharCode(((e&31)<<6)|(c2&63));d+=2}else{c2=a.charCodeAt(d+1);c3=a.charCodeAt(d+2);b+=String.fromCharCode(((e&15)<<12)|((c2&63)<<6)|(c3&63));d+=3}}}return b}};"use strict";var DEFAULT_NOTEPAD_HEIGHT="200";var DEFAULT_NOTEPAD_WIDTH="750";var AUTOSAVE_SECONDS=60;var AUTOSAVE_MAX_SIZE=32000;var INDICATOR_FEEDBACK_SECONDS=5;var MAX_NOTEPAD_SIZE=900000;var globalUsername="";var globalPassword="";var autosaveTimer;var statusTimer;var progressBar;var apiMutex=false;function resetStatusIndicator(){$("#status-box").css("border-color","");$("#status-box").removeClass("status-box-update").addClass("status-box-normal")}function setStatusIndicator(b,a){$("#status-box").removeClass("status-box-normal").addClass("status-box-update");$("#status-box").css("border-color",b);clearTimeout(statusTimer);if(a==true){statusTimer=setTimeout(function(){resetStatusIndicator()},INDICATOR_FEEDBACK_SECONDS*1000)}}function showStatusBox(){$("#status-box").slideDown("fast","linear");$("#page-content").css("padding-top","10px").animate("fast","linear")}function hideStatusBox(){$("#status-box").slideUp("fast","linear");$("#page-content").css("padding-top","25px")}function setStatusText(a,b){showStatusBox();switch(a){case"none":break;case"success":setStatusIndicator("#22bb22",true);break;case"failure":setStatusIndicator("#bb2222",true);break;case"alert":setStatusIndicator("#aa4000",true);break}$("#status-text").html(b)}function adjustNotepadSize(f,b,d){var c,a=0,e=0;if(b>3000){b=3000}if(d>3000){d=3000}switch(f){case"set":if(b===0){a=DEFAULT_NOTEPAD_HEIGHT}else{a=b}if(d===0){e=DEFAULT_NOTEPAD_WIDTH}else{e=d}$("#notepad").outerHeight(a.toString()+"px");$("#notepad").outerWidth(e.toString()+"px");break;case"increment":if(b!==0){a=b+parseInt($("#notepad").outerHeight());$("#notepad").outerHeight(a.toString()+"px")}if(d!==0){e=d+parseInt($("#notepad").outerWidth());$("#notepad").outerWidth(e.toString()+"px")}break}}function setNotepadFontColor(a){$("#notepad").css("color",a);$("#font-colors").val(a)}function setNotepadBackgroundColor(a){$("#notepad").css("background-color",a);$("#background-colors").val(a)}function setBodyToLogin(){window.location.hash="";$("#notepad-block").slideUp("fast","linear");$("#login-block").show()}function setBodyToNotepad(){$("#login-block").hide();$("#notepad-block").slideDown("fast","linear",function(){window.location.hash="form-top"})}function resetNotepad(){adjustNotepadSize("set",DEFAULT_NOTEPAD_HEIGHT,DEFAULT_NOTEPAD_WIDTH);$("#autosave").prop("checked",false)}function autosaveNotepad(a){if($("#autosave").prop("checked")===true){if($("#notepad").val().length>AUTOSAVE_MAX_SIZE){$("#autosave").prop("checked",false);setStatusText("alert","Looks like you're making good use of your notepad. :-) You're welcome to store lots of text, but auto-save is disabled for large notepads. You can continue to save manually and re-enable auto-save once the notepad is smaller.")}else{saveNotepadApi("auto",a)}}}function autosaveThread(a){if(a==="start"){clearInterval(autosaveTimer);autosaveTimer=setInterval(function(){autosaveNotepad()},AUTOSAVE_SECONDS*1000)}else{if(a==="stop"){clearInterval(autosaveTimer)}}}function resetFrontPage(){hideStatusBox();setBodyToLogin();resetNotepad();$("#front-page-header").html("Login to My Notepad Info");$("#username").focus()}function logout(){var b,a,c;autosaveThread("stop");autosaveNotepad(false);globalUsername="";globalPassword="";b=document.getElementById("notepad");b.value="";a=document.createElement("textarea");a.value="";c=document.getElementById("notepad-block");c.replaceChild(a,b);a.id="notepad";resetFrontPage()}function onPageLoad(){$("#username").focus()}function onPageUnload(){if(globalUsername!==""){autosaveNotepad(false)}}function getXmlTagNode(a,c){try{return a.getElementsByTagName(c)[0].childNodes[0].nodeValue}catch(b){console.error("Failed to extract XML node: "+c)}}function validateEmailAddress(a){var b=/\S+@\S+\.\S+/;console.debug("Email validate: "+a);return b.test(a)}function setApiMutex(){apiMutex=true}function clearApiMutex(){apiMutex=false}function isApiMutexOn(){return apiMutex}function handleApiResponse(b,d){var a,c;clearApiMutex();if(b.status==200){a=true;c=b.responseXML;try{c.getElementsByTagName("response_code")[0].childNodes[0].nodeValue}catch(f){a=false}if(a){d(c)}else{setStatusText("failure","Sorry, we could not complete your request due to a server error.");console.error("Malformed response from server: "+b.responseText)}}else{setStatusText("failure","<b>HTTP error, status =  "+b.status+"</b> - "+b.responseText+"<br />* Please  <a href='about.php'>report</a> this error status to the admin staff.")}}function sendApiQuery(g,b,f,d){var a,c;if(isApiMutexOn()===true){return false}setApiMutex();if(typeof d==="undefined"){d=true}try{a=new XMLHttpRequest()}catch(h){try{a=new ActiveXObject("Msxml2.XMLHTTP")}catch(h){setStatusText("failure","Sorry, your browser doesn't support AJAX so this site won't work for you. Try using a browser such as Firefox 4 or IE 7 or later.");return false}}a.open("POST","/api/api.php",d);a.setRequestHeader("Content-type","application/x-www-form-urlencoded");a.setRequestHeader("Content-length",g.length);a.setRequestHeader("Connection","close");a.send(g);if(b!==""){setStatusText("none",b)}if(d===true){a.onreadystatechange=function(){if(a.readyState==4){handleApiResponse(a,f)}}}else{handleApiResponse(a,f)}}function loginApiResponseHandler(c){var f="";switch(getXmlTagNode(c,"response_code")){case"success":hideStatusBox();try{globalUsername=Base64.decode(getXmlTagNode(c,"username"));globalPassword=$("#password").val();$("#username").val("");$("#password").val("");$("#front-page-header").html("Hello, "+globalUsername+"!");adjustNotepadSize("set",getXmlTagNode(c,"height"),getXmlTagNode(c,"width"));setNotepadFontColor(getXmlTagNode(c,"font_color"));setNotepadBackgroundColor(getXmlTagNode(c,"background_color"));$("#autosave").prop("checked",(getXmlTagNode(c,"autosave")==="true")?true:false);var b=c.getElementsByTagName("notepad_data")[0].childNodes.length;for(var a=0;a<b;a++){f+=c.getElementsByTagName("notepad_data")[0].childNodes[a].nodeValue}$("#notepad").val(Base64.decode(f));setBodyToNotepad();$("#notepad").focus();autosaveThread("start")}catch(d){setStatusText("failure","Sorry, an error occured while logging you in.")}break;case"failure":setStatusText(getXmlTagNode(c,"response_code"),getXmlTagNode(c,"status"));$("#username").val("");$("#password").val("");$("#username").focus();break}}function loginApi(){var a;a="action=login&username="+Base64.encode($("#username").val())+"&password="+Base64.encode($("#password").val());sendApiQuery(a,"",loginApiResponseHandler)}function registerUserApiResponseHandler(b){var a;$("#password").val("");$("#password2").val("");a=getXmlTagNode(b,"response_code");if(a==="failure"){$("#username").focus()}setStatusText(a,getXmlTagNode(b,"status"))}function registerUserApi(){var a;if($("#username").val().length==0){setStatusText("failure","You must specify a username.");return}if($("#password").val()!==$("#password2").val()){setStatusText("failure","Error: Your passwords do not match.");$("#password").val("");$("#password2").val("");$("#password").focus();return}if(!validateEmailAddress($("#email").val())){setStatusText("failure","Please specify a valid e-mail address. Remember, the email address is optional.");return}a="action=register&username="+Base64.encode($("#username").val())+"&password="+Base64.encode($("#password").val())+"&email="+Base64.encode($("#email").val());sendApiQuery(a,"Processing registration.",registerUserApiResponseHandler)}function resetPasswordApiResponseHandler(a){setStatusText(getXmlTagNode(a,"response_code"),getXmlTagNode(a,"status"))}function resetPasswordApi(){var a;if($("#username").val().length==0){setStatusText("failure","Please specify a username to reset their password.");return}a="action=reset_pwd&username="+Base64.encode($("#username").val());sendApiQuery(a,"Resetting password.",resetPasswordApiResponseHandler)}function changeProfileApiResponseHandler(b){var a=getXmlTagNode(b,"response_code");switch(a){case"success":$("#old_password").val("");$("#new_password").val("");$("#new_password2").val("");break;case"failure":$("#old_password").val("");$("#username").focus();break}setStatusText(a,getXmlTagNode(b,"status"))}function changeProfileApi(){var a,c,b;if($("#username").val().length==0){setStatusText("failure","You must specify a username.");return}c=$("#new_password").val();b=$("#new_password2").val();if(c!==b){setStatusText("failure","Your passwords do not match.");$("#new_password").val("");$("#new_password2").val("");$("#new_password").focus();return}console.debug("Email: "+$("#new_email").val());if(!validateEmailAddress($("#new_email").val())){setStatusText("failure","Please specify a valid e-mail address. Remember, the email address is optional.");return}a="action=change_profile&username="+Base64.encode($("#username").val())+"&old_password="+Base64.encode($("#old_password").val())+"&new_password="+Base64.encode($("#new_password").val())+"&new_email="+Base64.encode($("#new_email").val());sendApiQuery(a,"Updating account info.",changeProfileApiResponseHandler)}function submitFeedbackApiResponseHandler(a){setStatusText(getXmlTagNode(a,"response_code"),getXmlTagNode(a,"status"))}function submitFeedbackApi(){var a;if($("#message").val().length==0){setStatusText("failure","You need to enter a message. Just tell me what you think.");return}a="action=submit_feedback&name="+Base64.encode($("#name").val())+"&email="+Base64.encode($("#email").val())+"&subject="+Base64.encode($("#subject").val())+"&message="+Base64.encode($("#message").val());sendApiQuery(a,"Submitting feedback.",submitFeedbackApiResponseHandler)}function saveNotepadApiResponseHandler(d){var c,b,a,e;switch(getXmlTagNode(d,"response_code")){case"success":b=new Date();a=b.getHours();e=b.getMinutes();if(e<10){e="0"+e}if(a>12){c=(a-12)+":"+e+" PM"}else{if(a===12){c="12:"+e+" PM"}else{if(a>0&&a<12){c=a+":"+e+" AM"}else{if(a===0){c="12:"+e+" AM"}}}}$("#notepad-save-status").html(getXmlTagNode(d,"status")+"at "+c+".");hideStatusBox();break;case"failure":setStatusText("failure",getXmlTagNode(d,"status"));$("#notepad-save-status").html("Error saving notepad.");break}}function saveNotepadApi(c,b){var a;if(typeof b==="undefined"){b=true}if($("#notepad").val().length>MAX_NOTEPAD_SIZE){setStatusText("failure","Sorry, your notepad is too long ("+($("#notepad").val().length/1000)+"k characters). Please keep it under "+(MAX_NOTEPAD_SIZE/1000)+"k characters.");return false}a="action=save&mode="+c+"&username="+Base64.encode(globalUsername)+"&password="+Base64.encode(globalPassword)+"&notepad_data="+Base64.encode($("#notepad").val())+"&height="+$("#notepad").css("height").replace("px","")+"&width="+$("#notepad").css("width").replace("px","")+"&autosave="+$("#autosave").prop("checked")+"&font_color="+$("#font-colors").val().substr(1,6)+"&background_color="+$("#background-colors").val().substr(1,6);$("#notepad-save-status").html("Saving notepad...");sendApiQuery(a,"",saveNotepadApiResponseHandler,b)}function emailNotepadApiResponseCallback(a){setStatusText(getXmlTagNode(a,"response_code"),getXmlTagNode(a,"status"))}function emailNotepadApi(){var b,d="",a="",c;if(globalUsername!==""){d=globalUsername;a=globalPassword}else{d=$("#username").val()}if(d===""){setStatusText("failure","Please input your username so we can e-mail your notepad to the e-mail address on record.");return}b="action=email_notepad&username="+Base64.encode(d)+(a?("&password="+Base64.encode(a)):"");sendApiQuery(b,"Finding e-mail address.",emailNotepadApiResponseCallback)}$(document).ready(function(){onPageLoad()});$(window).on("beforeunload",function(){onPageUnload()});