# Cloudflare API Client - PHP

A PHP based API client library for Cloudflare which lets you execute common cloudflare based operations such as clearing a site cache and switching a site to development mode etc., directly through a PHP script without having to log into the cloudflare dashboard.

## Requirements

- PHP 7.2.5 or higher
- Composer package manager
- An HTTPS enabled web server. All the requests to Cloudflare API must be sent over HTTPS. 
- A Cloudflare API token. You can obtain the API token from your user profile's API Tokens page located at https://dash.cloudflare.com/profile/api-tokens

## Installation

Open your terminal. Switch to your project's root directory and run below command to install the package:
```
composer require ak86/php-cloudflare-api-client
``` 

## Basic Usage

In your script:
```
// require composer autoloader
require_once 'vendor/autoload.php';

// import the library
use Ak86\CF\ApiClient as CloudflareApiClient;

try {
	// Instantiate CloudflareApiClient by passing your cloudflare authentication token 
	$cfClient = new CloudflareApiClient('your_cloudflare_auth_token');

	// To clear the cache (i.e. abc.com)
	$res1 = $cfClient->clearCache('abc.com');

	if($res1)
	{
		echo 'Successfully cleared the cache of abc.com';
	}

	// To set a site to development mode (i.e. xyz.com)
	$res2 = $cfClient->enableDevMode('xyz.com');

	switch($res2['set'])
	{
		case 'now':
			echo 'Successfully switched xyz.com to the dev. mode. It will expire in another '. ($res2['time_remaining'] / 60) .' minutes.';
		break;

		case 'before':
			echo 'xyz.com is already switched to the development mode. It will expire in another '. ($res2['time_remaining'] / 60) .' minutes.';
		break;
	}
}
catch (Exception $e){
	// catch and handle exceptions here
	echo $e->getMessage();
	exit;
}

```
## License

[MIT](./LICENSE)

## Author

[Amila Kalansooriya](https://www.linkedin.com/in/amilakalansooriya/)