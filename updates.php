<?php
	include 'html/header.html';
?>

<h1>Site Updates</h1>

<h2>Febuary X, 2012</h2>

<p>
Six or so years after I first wrote My Notepad Info, I've given it a major internal revisement.
</p>

<p>
The site premise is still the same. I didn't want change what it is, just how it works. The original site was basically a quick hack job, so I rewrote practically all the code for the client and server to clean things up, add some features, and address some bugs. I also redid the majority of the theme, although it's still very simple.
</p>

<p>
What changed:
</p>

<ul>
<li> Added ability to e-mail your notepad contents to your e-mail (without a password). </li>
<li> Added ability to change your e-mail address. </li>
<li> Fixed a bug that caused lost notepad changes. </li>
<li> Open sourced, <a href='https://github.com/B-Con/my-notepad-info'>now on GitHub</a>. </li>
<li> The auto-save option now includes auto-saving on logout, as well as periodically. </li>
<li> The notepad supports basic click-n-drag resizing in compatable browsers. </li>
<li> Lots of UI tweaks. The main layout is still similar, but everything has been tweaked. It's not gorgeous, but at a bit cleaner. </li>
<li> Fixed a password-reset bug. </li>
<li> Slightly faster page loading times. (Not by much, but everything should technically load a little faster.) </li>
</ul>

<p>
Behind the scenes:
</p>

<ul>
<li> Improved logging for finding bugs. </li>
<li> A couple security tweaks, such as login rate limiting. </li>
<li> Cliche "misc bugfixes". </li>
<li> Some HTML5 reliance. </li>
</ul>

<p>
Please let me know if the new site breaks for you, feedback is welcome. The new design includes a little bit of HTML5 reliance and I'm not very interested in supporting old browsers (ie, older than 2 years). The old site interface (not backend, just the interface) will still be available <a href='http://archive.mynotepad.info'>here</a>, just in case anyone needs it.
</p>

<p>
This continues to be a pretty basic service I run for myself, so I haven't done anything good or fancy with the design.
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
