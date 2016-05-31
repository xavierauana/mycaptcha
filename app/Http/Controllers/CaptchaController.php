<?php

namespace App\Http\Controllers;


use App\Captcha;
use App\Record;
use App\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CaptchaController extends Controller
{
    private $myCaptchaTokenHeader;

    private $headers;

    /**
     * CaptchaController constructor.
     * @param array $headers
     */
    public function __construct()
    {
        $this->myCaptchaTokenHeader = "ana-myCaptcha-token";
        $this->headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers'     => 'X-Requested-With, Content-Type, X-Auth-Token, Origin, Authorization,'.$this->myCaptchaTokenHeader
        ];
    }


    public function getRequestHeaders(Request $request)
    {
        $sites = Cache::remember('sites',1, function(){
            return Site::lists('domain', 'token')->toArray();
        });
        $headers = in_array(parse_url($request->header('Origin'), PHP_URL_HOST), array_values($sites))? $this->headers:[];
        return response(null, 200, $headers);
    }

    public function getCaptcha(Request $request)
    {
        $headers = [];
        $data = null;
        if($this->isAValidRequest($request)){
                $data = $this->createCaptchaResponseData();
                $headers = $this->headers;
        }

        return response($data, 200, $headers);
    }

    public function verifyCaptcha(Request $request)
    {
        $result = false;
        $headers = [];
        if($this->isAValidRequest($request)){
            if ($request->has("captchaId") and $request->get('answer')) {
                if ($record = Record::whereUuid($request->get('captchaId'))->whereStatus('new')->first()) {
                    $result = $record->captcha_string == $request->get('answer') ? true : false;
                    if ($result) {
                        $record->update(['status' => 'used']);
                    }
                }
            }
            $headers = $this->headers;
        }
        $data = [
            "result" => $result
        ];
        return response()->json($data, $headers);
    }

    /**
     * @return bool
     */
    private function isValidToken($request)
    {
        $sites = Cache::remember('sites',1, function(){
            return Site::lists('domain', 'token')->toArray();
        });
        $result = isset($sites[$request->header("ana-myCaptcha-token")]) and $sites[$request->header("ana-myCaptcha-token")] == parse_url($request->header('Origin'), PHP_URL_HOST);
        return $result;
    }

    /**
     * @return array
     */
    private function createCaptchaResponseData()
    {
        $record = (new Captcha())->createImage();
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
    private function isAValidRequest(Request $request)
    {
        return !$request->hasHeader('Origin') or ($request->hasHeader('ana-myCaptcha-token') and $this->isValidToken($request));
    }
}
