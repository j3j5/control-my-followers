<?php

/**
 *
 * @author Julio Foulquié
 * @version 0.1.0
 *
 *
 */

namespace j3j5;

use \Pleo\BloomFilter\BloomFilter;

class ControlMyFollowers {

	private $bloom;
	private $api;
	private $username;

	private $followers;
	private $following;

	public function __construct($settings) {
		if(!isset($settings['username'])) {
			TwitterApio::debug("You must set a username on the settings!!");
			exit;
		}
		// generate a bloom filter for 1000 elements with a probability of 1% for false positives

		$this->username = $settings['username'];
		unset($settings['username']);
		$this->api = new TwitterApio($settings);
		$this->followers = array();
		$this->following = array();
	}

	public function get_all_my_followers() {

		if(!empty($this->followers)) {
			return $this->followers;
		}

		foreach($this->api->get_followers(array('screen_name' => $this->username, 'count' => 5000)) AS $page) {
			if(!empty($page) && is_array($page)) {
				$this->followers = array_merge($this->followers, $page);
			}
		}
		return $this->followers;
	}

	public function get_all_my_friends() {

		if(!empty($this->following)) {
			return $this->following;
		}

		foreach($this->api->get_friends(array('screen_name' => $this->username, 'count' => 5000)) AS $page) {
			if(!empty($page) && is_array($page)) {
				$this->following = array_merge($this->following, $page);
			}
		}
		return $this->following;
	}

	public function block($user_to_block) {
		$resp = $this->api->block($user_to_block);
		if(isset($resp['username'])) {
			TwitterApio::debug("Blocking @{$resp['username']}");
		}
		return $resp;
	}

	public function unblock($user_to_block) {
		return $this->api->unblock($user_to_block);
	}

	public function get_unwanted_followers() {
		if(empty($this->following)) {
			TwitterApio::debug("You are not following anybody so will unblock all your followers");
		} else {
			// Build the BloomFilter
			$this->bloom = BloomFilter::create(count($this->following), 0.0001);
			foreach($this->following AS $id) {
				if(is_numeric($id)) {
					TwitterApio::debug("Adding user $id.");
					$this->bloom->add($id);
				} else {
					var_dump($id); exit;
				}
			}
		}
		$friends = $notfriends = 0;
		$unwanted = array();
		foreach($this->followers AS $id) {
			// This follower is not on your friends list
			if(!$this->bloom->exists($id)) {
				TwitterApio::debug("User $id is NOT your friend, to Siberia!");
				$unwanted[] = $id;
				$notfriends++;
			} else {
				TwitterApio::debug("User $id is your friend.");
				$friends++;
			}
		}
		TwitterApio::debug("From a total of " . count($this->followers));
		TwitterApio::debug("you had $friends who were your friends (you follow them back).");
		TwitterApio::debug("and $notfriends who were not. Those have stop following you now.");
		return $unwanted;
	}

	public function make_user_unfollow_you($id) {
		TwitterApio::debug("Blocking and sleeping 2");
		$result_block = $this->block($id);
		sleep(2);
		TwitterApio::debug("Unblocking");
		$result_unblock = $this->unblock($id);

		return array('block'=>$result_block, 'unblock'=>$result_unblock);
	}

}
