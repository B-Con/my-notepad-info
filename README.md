My Notepad Info
===
My Notepad Info is a simple web-based notepad. It's a side-project, started years ago when I needed a simple, fast online notepad. It consists of an AJAX JS client that talks to a PHP/MySQL-powered RESTful API.

Goals
---
The goal is for the entire project to act like a simple, authenticated wrapper around a textbox. It should be flexible enough to be used by arbitrary users.

Since I wrote this primarily for my own use, that last requirement is in part to keep myself from writing sloppy, ad-hoc code. As a bonus it makes it something I can offer to the public.

The biggest implicit goal of the project is to stay simple, and I have a couple side "goals" to that end. One is to stay under about 2000 lines of custom code. This is somewhat arbitrary, and in general I am not a fan of "lines of code" limits, but I wanted some metric to ensure it didn't grow too much. Another minor goal is that it needs to be primarily "from sctratch". When I wrote the original version of the project it was in part to help me learn AJAX. I like keeping some of my side projects as somewhat "from scratch" so that I stay familiar with the  base-level technologies they use.

Known "Limitations"
---
* _No mobile integration_ - The era of mobile devices and cloud services make this type of browser-based service  out-of-date for many. That's OK, I still occationally need it as it is and don't need to integrate it with anything mobile or cloud-oriented.

* _The theme kind of sucks_ - I know that. But I like to experiment with making themes from scratch and I'm not very gifted in aesthetics, so I try out themes on my personal projects. Since the goal is not to attract a wide user-base and I'm the targeted audience, I don't mind using this to experiment.

* _No CMS_ - The site content is stored as nearly flat text files. But that's OK, I want the project to be just about a couple .JS and .PHP files. It's all about wrapping a textbox.
