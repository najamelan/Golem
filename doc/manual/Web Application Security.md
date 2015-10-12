# Web Application Security

## Introduction

This document tries to explain the basic concepts of web application security before illustrating how the design of Golem tries to accomodate you to put security measures in place. The limitations of this text are concerning the actual application (eg. in PHP, HTML, JavaScript), and it won't teach you anything about setting up the operating system of the server, nor about configuring the http server (eg. apache, nginx, ...).

## Required reading to understand this document

- Basic understanding of web servers, PHP, HTML, HTTP will help you to understand.

## Recommended reading to understand this document

## Types of attacks and vulnerabilities.

There are two main considerations:

1. Vulnerabilities that affect only the client (eg. XSS).
   For this type of vulnerabilities the main considerations are:
    - Make sure input data we send back to clients will not contain anything it's not supposed to (eg. code that executes, like JavaScript), Spam, things that can crash the browser, open popups, ...
    - Make sure input we send back can not escape it's context (eg. a value put in an html attribute can not escape the attribute context and add event handlers)
    - Make sure all data is consistent as far as character encoding is concerned.

2. Vulnerabilities that compromise the server (and thus also the client):
   Consider to:
    - Prevent (arbitrary) code execution
    - Prevent unauthorized file system access
    - Prevent security information leakage (which might help further attacks)
    - Prevent privilege escalation within the application (eg. XSRF)
    - Robust user authentification
    - Prevent security measures (eg. logging, validation, escaping) from being turned off or changed by an attacker who has been able to inject (php) code.

## Security measures

### Character encoding

As a general guideline it's good practice to use the same character encoding all throughout your application. That means, choose one single encoding to be used by your development machine, your server OS, accept that one encoding from clients that send input, use the same encoding for your php files, in your database, in output you send back to the client, and signal it correctly (eg. send correct http headers and html meta tag). This ensures that every part of the application including the browser will interprete string data the same way, which prevents data to pass validation on the server, yet do something nasty in the client.

If you are looking for an encoding that doesn't take to much space (eg. 32 bytes like UTF-32) yet allows all Unicode characters to be covered, rejoice, UTF-8 does exactly that and you are strongly encouraged to use it!

Golem has a String library which is an object oriented frontend to the php extension mbstring giving you both safety and convenience. For more information, read [Character encoding](Encoding.md)


### Input sanitation and validation

When a user sends input, there are things that are allowed and others that aren't. Maybe they will upload a file with: '../../../../etc/shadow' as filename. Oops, either sanitation changes that to some default value, or you have to reject that, because you sure don't want to let this be interpreted as a path later on. Maybe a file is to big in size. Maybe some content for a CMS is not supposed to contain JavaScript, so either strip that out (sanitize) or reject it all together. Usually it's good practice to try and save the day by sanitizing but to have a second check that can just tell you with certainty if a given input is acceptable or not. You should run validation after sanitation.
The tricky part here is to make sure that your validation routines will interprete data the same way as the client, which is always difficult in web applications. Say you write a syntax highlighter. If you want to be certain that you tokenize the stream correctly, best just use the actual tokenizer of the compiler. That way, you're never out of sync and it saves work. But what about the web. You deal with the server, but data gets interpreted in the browser, wait in the browserS. There is a plethora of browsers and evolving web technologies out there and it's difficult to keep track of all of them. In any case you can't use their code during validation.

Golem tries to simplify that task for you by creating tools that take into account the web standards and different browser quirks to let you define in a simple way what is valid and what not and Golem will try to make sure it's interpreted the same way by browsers. See [Input Validation](Input Validation.md) for more details.


### Output Escaping

When sending data to a browser say: `<a href="<%user input here%>">` and the user sends you `http://somelink"><script>evil javascript</script>` If you stick that in the attribute, the double quote will escape from the attribute context into html context and hopla, the user can now run JavaScript potentially in some other users browser. So for every context there's ways to escape data (eg. encode all characters that have some special meaning in that context) in order to make sure it cannot be interpreted as anything else by the browser, the filesystem, the database, ...

Golem provides you with a number of encoders to make something like this as easy as: `$escape->html5Attr( $userInput );`. Read all about it in: [Output Escaping](Output Escaping.md).


### Purifying HTML Content

One thing that can be done either on input or output is purifying HTML content that will be included in pages on the site. If we have a CMS for example and we want to allow users to use a subset of HTML to add formatting and other things to content, we should strip out all features of HTML that are unsafe. There is already an industry standard tool for this which is called HTMLPurifier and Golem incorporates it as a git submodule so you have it available directly for sanitizing submitted content. In principle if you sanitize it on input (eg. before storing it in a database) you don't really have to do it again on output, but if you database gets compromized, it could be a defense in depth to sanitize on output as well, or even to run it on output but not on input, depending on the performance implications. In any case you can read [Purifying HTML](HTMLPurifier.md) on how to use it.


### Arbitrary Code Execution

If a user can upload files to your server, there is great care to be taken in order to prevent that upload from being interpreted as php or shell code. If you eval strings, thing get even trickier (avoid if you can). In any case there is a number of measure you can take to mitigate alot of the risk.

- Give minimum rights to the user account that executes php or that runs the http server
   - PHP files that can be accessed by the user (in their address bar) must be executable by your PHP user and must be in your sites document root.
   - PHP files that will be included by other PHP files need to be readable by your PHP user
   - Configuration files should be readable by PHP user
  If you disable anything else, write/read permissions where you can, you make life on an attacker a lot harder.

- Files that get interpreted and files that don't: Files in your upload directory probably need to be world readable, however only the php user needs to be able to write them, and no one needs to execute them. Further, if you disable php interpretion for this directory, you don't risk php code in uploaded files to be interpreted. In the worst case it gets send back to the user as text.


### What if an attacker can get php code included in your site generation?

If malicious php code runs, there's still quite some things you can do to minimize the damage. If no other php files are writeable by the PHP user, for starters they won't be able to change any other files. Basically what is difficult to protect are resources that PHP needs to write to:
- temp directory (sessions)
- upload directory
- log files

Anything else should basically be safe if you configured your server correctly.

Within the script execution (script that generates your website), There is a number of risks that can be mitigated. If you disable the ReflectionClass in your php.ini, you can basically keep data and objects private. An important point here is not to use static functionality that stores state that can be changed. Eg. If you use a logger framework that allows static access to the loggers and allows code to turn them off, you have a problem. 

Golem is designed so that you can guarantee settings don't change during script execution. That's why you have to instantiate a Golem library object rather than access things statically. The inconvenience in this is that every class in Golem needs a golem object to get hold of it's default settings, adding an extra parameter to the constructor. However if you keep these objects private and pass them to constructors of objects that need them, you know they cannot be changed by any other included code. You can count on them.





