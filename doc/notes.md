General Notes
===


Bugs / Limitations
---
* Current web host sucks. Can't use Apache's "mod_deflate.c" or "mod_expires.c" for server-level compression and cache expiration. Currently compress PHP files via "php.ini".

* Host sucks more. There are seemingly random database connection failures that happen just once. The DB error messages say that the IP of the web server is not allowed to connect to the database server -- no idea why. Log analysis and tech support from the host is unable to cast any light on the problem. Hack around this by having the DB connect attempt wait and retry.


To-Do
---
* Custom font styles in the notepad - Probably not a good idea.

* Give each page it's own status box location and feedback. - Current method is consistent but not very UI friendly. Definitely easiest, though.


Misc. Thoughts
---
The black/blue color theme has nothing to do with traditional notepad design schemes. I know.

