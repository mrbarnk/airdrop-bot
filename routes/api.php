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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::match(['get', 'post'], '/botman', '\App\Http\Controllers\TelegramController@handle');
Route::get('/all_requests', function () {
    return  \App\Models\TelegramRequests::orderBy('id', 'desc')->get();
    // preg_match('/^;-) Done ?$/m...', ';-) Done', $matched);
    // return json_encode($matched);
});
Route::get('/all_users', function () {
    return  \App\Models\Chats::orderBy('id', 'desc')->get();
    // preg_match('/^;-) Done ?$/m...', ';-) Done', $matched);
    // return json_encode($matched);
});
Route::get('/debug', function () {
    $re = '/(\/start r[0-9]+)/m';
    $str = '/start r233543';

    preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

    // Print the entire match result
    $arrays = explode(' ', $matches[1][0]);
    $arrayMsg = end($arrays);
    var_dump($arrayMsg);

});