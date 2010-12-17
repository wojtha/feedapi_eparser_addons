<?php
// $Id: FeedapiEparserTypeSlideshare.class.php,v 1.1.2.1 2009/10/22 16:52:10 andrewlevine Exp $
/**
 * @file
 * Parses a brightcove video link out of a brightcove JSON feed to
 * be used by media_brightcove / emfield. Extends JSON type
 *
 */

/**
 * Eparse Youtube type parsing plugin.
 */
class FeedapiEparserTypeSlideshare extends FeedapiEparserTypePluginXML {

/**
   * Get a SimpleXML object containing the data for a feed.
   *
   * @param $feed
   * The feed to be looked up.
   * @return
   * SimpleXML object setup with the content of the feed.
   */
  protected function getXML() {

    if (!isset($this->xml)) {
      if (!defined('LIBXML_VERSION') || (version_compare(phpversion(), '5.1.0', '<'))) {
        @$this->xml = simplexml_load_string($this->getValidXmlUtf8Source(), NULL);
      }
      else {
        @$this->xml = simplexml_load_string($this->getValidXmlUtf8Source(), NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
      }
    }

    return $this->xml;
  }

  protected function getValidXmlUtf8Source() {
    return preg_replace('/[^\x9\xA\xD\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u','', $this->source);
  }

  function getGlobalContext() {
    return $this->getXML()->channel;
  }

  function getItems() {
    return $this->xml->xpath('//item');
  }

  function parseGlobal($global_context) {
    $rss_data = $this->feed->parsed_source->youtube;

    // Detect the title.
    $this->feed->parsed_source->title = isset($rss_data->title) ? $rss_data->title : "";
    // Detect the description.
    $this->feed->parsed_source->description = isset($rss_data->description) ?
    $rss_data->description : "";

    // Detect the link.
    $this->feed->parsed_source->link = isset($rss_data->link) ? $rss_data->link : "";

    // Parse some optional items.
    $this->feed->parsed_source->options = $rss_data;

    // Get a timestamp version of the date.
    $this->feed->parsed_source->timestamp = isset($rss_data->pubDate) ?
    $this->parseDate($rss_data->pubDate) : time();
  }

  /**
   * Parse feed items.
   */
  function parseItem(&$source_item, &$item) {
    // Get children for current namespace.
    $data = $item->slideshare;

    // get nodes in atom
    $atom = $source_item->children('http://www.w3.org/2005/Atom');

    // get nodes in media: namespace for media information
    $media = $source_item->children('http://search.yahoo.com/mrss/');

    // get video player URL
    $attrs = $media->content->player->attributes();
    $embed = $attrs['url'];

    // get video ID
    $id = $source_item->guid;

    // get <slideshare> node
    $slideshare = $source_item->children('http://slideshare.net/api/1');
    if (preg_match('/id="__ss_(\d+)"/i', $slideshare->embed, $matches)) {
      // Better ID
      $id = 'slideshare:' . $matches[1];
    }

    $created = strtotime($source_item->pubDate);

    $item->guid = $id;
    $item->title = $media->content->title;
    $item->author = $source_item->author;
    $item->description = $media->content->description;
    $item->thumbnail = $slideshare->meta->thumbnail;
    $item->type = $slideshare->meta->type;
    $item->tags = explode(',', $media->content->keywords);
    $item->embedded_player = $embed;
    $item->watch_page = $source_item->link;
    $item->view_count = $slideshare->meta->views;
    $item->comments_count = $slideshare->meta->comments;
    $item->submitted = $created;
    $item->updated = $created;

    $item->options = $data;

    // Some legacy compatibility.
    $item->options->original_author = (string) (!empty($this->xml->channel->title) ? $this->xml->channel->title : '');
    $item->options->original_url = $item->watch_page;
    $item->options->submitted = $created;
    $item->options->updated = $created;
    // Required
    $item->options->guid = $id;
    $item->options->timestamp = $created;
  }

}
