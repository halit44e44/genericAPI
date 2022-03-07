<?php

namespace App\Helpers;

use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class EpinFunctionsHelper
{

    public static function json($data)
    {
        header('Content-Type: application/json');
        echo $data;
        exit();
    }

    public static function getRequestResult($requestMethod = 'get', $requestUri = 'ping', $data = [], $formParams = [])
    {
        dd(1);
        return 1;
        try {
            $getClientsKeys = EpinFunctionsHelper::getAccessToken();
            $client = new Client();
            $response = $client->request(
                $requestMethod,
                env('EPINBASEURL') . $requestUri,
                [
                    'headers' => $getClientsKeys,
                    'query' => $data,
                    'form_params' => $formParams,
                    'http_errors' => false,
                    // 'body' => json_encode(['Action'=>'Return']),
                ],
            );
            if (!empty($response->getStatusCode()) && $response->getStatusCode() == 200) { // OK
                return json_decode($response->getBody()->getContents());
            }
        } catch (Exception $exception) {
            $data = ['error' => 512, 'message' => 'Exception getting result from Epin: ' . $exception->getMessage()];
            EpinFunctionsHelper::json(json_encode($data));
        }
    }

    public static function getAccessToken()
    {
        try {
            $data = [
                'Authorization' => 'testapi auth',
                'ApiName' => '1fbb882e15b89d43bc060e5327c0577e',
                'ApiKey' => '727bc65343a44872d7a3d99ba0709eeb',
            ];
            return $data;
        } catch (Exception $exception) {
            $data = ['error' => 510, 'message' => 'Exception getting access token: ' . $exception->getMessage()];
            EpinFunctionsHelper::json(json_encode($data));
        }
    }
}
