<?php
	include('html/header.html');
?>

<a name='form-top'>
<h1 id='front-page-header'>
	Login to My Notepad Info
</h1>
</a>

<div id='login-block'>
	<p>
	Don't resort to using your e-mail as a temporary notepad for URLs or memos. My Notepad Info is a simple, free, web-based notepad designed to get you in and out of your notes as easily as possible.
	</p>

	<p>
	Log in below or quickly <a href='register.php'>register</a> a free account. For returning visitors, yes, there are <a href='updates.php'>some new changes</a>.
	</p>

	<form action='#' onsubmit='loginApi(); return(false);'>
	<!-- OMG, a tablez0r! But wait, it's OK because the data is somewhat tabular; whew! -->
	<table class='login-table'>
		<tr>
			<td height="25"><span class='login-prompt-text'>Username:</span></td>
			<td><input type='text' class='login-field' id='username' tabindex='1' autofocus='autofocus' /></td>
		</tr>
		<tr>
			<td height="25"><span class='login-prompt-text'>Password:</span></td>
			<td><input type='password' class='login-field' id='password' tabindex='2' /></td>
		</tr>
	</table>

	<input class='clicky' type='submit' value='Log In' tabindex='3' />
	<span id='email-notepad-option'><a href='javascript:emailNotepadApi();'>E-Mail your notepad to yourself</a></span>
	</form>

	<noscript><p style='color: red;'>MyNotepad requires JavaScript, which is not available in your browser. Please reload the page with JavaScript support.</p></noscript>
</div>


