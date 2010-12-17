; $Id:$

Zero Main Category RSS20 FeedApi Extendable Parser

Author: Vojtěch Kusý <wojtha@gmail.com>
License: GNU GPL v2

== Description:

Picks first category at zero index from RSS2.0 item which is the primary
category of the item in the most cases.

== How to use:

Enable RSS20 type parser for the feed and then check ZeroCategory at Namespace
settings.

Note: Remember that Extensible Parser (Eparser) needs to be the only active
parser on the feed item.

== Required modules:

http://drupal.org/project/feedapi
http://drupal.org/project/ctools
http://drupal.org/project/feedapi_eparser

== Recommended:

http://drupal.org/project/feedapi_mapper
