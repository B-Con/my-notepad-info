<?php
include('html/header.html');
?>

<h1>Sign Up For Your Own Notepad</h1>

<p>
Ever needed to move simple notes, like URLs, from a computer that isn't yours to a computer that is? E-mailing them to yourself gets old. My Notepad Info aims to alleviate that. It's easy: just provide a username, password, and and optional e-mail address. In a few seconds you could have a fast, easy-to-use notepad and no longer fill your e-mail inbox with URLs to bookmark.
</p>

<form action='#' onsubmit='registerUserApi(); return(false);'>
<table>
	<tr>
		<td><span class='login-prompt-text'>Username:</span></td>
		<td><input type='text' class='login-field' id='username' tabindex='1' autofocus='autofocus' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>Password <small>(case sensitive)</small>:</span></td>
		<td><input type='password' class='login-field' id='password' tabindex='2' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>Password <small>(confirm)</small>:</span></td>
		<td><input type='password' class='login-field' id='password2' tabindex='3' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>E-Mail <small>(optional)*</small>:</span></td>
		<td><input type='email' class='login-field' id='email' tabindex='4' /></td>
	</tr>
</table>

<p>
By submitting your registration information you are agreeing with the <a href='conditions.php'>privacy/liability</a> conditions.
<p>

<p>
<input class='clicky' type='submit' value='Register' tabindex='5' />
</p>
</form>

<p>
* <span class='registration-notes'>Your e-mail address is safe, we do not send any unrequested e-mails nor share it with any third parties. You do not have to provide an address, however some features require one: You must have an e-mail in order to reset your password or to get your notepad contents e-mailed to you. You can add or remove an e-mail address for your account at any time after registration.</span>
</p>

<?php
	include('html/footer.html');
?>
