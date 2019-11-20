<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContentController extends Controller
{
	public function serveDefault(Request $request)
	{
		return $this->_serve();
	}

	public function serve(Request $request, $file)
	{
		return $this->_serve($file);
	}

	private function _serve($file = 'about')
	{
		$content = \Cache::get($file);
		if(empty($content))
			$content = \Cache::get('404');

		$dom = new \DOMDocument;
		$dom->loadHTML($content);
		$el = $dom->getElementsByTagName('h2');
		$title = $el[0]->nodeValue;

		return view('page')->with(['content' => $content, 'title' => $title]);
	}
}
