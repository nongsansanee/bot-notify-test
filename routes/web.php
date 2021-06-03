<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LINEWebhookController;

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

Route::get('/', function () {
    return view('welcome');
});

// webhooks
Route::post('/webhooks/line', LINEWebhookController::class);

Route::post('noti',function(){
 //return "test noti";
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(env('line_token'));
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => env('line_secret')]);

    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
    $response = $bot->pushMessage(env('LINE_ID_TEST'), $textMessageBuilder);
    return $response->getHTTPStatus() . ' ' . $response->getRawBody();
    //echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
});
