<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use Log;

class Dropbox extends Model
{
    private $client;
    private $headers;

    function __construct()
    {
    	$this->headers = [
		    'Authorization' => 'Bearer ' . env('DBX_ACCESS_TOKEN'),
		    'Accept'        => 'application/json',
		];

		$this->client = new Client(['base_uri' => env('DBX_API_ENDPOINT')]);
		$this->contentClient = new Client(['base_uri' => env('DBX_CONTENT_ENDPOINT')]);
    }

    public function getClient()
    {
    	return $this->client;
    }

    public function post($url, $json)
    {
    	if(empty($url))
    		throw new \Exception('Please specify endpoint url');

    	$args = ['headers' => $this->headers];
    	if(!empty($json))
    		$args['json'] = $json;

    	try{
    		$response = $this->client->request('POST', $url, $args);
    		return json_decode($response->getBody()->getContents(), true);
    	}catch(\Exception $e){
    		Log::error('Failed to post request to API', [$e->getMessage()]);
			throw $e;
    	}
    }

    public function downloadFile($name)
    {
    	if(empty($name))
    		throw new \Exception('Please specify filename to download');

    	try{
    		$url = "files/download";
    		$args = ['headers' => $this->headers];
    		$args['headers']['Dropbox-API-Arg'] = json_encode(['path' => "/$name"]);

    		$response = $this->contentClient->request('GET', $url, $args);
    		return $response->getBody()->getContents();
    	}catch(\Exception $e){
    		Log::error('Failed to download file', [$e->getMessage()]);
			throw $e;
    	}

    }

    public static function verifySignature($headers, $body)
    {	  
	  if(empty($headers)){
	  	Log::info('Received webhook missing required headers');
	    return false;
	  }

	  $hmac = hash_hmac('sha256', $body, env('DBX_APP_SECRET'));

	  if(md5($headers) == md5($hmac))
	    return true ;

	  return false;
	}
}
