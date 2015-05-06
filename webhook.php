<?php

require_once "lib/Dropbox/autoload.php" ;
include_once "lib/simpleCache.php" ;
require 'template.php';
require 'lib/Slim/Slim.php';
use \Dropbox as dbx;


\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim(array(
    'view' => new Template()
));

$str = file_get_contents("./app.json");
$jsonArr = json_decode($str, TRUE);

define("DBX_PATH", "/host") ; 
define("CACHE_PATH", "cache/") ;
define("HASH_PATH", "cache/hash") ;
define("APP_NAME", $jsonArr["name"]) ;
define("DBX_KEY", $jsonArr["accessKey"]) ; 
define("LOG_PATH", "log/") ;

// GET route
$app->get(
    '/challenge',
    function() use ($app){
        logRequest($app->request) ;
        answerChallenge($app) ;
    }
);

$app->get('/', function() use ($app){
    $app->render('template.php', array('name' => "about"));
});

$app->get('/:name+', function ($name) use ($app) {
    $app->render('template.php', array('name' => $name[0]));
});

$app->post('/delta', function() use ($app){
  delta() ;
});

// POST route
$app->post(
    '/',
    function () use ($app) {
        logNotification($app->request) ;
        if( verifySignature($app) == true ) {
          delta() ;
	  // fetchFile() ;
        }
    }
);

$app->run();


function delta(){

  $dbxClient = new dbx\Client(DBX_KEY, APP_NAME);
  $hash = @file_get_contents(HASH_PATH) ;
  $meta = $new_hash = '' ;

  if($hash == ''){
    $meta = $dbxClient->getMetadataWithChildren(DBX_PATH) ;
    $new_hash = $meta[hash] ;
    $f = file_put_contents(HASH_PATH, $new_hash);
  }
  else{
    $meta = $dbxClient->getMetadataWithChildrenIfChanged(DBX_PATH, $hash);
  }

  if(isset($meta[1])){
    $new_hash = $meta[1][hash] ;
    $f = file_put_contents(HASH_PATH, $new_hash) ;

    foreach($meta[1][contents] as $each){
      fetchFile($each[path]) ;
    }
  }

}


function fetchFile($remoteFilename = '/host/site.txt'){

  $dbxClient = new dbx\Client(DBX_KEY, APP_NAME);
  $remoteFilenameBase = basename($remoteFilename) ;

  $cache = new SimpleCache() ;
  $cache->cache_path = CACHE_PATH ;

  $f = fopen($remoteFilenameBase, "w+b");
  $file = $dbxClient->getFile($remoteFilename, $f);
  fclose($f);
  $matches = $siteContent = array() ;
  $fileContents = file_get_contents($remoteFilenameBase) ;
  preg_match("/title:\s*([^----]+)\s*----\s*(.+)/is", $fileContents, $matches) ;
  $siteContent["title"] = isset($matches[1]) ? $matches[1] : 'Title' ;
  $siteContent["body"]  = $matches[2] ;

  $info = pathinfo($remoteFilenameBase);
  $cacheHandle = basename($remoteFilenameBase,'.'.$info['extension']);

  $cache->set_cache($cacheHandle, json_encode($siteContent)) ;
  unlink($remoteFilenameBase) ;
}


function answerChallenge($app){
  
  $ua = $app->request->getUserAgent() ;

  $app->response->setBody($app->request->get('challenge'));
}

function verifySignature($app){
  $request = $app->request ;
  $signature = $request->headers->get('X-Dropbox-Signature') ;
  
  if(empty($signature)){
    $app->response->setStatus(403) ;
    return false;
  }

  $appInfo = dbx\AppInfo::loadFromJsonFile("./app.json");
  $hmac = hash_hmac('sha256', $request->getBody(), $appInfo->getSecret()) ;

  if(dbx\Security::stringEquals($signature, $hmac)){
    return true ;
  }

  $app->response->setStatus(400) ;
  return false;
}

function writeFile($filename, $content){
  file_put_contents($filename . time() . ".log", $content) ;
}

function logRequest($request){
  $headers = $request->headers;

  writeFile(LOG_PATH . "request", serialize($headers)) ;
}

function logNotification($request){
  $body = $request->getBody();
  
  writeFile(LOG_PATH . "notification", serialize($body)) ;
}

?>
