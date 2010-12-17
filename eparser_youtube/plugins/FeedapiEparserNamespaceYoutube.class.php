<?php
// $Id: FeedapiEparserNamespaceYoutube.class.php,v 1.1 2009/10/21 22:49:37 neclimdul Exp $
/**
 * @file
 * Parse RSS 2.0 data from its namespace.
 */

/**
 * Eparser RSS 2.0 namespace handler.
 */
class FeedapiEparserNamespaceYoutube extends FeedapiEparserNamespacePlugin {

  function parseGlobal($global_context) {
    // Find what we can find.
    $this->feed->parsed_source->youtube = $this->convertStrings(array(
      'title',
      'description',
      'link',
      'managingEditor',
      'pubDate',
      'lastBuildDate',
      'category',
      'generator',
      'image' => array(
        'url',
        'title',
        'link',
      ),
    ), $global_context);
  }

  function parseItem(&$source_item, &$item) {

    $data = $this->convertStrings(
      array(
        'guid',
        'title',
        'author',
        'updated',
        'description',
        'thumbnail',
        'category',
        'tags',
        'embedded_player',
        'watch_page',
        'duration',
        'view_count',
        'fav_count',
        'rating',
        'submitted',
      ), $source_item);

    // Required values.
    $data->title = $this->getTitle($data->title, $data->description);
    $data->timestamp = isset($data->submitted) ? $this->parseDate($data->submitted) : time();
    // FeedAPI Node requires this to exist
    $data->guid = isset($data->guid) ? $data->guid : NULL;

    $additional_taxonomies = $this->parseCategories((array) $source_item);
    // Some legacy compatibility.
    $data->domains = $additional_taxonomies['RSS Domains'];
    $data->tags = $additional_taxonomies['RSS Categories'];

    $item->youtube = $data;
  }

  /**
   * Parse a list of categories.
   *
   * Note: This was abstracted from code in parser_common_syndication and I'm
   * not familiar with all the logic.
   * @param $context
   * @return unknown_type
   */
  function parseCategories($context) {
    $additional_taxonomies = array(
      'RSS Categories' => array(),
      'RSS Domains' => array(),
    );

    if (isset($context['category'])) {
      // SimpleXML does not parse single entries into arrays so fix it.
      if (!is_array($context['category'])) {
        $context['category'] = array($context['category']);
      }
      foreach ($context['category'] as $category) {
        $additional_taxonomies['RSS Categories'][] = (string) $category;
        if (isset($category['domain'])) {
          $domain = (string) $category['domain'];
          if (!empty($domain)) {
            if (!isset($additional_taxonomies['RSS Domains'][$domain])) {
              $additional_taxonomies['RSS Domains'][$domain] = array();
            }
            $additional_taxonomies['RSS Domains'][$domain][] = count($additional_taxonomies['RSS Categories']) - 1;
          }
        }
      }
    }

    return $additional_taxonomies;
  }
}
