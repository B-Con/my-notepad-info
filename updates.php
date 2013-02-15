<?php
	include 'html/header.html';
?>

<h1>Site Updates</h1>

<h2>Febuary, 2012</h2>

<p>
Six or so years after I first wrote My Notepad Info, I've given it a major revisement.
</p>

<p>
The site premise is still the same and most of what it does is still the same. I didn't want change what it is, just how it works. The original site was basically a quick hack job, so I rewrote the entire client and server to clean things up, add features, and address bugs. I also redid a lot of the theme.
</p>

<p>
Visible changes:
</p>

<ul>
	<li> Feature: e-mail your notepad contents to yourself (without needing a password). </li>
	<li> Feature: auto-save option now includes auto-saving on logout, page leaving, as well as periodically. </li>
	<li> Feature: notepad supports native browser click-n-drag resizing. </li>
	<li> Feature/bug: you can now change your e-mail address. </li>
	<li> Bug: fixed some cases of lost notepad contents. </li>
	<li> Bug: fixed password-reset being prevented. </li>
	<li> Open sourced, <a href='https://github.com/B-Con/my-notepad-info'>now on GitHub</a>. </li>
	<li> Lots of UI tweaks. The main layout and color scheme is still the same, but everything has been tweaked. It's not gorgeous, but it's cleaner and a bit more polished. </li>
</ul>

<p>
Behind the scenes changes:
</p>

<ul>
	<li> Improved logging for finding bugs. </li>
	<li> A couple security tweaks, such as login rate limiting. </li>
	<li> Some HTML5 reliance. </li>
	<li> Cliche "misc bugfixes". </li>
</ul>

<p>
Please let me know if the new site breaks for you, feedback is welcome. The new design includes some HTML5 features and very little fallback for older browsers. Any browser from the last 2 years should work fine, but, and if for some reason it doesn't work in a browser more than 2 years old, I'm not interested in fixing it -- sorry.
</p>

<p>
Unfortunately, there are no major new features in this update, unless you count the theme sucking less as a "feature". Some may come at a future date.
</p>


<h2>2008</h2>

<p>
I made a few minor styling tweaks. No functionality changes.
</p>


<h2>2007</h2>

<p>
The site is live.
</p>

<?php
	include 'html/footer.html';
?>
