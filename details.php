<?php
	include('html/header.html');
?>

<h1>General Features</h1>

<p>
Here's a summary of what My Notepad Info offers:
</p>

<ul>
<li>A simple, no-hassle interface.</li>
<li>An easy way to get your notepad to your e-mail.</li>
<li>Auto-saving.</li>
<li>Customizable size and colors.</li>
</ul>

<p>
It's worth noting that <b>login</b> sessions are maintained on the actual page, not in the browser. When you leave the notepad page, you can't come back to it and expect to still be logged in. You will need to login every time you re-visit the notepad home page. This fits the "show up, save, leave" work-flow that My Notepad Info is designed to fit, but may prove initially confusing for some.
</p>

<p>
The <b>auto-save</b> feature is on by default and attempts to keep you from accidentally losing your notes. It will save your notes in regular time intervals, when you choose to logout, and when you navigate away from the page. But for reliability reasons, it is advised that you manually save the notepad before leaving the page.
</p>

<p>
That's all. Get in, save some notes, get out.
</p>


<h1>Using the Notepad Efficiently</h1>

<p>
The best feature you can take advantage of is the tab order and hotkeys on the notepad page. MyNotepad is designed so that you never have to touch the mouse.
</p>

<ul>
	<li>The cursor is automatically placed in the "username" field. Fill this in.</li>
	<li><b>Tab</b> to the "password" field and enter your password.</li>
	<li>Then press <b>Enter</b> to submit your login info. (If your login fails these fields will be cleared and the cursor will return to the "username" field.)</li>
	<li>When you log in, your notepad and settings will be loaded and the focus will be set in the notepad.</li>
	<li>From the notepad you can <b>tab</b> to the "save" button. Alternatively, you can use the <b>s</b> hotkey.</li>
	<li>If you want to continue editing your notes, you can "reverse tab" back to the notepad. (In most browsers, this is done via <b>Shift + Tab</b>.) Alternatively you can use the notepad's "hotkey", the letter <b>n</b>. You can toggle between the notepad and the save button until you are ready to logout.</li>
	<li>From the "Save" button, a tab will take you to the "log out" link.</li>
</ul>

<p>
The basic idea is: "username - TAB - password - ENTER - notes - TAB - ENTER (- TAB - ENTER)".
</p>

<p>
Note: To use hotkeys: In Firefox press <b>Alt + Shift + C</b>, and in Internet Explorer press: <b>Alt + C</b>, where C is the hotkey character.
</p>

<?php
	include('html/footer.html');
?>
