<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IpAddressController extends Controller
{
    private function curlRequest($url, $fields, $method = 'POST',$headers = [])
    {
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $response = $client->request($method, $url, [
            'form_params' => $fields,
            'headers' => $headers,
        ]);

        $result = $response->getBody()->getContents();
        return json_decode($result);
    }


    public function index(){
       $fields = [
            'domain' => 'emassmart.com',
            'version' => '121',
            'item_id' => '38944711',
            'url' => 'https://emassmart.com/install/process',
            'purchase_code' =>'helloworld'
        ];

        $curl_response = $this->curlRequest("https://desk.spagreen.net/verify-installation-v2", $fields);
        return response()->json(compact('curl_response'));

    }

    public function save(){
        $ip= IpAddress::updateOrCreate(
            
        );
    }
}
