<?php
require 'lib/Slim/View.php';
include_once "lib/simpleCache.php" ;
require "lib/parsedown.php" ;

class Template extends \Slim\View
{

  private function headers($site){
    echo '<title>' . $site['title'] . '</title>' ;
    echo '<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >' ;
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' ;
    echo '<head><link href="assets/css/dbx.css" rel="stylesheet" type="text/css"></head>' ;
  }

  public function render($template)
  {
    $cache = new SimpleCache() ;
    $CACHE_PATH = 'cache/' ;
    $data = array() ;
    $cache->cache_path = $CACHE_PATH ;
    $site ;

    if($data = $cache->get_cache($this->data['name'])){
       $site = json_decode($data, true) ;
    }
    else{
       $data = $cache->get_cache("404") ;
       $site = json_decode($data, true) ;
    }

    $this->headers($site) ;
    
    echo  '<body><div class="title">' . $site['title'] . '</div>' ;

    $Parsedown = new Parsedown();
    echo ($Parsedown->text($site['body']));

    echo '</body>' ;

  }
}

?>
