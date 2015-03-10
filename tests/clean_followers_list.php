<?php

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload

use j3j5\ControlMyFollowers;

$twitter_settings = array(
	// APP SETTINGS
	'consumer_key'		=> 'YOUR_CONSUMER_KEY',
	'consumer_secret'	=> 'YOUR_CONSUMER_SECRET',

	// USER SETTINGS
	'username'			=> 'YOUR_USERNAME',	// Without the '@'
	'token'				=> 'A_USER_TOKEN',	// The user who will be doing the cleaning
	'secret'			=> 'A_USER_TOKEN_SECRET',
);

if(!isset($twitter_settings['consumer_key']) OR empty($twitter_settings['consumer_key']) OR  $twitter_settings['consumer_key'] == "YOUR_CONSUMER_KEY") {
	echo "You must fill in your consumer key to run this script, open " . __FILE__ .  " and fill your data in." . PHP_EOL;
	exit;
}

if(!isset($twitter_settings['consumer_secret']) OR empty($twitter_settings['consumer_secret']) OR  $twitter_settings['consumer_secret'] == "YOUR_CONSUMER_SECRET") {
	echo "You must fill in your consumer secret to run this script, open " . __FILE__ .  " and fill your data in." . PHP_EOL;
	exit;
}

if(!isset($twitter_settings['username']) OR empty($twitter_settings['username']) OR  $twitter_settings['username'] == "YOUR_USERNAME") {
	echo "You must fill in your username to run this script, open " . __FILE__ .  " and fill your data in." . PHP_EOL;
	exit;
}

if(!isset($twitter_settings['token']) OR empty($twitter_settings['token']) OR  $twitter_settings['token'] == "A_USER_TOKEN") {
	echo "You must fill in your user's token to run this script, open " . __FILE__ .  " and fill your data in." . PHP_EOL;
	exit;
}

if(!isset($twitter_settings['secret']) OR empty($twitter_settings['secret']) OR  $twitter_settings['secret'] == "A_USER_TOKEN_SECRET") {
	echo "You must fill in your users's token secret to run this script, open " . __FILE__ .  " and fill your data in." . PHP_EOL;
	exit;
}

$safe_mode = FALSE;
if(isset($argv[1]) && $argv[1] == 'safe') {
	$safe_mode = TRUE;
}

$parser = new ControlMyFollowers($twitter_settings);

$followers = $parser->get_all_my_followers();
echo "@{$twitter_settings['username']}'s followers are: " . print_r($followers, TRUE) . PHP_EOL;

$friends = $parser->get_all_my_friends();
echo "@{$twitter_settings['username']}'s friends are: " . print_r($friends, TRUE) . PHP_EOL;

$unwanted = $parser->get_unwanted_followers();

if(empty($unwanted)) {
	echo "You already have a clean follower list. Nothing to do here." . PHP_EOL;
	exit;
}

echo PHP_EOL;
echo "@{$twitter_settings['username']}'s followers who will stop following you are: " . print_r($unwanted, TRUE) . PHP_EOL;

echo "The process may take a while depending on your amount of followers.".PHP_EOL."Are you sure you want to continue?[Y/n] ";

prompt_answer();

echo "Alright... Let's kick some asses out of your follower list." . PHP_EOL;
foreach($unwanted AS $user_id) {
	$now = 0;
	$has_slept = FALSE;
	if($safe_mode) {
		$user = $parser->get_user_info($user_id);
		if(!isset($user['errors'])) {
			echo "Do you want \"{$user['name']}\" (@{$user['screen_name']}) to stop following you?[Y/n]";
			$delete = prompt_answer(FALSE);
			if(!$delete) {
				continue;
			}
		}
	}

	$response = $parser->make_user_unfollow_you($user_id);
	// Check for rate limits
	if((isset($response['block']['tts']) && $response['block']['tts'] > 0)) {
		echo "Sleeping..." . PHP_EOL;
		sleep($response['block']['tts'] + 1);
		$has_slept = TRUE;
	}

	if(isset($response['unblock']['tts']) && $response['unblock']['tts'] > 0) {
		if($has_slept && ($response['block']['tts'] < $response['unblock']['tts'])) {
			echo "Sleeping (un)..." . PHP_EOL;
			sleep(($response['unblock']['tts'] - $response['block']['tts'])  + 1);
		}
	}

	if(isset($response['block']['errors'])) {
		echo "Ouch! Some error occured:" . PHP_EOL;
		var_dump($response['block']);
		exit;
	}
	if(isset($response['unblock']['errors'])) {
		echo "Ouch! Some error occured:" . PHP_EOL;
		var_dump($response['unblock']);
		exit;
	}

	if(isset($response['username'])){
		echo "The user @{$response['username']} don't follow you anymore." . PHP_EOL;
	}
}

echo "Done, you should have now a pretty clean and neat list of followers." . PHP_EOL;

function prompt_answer($exit = TRUE) {
	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	if(trim($line) !== 'y' && !empty(trim($line))) {
		fclose($handle);
		if($exit) {
			echo "ABORTING!" . PHP_EOL;
			exit;
		} else {
			return FALSE;
		}
	}
	if(!$exit) {
		return TRUE;
	}
}
