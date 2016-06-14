# Differences from ESAPI

## Ambitions
The only features provided right now are:

- logging (very basic at the moment, needs work)
- Input validation
- Output escaping
- Meta data cleaning for uploaded files (for privacy)
- String class which will make encoding nightmares unexistant
- Randomizer
- A wordpress plugin to allow people to use Golem in wordpress easily

## Design

The design is different from ESAPI. I mainly try to provide simplicity and convenience. 

ESAPI has code spread out all over the place converting strings to utf32 and back. Golem has one central string class which deals with encoding issues transparently. This simplifies alot of code. 

There is a central options system which is easy to use. You can put all your default options in a yaml file (as opposed to xml, although it could easily be made to work with xml if you so wish to). The configuration file should be read-only to the php user, as should php files themselves (Golem's php files, as well as ideally your application's files).

## Security

No more static access. Static access is a security vulnerability unless it's read only. Any code an attacker manages to include in a script can change security settings if they can be accessed statically, like log4php::rootLogger->disable() which would disable all logging from that point on. Thus exit log4php. To benefit from this you will have to change some php settings as explained in ... because reflection classes allow any code to access anything, so they have to be disabled.

Exit canonicalization. Ideally you escape exactly once for every unescaping the browser will do. Canonicalization violates this rule and thereby introduces false intrusion alerts and will mangle perfectly valid data. If someone can provide one example of an attack that would need canonicalization, and that works without counting on a browser vulnerability, we can add it again, but for now I have found absolutely no use for canonicalization, but a lot of trouble with it.

## Specific differences

- HTML escaping: be standard compliant
- JavaScript escaping: escape comma

Everything is fully documented with examples and unit tested.
