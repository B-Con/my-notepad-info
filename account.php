<?php
	include('html/header.html');
?>

<h1>Modify Your Account</h1>

<p>
You can change your password or e-mail address here. You can also reset your password, provided you have an e-mail address already associated with your account. To reset your password you only need to specify your username. To update your account, specify your username, password, and the additional fields you want to modify.
</p>

<table>
	<tr>
		<td><span class='login-prompt-text'>Username:</span></td>
		<td><input type='text' class='login-field' id='username' tabindex='1' autofocus='autofocus' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>Current Password:</span></td>
		<td><input type='password' class='login-field' id='old_password' tabindex='2' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>New Password:</span></td>
		<td><input type='password' class='login-field' id='new_password' tabindex='3' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>Confirm New Password:</span></td>
		<td><input type='password' class='login-field' id='new_password2' tabindex='4' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>New E-Mail*:</span></td>
		<td><input type='email' class='login-field' id='new_email' tabindex='5' /></td>
	</tr>
</table>

<p>
<input class='clicky' type='submit' value='Update Account' onclick='javascript:changeProfileApi()' tabindex='6' style='margin-right: 10px;' />
or
<input class='clicky' type='submit' value='Reset Password' onclick='javascript:resetPasswordApi()' tabindex='7' style='margin-left: 10px;' />
<span id='progress-bar'></span>
</p>

<p>
* <span class='registration-notes'>Your e-mail address is <strong>optional</strong> and, if you provide it, safe with us. We do not send any unrequested e-mails nor share it with any third parties. You do not have to provide an e-mail, however some features require one: You must have an e-mail in order to reset your password or to get your notepad contents e-mailed to you. You can add or remove an e-mail address for your account at any time after registration.</span>
</p>

<div id='status-text' style='border: none;'></div> <!-- Make it invisible until explicitly set. -->

<?php
	include('html/footer.html');
?>
