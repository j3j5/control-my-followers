Control My Followers
============

Tool for Twitter that makes everybody who you don't follow, unfollow you (in case they were already your followers)


## Installation

Add `j3j5/control_my_followers` to `composer.json`.
```
"j3j5/twitterapio": "dev-master"
```

Run `composer update` to pull down the latest version of Twitter.

## Use

You will need a Twitter APP in order to use this library, you must go to [Twitter apps](https://apps.twitter.com) and login there, create an app with *Read and Write permissions* and then copy your settings into the settings array in `tests/clean_followers_list.php`

You should see an array like this on top of the file, copy paste your details from *Keys and Access Tokens* tab once your app is created at [Twitter apps](https://apps.twitter.com).
```php
$twitter_settings = array(
	'consumer_key'		=> 'YOUR_CONSUMER_KEY',
	'consumer_secret'	=> 'YOUR_CONSUMER_SECRET',
	'username'		=> 'YOUR_CONSUMER_KEY',
	'token'				=> 'A_USER_TOKEN',
	'secret'			=> 'A_USER_TOKEN_SECRET',
);
```
In order to run the script, just go to wherever you've cloned the project and run: (the safe is optional, just for *safe mode*, if you decide to use it goes WITHOUT the brackets)

```bash
$ php tests/clean_followers_list.php [safe]
```

That's it!
