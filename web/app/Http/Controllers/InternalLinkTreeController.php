<?php

namespace App\Http\Controllers;

use App\Http\Requests\UrlRequest;
use Illuminate\Http\Request;
use App\Processor\WebCrawlerBot;

class InternalLinkTreeController extends Controller
{
    public function index(Request $request)
    {
        return view('index'); // ビューに内部リンクツリーを渡して表示する
    }

    public function createTree(UrlRequest $request)
    {
        $url = $request->url; // 起点となるURL
        $service = new WebCrawlerBot($url);

        $tree = $service->createInternalLinkTree($url);

        return view('index', compact('tree')); // ビューに内部リンクツリーを渡して表示する
    }
}