<div id='notepad-block' style='display: none;'>

	<ul class='notepad-description'>
		<li>Yep, there are <a href='updates.php'>a lot of new updates</a>.</li>
		<!--<li>Remember, you can <a href='details.php'>do everything from the keyboard</a>.</li> -->
		<li><a href='javascript:emailNotepadApi();'>Click here to e-mail your notepad to yourself</a>.</li>
		<li><a href='javascript:logout();' tabindex='6' title='Log out'>Click here to log out</a>.</li>
	</ul>

	<ul class='controls'>
		<li>
			<input type='button' class='clicky' value='Save Notepad' onclick="saveNotepadApi('manual');" tabindex='5' accesskey='s' title='Save your notepad data and settings' />
		</li>
		<li>
			<label for='autosave'>Enable auto-saving:</label><input type="checkbox" class="clicky" id='autosave' value='on'>
		</li>
		<li>
			<span id='notepad-save-status'></span>
		</li>
	</ul>

	<textarea id='notepad' style='width: 750px; height: 200px; font-size: 14px;' tabindex='4' accesskey='n'></textarea>

	<ul class='controls'>
		<li>
			Background:
			<select id='background-colors' class='notepad-button' onchange="setNotepadBackgroundColor(document.getElementById('background-colors').value)">
				<option value='#000000' style='background-color: #000000;	color: #ffffff;'>Black</option>
				<option value='#ffffff' style='background-color: #ffffff;						'>White</option>
				<option value='#f2f2f2' style='background-color: #f2f2f2;' selected='selected'>Light Gray</option>
				<option value='#a9a9a9' style='background-color: #a9a9a9;						'>Gray</option>
				<option value='#808080' style='background-color: #808080;						'>Dark Gray</option>
				<option value='#7fffd4' style='background-color: #7fffd4;						'>Aquamarine</option>
				<option value='#0000ff' style='background-color: #0000ff;						'>Blue</option>
				<option value='#000080' style='background-color: #000080;	color: #ffffff;'>Navy</option>
				<option value='#800080' style='background-color: #800080;	color: #ffffff;'>Purple</option>
				<option value='#ff1493' style='background-color: #ff1493;						'>Deep Pink</option>
				<option value='#ee82ee' style='background-color: #ee82ee;						'>Violet</option>
				<option value='#ffc0cb' style='background-color: #ffc0cb;						'>Pink</option>
				<option value='#006400' style='background-color: #006400;	color: #ffffff;'>Dark Green</option>
				<option value='#008000' style='background-color: #008000;	color: #ffffff;'>Green</option>
				<option value='#9acd32' style='background-color: #9acd32;						'>Yellow Green</option>
				<option value='#ffff00' style='background-color: #ffff00;						'>Yellow</option>
				<option value='#ffa500' style='background-color: #ffa500;						'>Orange</option>
				<option value='#ff0000' style='background-color: #ff0000;						'>Red</option>
				<option value='#a52a2a' style='background-color: #a52a2a;						'>Brown</option>
				<option value='#deb887' style='background-color: #deb887;						'>Burly Wood</option>
				<option value='#f5f5dc' style='background-color: #f5f5dc;						'>Beige</option>
			</select>
		</li>
		<li>
			Font:
			<!-- List taken from http://pietschsoft.com/Blog/Post.aspx?PostID=204 and modified slightly. -->
			<select id='font-colors' class='notepad-button' onchange="setNotepadFontColor(document.getElementById('font-colors').value)">
				<option value='#000000' style='background-color: #000000;	color: #ffffff;' selected='selected'>Black</option>
				<option value='#ffffff' style='background-color: #ffffff;						'>White</option>
				<option value='#f2f2f2' style='background-color: #f2f2f2;						'>Light Gray</option>
				<option value='#a9a9a9' style='background-color: #a9a9a9;						'>Gray</option>
				<option value='#808080' style='background-color: #808080;						'>Dark Gray</option>
				<option value='#7fffd4' style='background-color: #7fffd4;						'>Aquamarine</option>
				<option value='#0000ff' style='background-color: #0000ff;						'>Blue</option>
				<option value='#000080' style='background-color: #000080;	color: #ffffff;'>Navy</option>
				<option value='#800080' style='background-color: #800080;	color: #ffffff;'>Purple</option>
				<option value='#ff1493' style='background-color: #ff1493;						'>Deep Pink</option>
				<option value='#ee82ee' style='background-color: #ee82ee;						'>Violet</option>
				<option value='#ffc0cb' style='background-color: #ffc0cb;						'>Pink</option>
				<option value='#006400' style='background-color: #006400;	color: #ffffff;'>Dark Green</option>
				<option value='#008000' style='background-color: #008000;	color: #ffffff;'>Green</option>
				<option value='#9acd32' style='background-color: #9acd32;						'>Yellow Green</option>
				<option value='#ffff00' style='background-color: #ffff00;						'>Yellow</option>
				<option value='#ffa500' style='background-color: #ffa500;						'>Orange</option>
				<option value='#ff0000' style='background-color: #ff0000;						'>Red</option>
				<option value='#a52a2a' style='background-color: #a52a2a;						'>Brown</option>
				<option value='#deb887' style='background-color: #deb887;						'>Burly Wood</option>
				<option value='#f5f5dc' style='background-color: #f5f5dc;						'>Beige</option>
			</select>
		</li>
		<li>
			Height:
			<input type='button' value='+' class='notepad-button' onclick="adjustNotepadSize('increment', 50, 0)" title='Make notepad wider' />
			<input type='button' value='-' class='notepad-button' onclick="adjustNotepadSize('increment', -50, 0)" title='Make notepad narrower' />
		</li>
		<li>
			Width:
			<input type='button' value='+' class='notepad-button' onclick="adjustNotepadSize('increment', 0, 50)" title='Make notepad taller' />
			<input type='button' value='-' class='notepad-button' onclick="adjustNotepadSize('increment', 0, -50)" title='Make notepad shorter'  />
		</li>

		<li>
			<input type='button' value=' Reset Size ' class='notepad-button' onclick="adjustNotepadSize('set', 0, 0)" title='Make notepad taller' />
		</li>
	</ul>
</div>


<?php
	include('html/footer.html');
?>
