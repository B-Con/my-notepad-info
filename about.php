<?php
	include('html/header.html');
?>

<h1>More About My Notepad Info</h1>

<h2>The Problem</h2>
<p>
Smartphones and cloud-y services probably offer convenient solutions for saving most the content we come across. But unfortunately, edge cases in life still throw situations at us that our normal tools don't always handle gracefully, such as:
</p>

<ul>
	<li>save URLs from a computer that isn't ours</li>
	<li>write notes that wouldn't be convenient to save for later</li>
	<li>stash away blobs of text to retrieve later</li>
</ul>

<p>
Traditionally what do we do when we encounter a semi-bizarre situation? We stuff e-mail ourself. We use our email accounts as freaking temporary notepads. It's horrible. If we get caught without a smartphone, we lose the ability to save things effecitvely. We go to an e-mail provider, possibly log out of someone else's account, log into our own account, open a couple pages, stick something in an e-mail body, then send it to ourselves. Then we later go get it and delete it. Many web-based e-mail clients aren't particularly fast, either, so we have moderately long page load times several times over. (And doing it over a slow Internet connection feels like a pain because it takes 2 minutes to do something that should take 20 seconds. It's manageable, but it feels like an eternity.)
</p>

<h2>The Solution</h2>
<p>
Leaving notes should be easy, fast, and painless. You login on the homepage, we show you your notes, you edit them, you save them, you leave. We even try to auto-save them as you go. Slow computer, slow Internet connection, it doesn't matter, the site should run smoothly. If you use the keyboard shortcuts you can get in, leave a note, and get out in 8 seconds.
</p>

<p>
The notepad form has a minimal straight-forward design and a couple of supporting features. It's all about getting a password-protected text box in front of you with your notes as hassle-free as possible.
</p>

<p>
What does the site do to be fast and simple? It's more about what it doesn't do than what it does do. It just uses basic techniques for web optimization, like asynchronous JavaScript, no images, minified JS and CSS files, compressed files where possible, minimal DNS lookups, and a nearly flat backend. For the simplicity aspect I just avoid requiring page refreshes and make everything easily accessible through the keyboard. Absolutely nothing fancy at all.
</p>


<h1>Contact</h1>
<p>
Comments? Suggestions? Bugs? Feel free to give feedback on any bugs you find, features you want, or observations you have. Use the form here or send a direct e-mail to <a href='mailto:admin@mynotepad.info'>admin@mynotepad.info</a>.
</p>

<p>
If you are reporting a <b>bug</b>, please provide the status text of the error, what you were doing when the error happened, your username, and about how long ago it happened (if it was more than a few minutes). These details are often necessary to resolve the issue.
</p>

<form action='#' onsubmit='submitFeedbackApi(); return(false);'>
<table>
	<tr>
		<td><span class='login-prompt-text'>Username:</span></td>
		<td><input type='text' class='login-field' id='name' tabindex='1' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>E-Mail <small>(only if you want a reply):</span></small></td>
		<td><input type='text' class='login-field' id='email' tabindex='2' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>Subject:</span></td>
		<td><input type='text' class='login-field' id='subject' tabindex='3' /></td>
	</tr>
	<tr>
		<td><span class='login-prompt-text'>Message:</span></td>
		<td><textarea id='message' tabindex='4' rows='6' cols='50' ></textarea></td>
	</tr>
</table>

<p>
<input class='clicky' type='submit' value='Submit Feedback' tabindex='5' />
</p>
</form>


<h1>About the Author</h1>

<p>
I'm Brad Conte, a software developer who keeps a variety of side personal projects going. My areas of specific interest are cryptography and Linux. You can read more about me on my <a href="http://bradconte.com/about_me.html">personal website</a>.
</p>


<?php
	include('html/footer.html');
?>
