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

use App\Http\Controllers\CaptchaController;


$app->group(['prefix' => 'api'], function () use ($app) {

    $app->options('captcha', ['uses' => CaptchaController::class . "@getRequestHeaders"]);

    //    $app->get('captcha', CaptchaController::class."@getCaptcha");
    $app->get('captcha', function () {
        dd("done");
    });

    $app->post('captcha', CaptchaController::class . "@verifyCaptcha");

    $app->post('captcha/verify', CaptchaController::class . "@verifyCaptchaToken");

});

$app->get('/', function () use ($app) {
    return view('welcome');
});