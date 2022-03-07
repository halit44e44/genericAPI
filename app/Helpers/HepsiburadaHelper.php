<?php

namespace App\Helpers;

use Exception;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;

class HepsiburadaHelper
{
    static function getRequestResult($requestMethod = 'get', $requestUri = 'ping', $data = [], $formParams = [], $filePath = null, $external = false)
    {
        $client = new Client();
        $requestInfo = [
            'auth'    => [
                env('HEPSIBURADA_USERNAME'),
                env('HEPSIBURADA_PASSWORD'),
            ],
            'headers' =>
            [
                //'Content-Type' => 'application/json',
            ],
            'query' => $data,
            'http_errors' => false,
            // 'body' => json_encode(['Action'=>'Return']),
        ];
        if ($filePath) {
            $requestInfo += [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => file_get_contents($filePath),
                        'filename' => 'productFile.json'
                    ],

                ]
            ];
            
        } else {
            $requestInfo += ['form_params' => $formParams,];
        }
        if (!$external) {
            
            $response = $client->request(
                $requestMethod,
                env('HEPSIBURADA_BASEURL') . $requestUri,
                $requestInfo
            );
        } else {
            //dd('out');
            $response = $client->request(
                $requestMethod,
                env('HEPSIBURADA_BASEURL_external') . $requestUri,
                $requestInfo
            );
        }


        return $response;
    }
    static function jsonFileGenerate()
    {
        $data = json_encode(['Text One', 'Text Two', 'Text Three']);

        $jsongFile = time() . '_file.json';

        try {
            File::put(public_path('upload/json/' . $jsongFile), $data);
            $filePath = public_path('upload/json/' . $jsongFile);
        } catch (Exception $exception) {
            $filePath = '';
        }
        return $filePath;
    }
}
