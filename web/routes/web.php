<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InternalLinkTreeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [InternalLinkTreeController::class, 'index']);

Route::post('/', [InternalLinkTreeController::class, 'createTree'])->name('search')->middleware('request_interval');

// Route::get('/{depth}/{url}', function ($depth, $url) {
//     return view('search');
// })->where('url', '.*');


