<?php
// $Id: $

/**
 * Eparser namespace handler.
 */
class FeedapiEparserNamespaceZeroCategory extends FeedapiEparserNamespacePlugin {

 function parseGlobal($global_context) {
 }

  function parseItem(&$source_item, &$item) {
//    dpm($source_item, 'source_item');
//    dpm($item, 'item');
    $item->zerocategory = '';
    if (isset($item->rss20->tags[0])) {
      $item->zerocategory = $item->rss20->tags[0];
    }
  }
}
