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
      array_push($oldIDs, $entry->link->attributes()->href);
    }
  }
  return $oldIDs;
}

// This function looks for new tweets and retweets them out
function retweet() {

  global $consumer_key, $consumer_secret, $access_token, $access_token_secret, $feed_urls, $cached_file_path, $excludeIDs, $flickr_api_key, $flickr_api_secret;
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
          if (!in_array($twitter_handle, $excludeIDs) && !in_array($urlstring, $oldIDArray)) {
            if ($urlstringparsedpatharray[2] == 'status') {
              $tweet_id = $urlstringparsedpatharray[3];
                $rt = $toa->post('statuses/retweet/' . $tweet_id);
	              if (isset($list_info['slug'])) {
                   $list_info['screen_name'] = $twitter_handle;
                   $list = $toa->post('lists/members/create', $list_info);
                }
            }
            else {
              $url = parse_url($urlstring);
              $tweet = $urlstring;
              if ($url['host'] == 'flickr.com') {
                $path = explode('/', $url['path']);
                $flickr_user = $path[2];
                $flickr_photo = $path[3];
                require_once('phpFlickr/phpFlickr.php');
                $f = new phpFlickr($flickr_api_key, $flickr_api_secret, TRUE);
                $user = $f->people_getInfo($flickr_user);
                $flickr_photo_info = $f->photos_getInfo($flickr_photo);
                $title = wordwrap($flickr_photo_info['photo']['title'], 117);
                $urlstring = "http://www.flickr.com/photos/" . $user['path_alias'] . '/' . $flickr_photo . '/';
                $tweet = $title . ' ' . $urlstring;
              }
              print_r($toa->post('statuses/update', array('status' => $tweet)));
            }
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
