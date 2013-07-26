<?php

if (file_exists(dirname(__FILE__) . '/stellar_tweetbot_config.php')) {
  require_once(dirname(__FILE__) . '/stellar_tweetbot_config.php');
}
else {
  exit;
}

require_once $twitter_oauth_path;

// This function grabs the last tweets we cached so we don't try to tweet them again... saves API hits
function getOldIDs () {
  global $cached_file_path;
  $handle = fopen($cached_file_path, 'r');
  $contents = fread($handle, filesize($cached_file_path));
  fclose($handle);

  $oldxml = simplexml_load_string($contents);

  $oldIDs = array();

  foreach($oldxml->entry as $entry) {
    if ($entry->link->attributes()->href) {
      $urlstring = (string)$entry->link->attributes()->href;
      if (preg_match("/twitter.com\/[A-Z0-9_]+\/status\/([0-9]+)/i", $urlstring, $matches)) { 
        array_push($oldIDs, $matches[1]);
      }
    }
  }
  return $oldIDs;
}

// This function looks for new tweets and retweets them out
function retweet() {

  global $consumer_key, $consumer_secret, $access_token, $access_token_secret, $feed_urls, $cached_file_path, $excludeIDs;
  foreach($feed_urls as $feed_url) {
    $toa = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);

    $ch = curl_init($feed_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $feedcontents = curl_exec($ch);
    curl_close($ch);

    $oldIDArray = getOldIDs();

    $newxml = simplexml_load_string($feedcontents);
    if (isset($newxml->entry)) {
      foreach($newxml->entry as $entry) {
        if ($entry->link->attributes()->href) {
          $isreply = preg_match("/^@/i", (string)$entry->title); 
          $urlstring = (string)$entry->link->attributes()->href;
          $urlstringparsed = parse_url($urlstring);
          $urlstringparsedpath = $urlstringparsed['path'];
          $urlstringparsedpatharray = explode('/', $urlstringparsedpath);
          $twitter_handle = $urlstringparsedpatharray[1];
          if ($urlstringparsedpatharray[2] == 'status') {
            $tweet_id = $urlstringparsedpatharray[3];
            if (!in_array($twitter_handle, $excludeIDs)) {
              $rt = $toa->post('statuses/retweet/' . $tweet_id);
              if (isset($list_info['slug'])) {
                $list_info['screen_name'] = $twitter_handle;
                $list = $toa->post('lists/members/create', $list_info);
              }
            }
          }
          else {
            print_r($toa->post('statuses/update', array('status' => $urlstring)));
          }
        }
      }    
    }
  }
  $handle = fopen($cached_file_path, 'w');
  fwrite($handle,$feedcontents);
  fclose($handle);  
}

retweet();
