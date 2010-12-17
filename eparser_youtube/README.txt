; $Id:$

Youtube FeedApi Extendable Parser

Author: Vojtěch Kusý <wojtha@gmail.com>
License: GNU GPL v2

== Description:

Parse the most used parts of Youtube feed, but support is limited (e.g.
no listings, opensearch or geotags).

== How to use:

Enable Youtube type parser and also Youtube namespace parser on the feed node.

Note: Remember that Extensible Parser (Eparser) needs to be the only active
parser on the feed item.

== Required modules:

http://drupal.org/project/feedapi
http://drupal.org/project/ctools
http://drupal.org/project/feedapi_eparser

== Recommended:

http://drupal.org/project/feedapi_mapper
