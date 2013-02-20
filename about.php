<?php
	include('html/header.html');
?>

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


<h1>About the Site</h1>

<p>
My Notepad Info is a fast, simple, free notepad service. "Simple" means that the entire site is basically just a wrapper around a textbox. Instead of e-mailing yourself notes or URLs to bookmark yourself from someone else's computer, you can quickly save and retrieve them here in your notepad.
</p>

<p>
This isn't a novel idea, a quick Google search will turn up dozens and dozens of similar services. But originally it did have a somewhat unique emphasis on speed and simplicity. Many other notepads weren't as simple and easy to use. Some were downright clunky, requiring large pages be loaded and refreshed several times over the course of logging in, modifying notes, saving notes, and logging out. Some were intended only for one-time use or otherwise impractical for someone who needs to constantly store and edit notes.
</p>

<p>
I originally wrote My Notepad Info for myself, and it continues to exist primarily as a personal side-project. Back in the mid 2000s (before smartphones, 4G, and high bandwidth) I got tired of e-mailing myself links from school computers. I couldn't find any online notepad that had exactly what I wanted so I wrote one myself. I wrote the first version from scratch in about 8 hours. It was both a quick hack to get the online notepad I wanted and an excuse to learn a bit about AJAX. I never really publicized the site, it was honestly just for myself. Once I realized that other people had been using it for years I came back, cleaned it up a bit, and added some features. It still has something of a "quick hack" feeling to it (particularly in the theme), but it's just a hobby project. The source code is available <a href='https://github.com/B-Con/my-notepad-info'>on GitHub</a>. I keep a <a href='updates.php'>list of important updates</a>.
</p>

<p>
With time this site becomes less relevant. Smartphones and cloud service integration provide so many more convenient ways of doing the same thing. But interestingly, years later, despite having a 4G smartphone and the Google ecosystem,I still use it occationally. I presume the need isn't completely dead and I will keep the site up so long as I can imagine using it.
</p>


<h1>Speed and Simplicity</h1>

<p>
What does the site do to be "fast and simple"? It's more about what it doesn't do than what it does do. It just uses basic techniques for web optimization, like asynchronous JavaScript, no images, minified JS and CSS files, compressed files where possible, minimal DNS lookups, and a nearly flat backend. For the simplicity aspect I just avoid requiring page refreshes and make everything easily accessible through the keyboard. Absolutely nothing fancy at all.
</p>


<h1>About the Author</h1>

<p>
I'm Brad Conte, a software developer who keeps a variety of side personal projects going. My areas of specific interest are cryptography and Linux. You can read more about me on my <a href="http://bradconte.com/about_me.html">personal website</a>.
</p>


<?php
	include('html/footer.html');
?>
