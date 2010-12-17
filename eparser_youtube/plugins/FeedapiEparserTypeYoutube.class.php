<?php
// $Id: FeedapiEparserTypeYoutube.class.php,v 1.1.2.1 2009/10/22 16:52:10 andrewlevine Exp $
/**
 * @file
 * Parses a brightcove video link out of a brightcove JSON feed to
 * be used by media_brightcove / emfield. Extends JSON type
 *
 */

/**
 * Eparse Youtube type parsing plugin.
 */
class FeedapiEparserTypeYoutube extends FeedapiEparserTypePluginXML {

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
    $data = $item->youtube;

    // get video ID
    $id = 'youtube:' . end(explode(':', $source_item->guid));

    // get nodes in atom
    $atom = $source_item->children('http://www.w3.org/2005/Atom');

    // get nodes in media: namespace for media information
    $media = $source_item->children('http://search.yahoo.com/mrss/');

    // get video player URL
    $attrs = $media->group->player->attributes();
    $watch = $attrs['url'];

    // get video thumbnail
    $attrs = $media->group->thumbnail[0]->attributes();
    $thumbnail = $attrs['url'];
    $attrs = $media->group->player->attributes();
    $player = $attrs['url'];

    // get <yt:duration> node for video length
    $yt = $media->children('http://gdata.youtube.com/schemas/2007');
    $attrs = $yt->duration->attributes();
    $length = $attrs['seconds'];

    // get <yt:stats> node for viewer statistics
    $yt = $source_item->children('http://gdata.youtube.com/schemas/2007');
    $attrs = $yt->statistics->attributes();
    $viewCount = $attrs['viewCount'];
    $favCount = $attrs['favoriteCount'];

    // get <gd:rating> node for video ratings
    $gd = $source_item->children('http://schemas.google.com/g/2005');
    if ($gd->rating) {
      $attrs = $gd->rating->attributes();
      $rating = $attrs['average'];
    } else {
      $rating = 0;
    }

    $created = strtotime($source_item->pubDate);

    $item->guid = $id;
    $item->title = $media->group->title;
    $item->author = $source_item->author;
    $item->updated = strtotime($atom->updated);
    $item->description = $media->group->description;
    $item->thumbnail = $thumbnail;
    $item->category = $media->group->category;
    $item->tags = explode(',', $media->group->keywords);
    $item->embedded_player = $player;
    $item->watch_page = 'http://www.youtube.com/watch?v=' . $id;
    $item->duration = $this->secsToTime($length);
    $item->view_count = $viewCount;
    $item->fav_count = $favCount;
    $item->rating = $rating;
    $item->submitted = $created;

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

  /**
   *  Display seconds as HH:MM:SS, with leading 0's.
   *
   *  Taken from Feeds Youtube Parser module
   *
   *  @param $seconds
   *    The number of seconds to display.
   */
  public function secsToTime($seconds) {
    // Number of seconds in an hour.
    $unith =3600;
    // Number of seconds in a minute.
    $unitm =60;

    // '/' given value by num sec in hour... output = HOURS
    $hh = intval($seconds / $unith);

    // Multiply number of hours by seconds, then subtract from given value.
    // Output = REMAINING seconds.
    $ss_remaining = ($seconds - ($hh * 3600));

    // Take remaining seconds and divide by seconds in a min... output = MINS.
    $mm = intval($ss_remaining / $unitm);
    // Multiply number of mins by seconds, then subtract from remaining seconds.
    // Output = REMAINING seconds.
    $ss = ($ss_remaining - ($mm * 60));

    $output = '';

    // If we have any hours, then prepend that to our output.
    if ($hh) {
      $output .= "$hh:";
    }

    // Create a safe-for-output MM:SS.
    $output .= check_plain(sprintf($hh ? "%02d:%02d" : "%d:%02d", $mm, $ss));

    return $output;
  }

}
