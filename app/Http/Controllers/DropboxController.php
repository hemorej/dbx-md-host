<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Dropbox;
use Parsedown;

class DropboxController extends Controller
{
    public function challengeHandler(Request $request)
    {
    	return $request->get('challenge');
    }

    public function webhookHandler(Request $request)
    {
    	$handshake = Dropbox::verifySignature($request->header('X-Dropbox-Signature'), $request->getContent());

    	if($handshake == false)
    		return;

    	$dbx = new Dropbox();
    	$body = $dbx->post('files/list_folder', ['path' => '']);

    	foreach($body['entries'] as $file)
    	{
    		if(empty(\Cache::get($file['id'])) || Cache::get($file['id']) != $file['content_hash'])
    		{
    			$parser = new Parsedown();
				\Cache::put($file['id'], $file['content_hash']);
				\Cache::put(pathinfo($file['name'])['filename'], $parser->text($dbx->downloadFile($file['name'])));
    		}
    	}
    }
}
