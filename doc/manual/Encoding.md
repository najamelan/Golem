# Character Encoding


## Introduction

This document describes the design of Golem as far as character encoding is concerned and explains everything you need to know to use Golem safely when it comes down to the encoding of strings.


## Required reading to understand this document

- Golem documentation: Options


## Recommended reading to understand this document

- Joel Spolsky: What every programmer should know about unicode


## The problem

As Joel Spolsky correctly puts it, there is no way from a piece of string data to correctly deduce what encoding it is in, so it is damn well useless to have a string if you don't know what encoding it's in.

That means that whenever someone feeds you a string, you need to know what encoding it's in, and so does Golem. The following sections describe the different ways you can tell Golem what encoding is used for the strings you feed it.


## Declaring encodings to Golem

First of all, there is one encoding setting that every programmer should be aware of:

**Golem.configEncoding**
This is used to declare the character encoding of the php and configuration files of Golem itself. Any time Golem compares some string to any values that where hardcoded in php files or in configuration files, it will assume that those values are encoded in this encoding. It defaults to UTF-8 and I strongly recommend you write any configuration files in UTF-8. If you need anything else you will have to convert the default configuration file and all the php files of Golem to your encoding, because there is no way to distinguish between the config files and the php files. In any case to set the configuration option:

```yaml

Golem:

   configEncoding: 'UTF-16'
```

or:

```php

$golem = new Golem( [ 'Golem' => [ 'configEncoding' => 'UTF-16' ] ] );
```

### Case 1: You use 1 and only 1 character encoding all throughout your application

This is the recommended scenario and on top of that it's recommended you choose UTF-8. If you do, you don't need to specify anything else anywhere and you don't need to declare encoding when calling specific Golem functions. This is the safest and most convenient way of writing web applications. If you use only one encoding, but it isn't UTF-8, than the only thing to do is set 1 global option:

**String.encoding**: used as default for all strings in the application

```yaml

String:

   encoding: 'UTF-16'
```

or:

```php

$golem = new Golem( [ 'String' => [ 'encoding' => 'UTF-16' ] ] );
```

### Case 2: You mix different encodings throughout your application. 

You will need to pass the encoding of newly created strings that aren't in the default encoding to the constructor of Golem\String to override the default encoding.

## What if my users send data in the wrong encoding?

As you take (potentially malicious) user input, it might not be encoded the way you expect it to be. There's nothing you can do about it, it's user input after all. Every string passed to Golem will be sanitized to be valid in the encoding you specified, and if that's impossible an exception is thrown. Under no circumstances will Golem work with or return to you a string that is not valid in the encoding you specified.

Sanitizing means that if you send in a string in an encoding different than the encoding you specify, Golem might ruin your string (eg. replace characters with the Unicode replacement character). If this happens in a legit scenario, there is a programming error somewhere which needs to be fixed, and if the input is malicious, you shouldn't be worried about the string getting mangled in the first place.

It's your call wether you allow user input with the unicode replacement character into your database, or whether you let the exception end the script.
