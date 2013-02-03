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
		<td><span class='login-prompt-text'>Current Password <small>(case sensitive)</small>:</span></td>
		<td><input type='password' class='login-field' id='old_password' tabindex='2' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>New Password <small>(case sensitive)</small>:</span></td>
		<td><input type='password' class='login-field' id='new_password' tabindex='3' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>New Password <small>(confirm)</small>:</span></td>
		<td><input type='password' class='login-field' id='new_password2' tabindex='4' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>New E-Mail:</span></td>
		<td><input type='email' class='login-field' id='new_email' tabindex='5' /></td>
	</tr>
</table>

<p>
<input class='clicky' type='submit' value='Update Account' onclick='javascript:changeProfileApi()' tabindex='6' style='margin-right: 10px;' />
or
<input class='clicky' type='submit' value='Reset Password' onclick='javascript:resetPasswordApi()' tabindex='7' style='margin-left: 10px;' />
<span id='progress-bar'></span>
</p>

<div id='status-text' style='border: none;'></div> <!-- Make it invisible until explicitly set. -->

<?php
	include('html/footer.html');
?>
