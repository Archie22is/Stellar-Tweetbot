<?php
$twitter_oauth_path = '/{YOURFILEPATH}/twitteroauth/twitteroauth.php';

// Insert absolute filepath where your cached feed file will go - can be the same directory as this script
$cached_file_path = '/{YOURFILEPATH}/Stellar-Tweetbot/feed.xml';

// Insert HTTP path to your Stellar.io XML/RSS file
$feed_url = "http://stellar.io/{YOURUSERNAME}/flow/feed";

// Insert your Twitter app credentials... you can create and get these at https://dev.twitter.com/apps
$consumer_key = '{GET_THIS_FROM_YOUR_TWITTER_ACCOUNT_SETTINGS}';
$consumer_secret = 'GET_THIS_FROM_YOUR_TWITTER_ACCOUNT_SETTINGS';
$access_token = 'GET_THIS_FROM_YOUR_TWITTER_ACCOUNT_SETTINGS';
$access_token_secret = 'GET_THIS_FROM_YOUR_TWITTER_ACCOUNT_SETTINGS';

$excludeIDs = array(''); // Insert an ID to exclude. This could be your Twitter handle, so as to not get autoretweeted by your own bot. Separate each ID with a comma.

$list_info = array(
		   'slug' => '', // list name here
		   'owner_screen_name', // the name of the bot that retweets things
		   );
       
$flickr_api_key = "{GET_THIS_FROM_FLICKR_API_DOCUMENTATION}";
$flickr_api_secret = "{GET_THIS_FROM_FLICKR_API_DOCUMENTATION}"