<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/crawl/{depth}/{url}', function ($depth, $url) {
    return json_encode(App\Processor\WebCrawler::crawl(urldecode($url), urldecode($depth)));
})->where('url', '.*');
