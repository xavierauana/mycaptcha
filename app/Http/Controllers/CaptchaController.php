<?php

namespace App\Http\Controllers;


use App\Captcha;
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
        if(!$request->hasHeader('Origin') or ($request->hasHeader('ana-myCaptcha-token') and $this->isValidToken($request))){
                $data = $this->createCaptchaResponseData();
                $headers = $this->headers;
        }

        return response($data, 200, $headers);
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
}
