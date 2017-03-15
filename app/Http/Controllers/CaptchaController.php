<?php

namespace App\Http\Controllers;


use App\Captcha;
use App\Record;
use App\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;

class CaptchaController extends Controller
{
    private $myCaptchaTokenHeader;

    private $headers;

    /**v
     * CaptchaController constructor.
     * @param array $headers
     */
    public function __construct() {
        $this->myCaptchaTokenHeader = "ana-myCaptcha-token";
        $this->headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers'     => 'X-Requested-With, Content-Type, X-Auth-Token, Origin, Authorization,' . $this->myCaptchaTokenHeader
        ];
    }


    public function getRequestHeaders(Request $request) {
        $sites = Cache::remember('sites', 1, function () {
            return Site::lists('domain', 'token')->toArray();
        });
        $headers = in_array(parse_url($request->header('Origin'), PHP_URL_HOST),
            array_values($sites)) ? $this->headers : [];

        return response(null, 200, $headers);
    }

    public function getCaptcha(Request $request) {
        $headers = [];
        $data = null;
        if ($this->isAValidRequest($request)) {
            $data = $this->createCaptchaResponseData($request);
            $headers = $this->headers;
        }

        return response($data, 200, $headers);
    }

    public function verifyCaptcha(Request $request) {
        $result = false;
        $headers = [];
        $data = [];

        $temp = $request->all();

        foreach ($temp as $key => $value) {
            Log::info("key is: {$key}");
            Log::info("value is: {$value}");
        }

        if ($this->isAValidRequest($request)) {
            Log::info("is valid request");
            if ($request->has("captchaId") and $request->get('answer')) {
                if ($record = Record::whereUuid($request->get('captchaId'))->whereStatus('new')->first()) {
                    $result = $record->captcha_string == $request->get('answer') ? true : false;
                    if ($result) {
                        $record->verification_token = str_random(36);
                        $record->update(['status' => 'used']);
                        $data["verificationToken"] = $record->verification_token;
                    }
                }
            }
            $headers = $this->headers;
        }
        $data["result"] = $result;

        return response()->json($data, 200, $headers);
    }

    public function verifyCaptchaToken(Request $request) {
        if ($request->has("verificationToken")) {
            return response()->json(['success' => true], 200, $this->headers);
        }

        return response()->json(['fail' => true], 200, $this->headers);
    }

    /**
     * @return bool
     */
    private function isValidToken($request) {
        $sites = Cache::remember('sites', 1, function () {
            return Site::lists('domain', 'token')->toArray();
        });
        $result = isset($sites[$request->header($this->myCaptchaTokenHeader)]) and $sites[$request->header($this->myCaptchaTokenHeader)] == parse_url($request->header('Origin'),
            PHP_URL_HOST);

        return $result;
    }

    /**
     * @return array
     */
    private function createCaptchaResponseData(Request $request) {
        $record = (new Captcha())->createImage($request->all());
        $data = [
            'imageUrl'  => $record->imageUrl,
            'captchaId' => $record->uuid
        ];

        return $data;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function isAValidRequest(Request $request) {
        return !$request->hasHeader('Origin') or ($request->hasHeader($this->myCaptchaTokenHeader) and $this->isValidToken($request));
    }
}
