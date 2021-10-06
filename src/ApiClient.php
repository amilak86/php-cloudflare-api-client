<?php
namespace Ak86\CF;

use GuzzleHttp\Client as GuzzleHttpClient;

class ApiClient
{
	/** @var GuzzleHttpClient holds a guzzle http client instance */
	private static $guzzle;

	/**
	 * Instantiates a guzzle http client object
	 * 
	 * @param string $token Cloudflare Auth Token
	 */
	public function __construct($token)
	{
		// instantiate and store a new guzzle http client
		self::$guzzle = new GuzzleHttpClient([
			'base_uri' => 'https://api.cloudflare.com/client/v4',
			'headers' => [
					'Content-Type' => 'application/json',
					'Authorization: Bearer '. $token
			]

		]);
	}

	/**
	 * Dispatches an API request to clear the cache of the site
	 * 
	 * @param  string $domain the domain name to clear the cache
	 * @return mixed          True on success. An exception object otherwise
	 */
	public function clearCache($domain)
	{
		// get the dns zone id of the $domain
		$zoneid = self::getZoneId($domain);

		// send the api request to clear the domain cache
		$req = self::$guzzle->request('POST', '/zones/'.$zoneid.'/purge_cache', ['json' => ['purge_everything' => true]]);

		// convert the json response to an object
		$res = json_decode($req->getBody());

		if($res->success)
		{
			return true;
		}
		else
		{
			throw new \Exception('Failed to clear the cache on '. $domain);
		}
	}

	/**
	 * Dispatches an API request to switch the site to development mode
	 * 
	 * @param  string $domain the domain name to switch to the development mode
	 * @return mixed 		  on success an array indicating the current status and the time remaining in seconds. An exception object otherwise
	 */
	public function enableDevMode($domain)
	{
		// get the dns zone id of the $domain
		$zone = self::getZoneId($domain);

		// send an api request to get the current dev. mode status of the domain
		$req = self::$guzzle->request('GET', '/zones/'.$zone.'/settings/development_mode');

		// convert the json response to an object
		$res = json_decode($req->getBody());

		if($res->success)
		{
			// this indicates the dev.mode status
			$r = $res->result;

			if($r->value == 'off')
			{
				// status is off. lets set this site to dev. mode
				$devreq = self::$guzzle->request('PATCH', '/zones/'.$zone.'/settings/development_mode', ['json' => ['value' => 'on']]);
				
				// convert the json resp. to an object
				$devreqres = json_decode($devreq->getBody());

				if($devreqres->success)
				{
					// the site is successfully switched to dev. mode
					return array('status' => true, 'set' => 'now', 'time_remaining' => $devreqres->result->time_remaining);
				}
				else
				{
					// error.
					throw new \Exception('Failed setting dev mode!');
				}
			}
			else
			{
				// status not == off. should be already on. return an array of data
				return array('status' => true, 'set' => 'before', 'time_remaining' => $r->time_remaining);
			}

		}
		else
		{
			// success = 0. something wrong
			throw new \Exception('Failed setting '.$domain. ' to dev. mode!');
		}
	}

	/**
	 * Dispatches an API request to get the domain zone info and returns the zone id
	 * 
	 * @param  string $domain the domain name to query the zone info
	 * @return string      	  The zone ID of the domain
	 */
	private static function getZoneId($domain)
	{
		// send an api request to get the domain zone details
		$response = self::$guzzle->request('GET', '/zones?name='.$domain);

		// get the json response
		$responseBody = $response->getBody();

		// convert the json response to an object
		$stdClassResObj = json_decode($responseBody);

		// extract zone info object for the root domain
		$domainInfoObj = $stdClassResObj->result[0];	

		return $domainInfoObj->id;
	}

}
