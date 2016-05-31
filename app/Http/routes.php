<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Captcha;
use App\Http\Controllers\CaptchaController;
use App\Record;
use App\Site;
use Illuminate\Http\Request;


$app->group(['prefix' => 'api'], function () use ($app) {

    $app->options('captcha', ['uses'=>CaptchaController::class."@getRequestHeaders"]);

    $app->get('captcha', CaptchaController::class."@getCaptcha");

    $app->post('captcha', function (Request $request) use ($app) {
        $result = false;
        if ($request->has("captchaId") and $request->get('answer')) {
            if ($record = Record::whereUuid($request->get('captchaId'))->whereStatus('new')->first()) {
                $result = $record->captcha_string == $request->get('answer') ? true : false;
                if ($result) {
                    $record->update(['status' => 'used']);
                }
            }
        }

        $data = [
            "result" => $result
        ];

        return response()->json($data);
    });

});

$app->get('/', function () use ($app) {
    return view('test');
});