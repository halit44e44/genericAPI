<?php

namespace App\Helpers;

use App\Models\Genba\GenbaToken;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class GenbaFunctionsHelper
{

    public static function json($data)
    {
        header('Content-Type: application/json');
        echo $data;
       // exit();
    }

    public static function getRequestResult($requestMethod = 'get', $requestUri = 'ping', $data = [], $formParams = [])
    {
        try {
            $token = GenbaToken::latest()->first();
            if (!$token) {
                $genbaAccessToken = GenbaFunctionsHelper::getAccessToken();
                $token = new GenbaToken;
                $token->token = $genbaAccessToken;
                $token->save();
            }
            $genbaAccessToken = $token->token;

            $client = new Client();
            $response = $client->request(
                $requestMethod,
                env('GENBABASEURL') . $requestUri,
                [
                    'headers' =>
                    [
                        'Authorization' => "Bearer {$genbaAccessToken}",
                        // 'Content-Type' => 'application/json',
                    ],
                    'query' => $data,
                    'form_params' => $formParams,
                    'http_errors' => false,
                    // 'body' => json_encode(['Action'=>'Return']),
                ],
            );
            if (!empty($response->getStatusCode()) && $response->getStatusCode() == 200) { // OK
                return $response->getBody()->getContents();
            }

            // 400 vs errors..
            if(json_decode($response->getBody()->getContents())->Code==1001)
            {

                $genbaAccessToken = GenbaFunctionsHelper::getAccessToken();
                $token = new GenbaToken;
                $token->token = $genbaAccessToken;
                $token->save();
                GenbaFunctionsHelper::getRequestResult($requestMethod, $requestUri, $data, $formParams);
            }
            //dd($response);
            $data = ['error' => 511, 'message' => 'Error getting result from genba', 'genbaOutput' => json_decode($response->getBody()->getContents())];
            GenbaFunctionsHelper::json(json_encode($data));
        } catch (Exception $exception) {
            //dd($exception->getMessage());
            $data = ['error' => 512, 'message' => 'Exception getting result from genba: ' . $exception->getMessage()];
            GenbaFunctionsHelper::json(json_encode($data));
        }
    }

    public static function getAccessToken()
    {
        try {
            $certificateResult = GenbaFunctionsHelper::readCertificate();
            if (isset($certificateResult['error']) && $certificateResult['error'] == 507) {
                $data = ['error' => 507, 'message' => 'Error reading certificate'];
                GenbaFunctionsHelper::json(json_encode($data));
            }
            $jwt = GenbaFunctionsHelper::createJwt($certificateResult);
            if (isset($jwt['error']) && $jwt['error'] == 508) {
                $data = ['error' => 508, 'message' => 'Error creating JWT'];
                GenbaFunctionsHelper::json(json_encode($data));
            }
            $accessToken = GenbaFunctionsHelper::getToken($jwt);
            if (isset($accessToken['error']) && $accessToken['error'] == 509) {
                $data = ['error' => 509, 'message' => 'Error getting access token'];
                GenbaFunctionsHelper::json(json_encode($data));
            }
            $sessionToken = new GenbaToken;
            $sessionToken->token = $accessToken;
            $sessionToken->save;
            return $accessToken;
        } catch (Exception $exception) {
            $data = ['error' => 510, 'message' => 'Exception getting access token: ' . $exception->getMessage()];
            GenbaFunctionsHelper::json(json_encode($data));
        }
    }
    static function readCertificate()
    {
        try {
            $certLocation = env('CERTIFICATION_PATH');
            $privetKeyLocation = env('CERTIFICATION_KEY_PATH');
            $certificationPassword = env('CERTIFICATION_PASSWORD');
            $data = file_get_contents($certLocation);
            $pKeyData = file_get_contents($privetKeyLocation);
            $pkData = openssl_get_privatekey($pKeyData, $certificationPassword);
            $temp = openssl_x509_parse($data);
            $thumbprintRaw = openssl_x509_fingerprint($data, 'sha1', true);
            $cert = $temp;
            $validFrom = $temp['validFrom_time_t'];
            $validTo = $temp['validTo_time_t'];
            $thumbprint = base64_encode($thumbprintRaw);

            $result = [
                'validFrom' => $validFrom,
                'validTo' => $validTo,
                'thumbprint' => $thumbprint,
                'pkey' => $pkData,
            ];
            return $result;
        } catch (\Exception $exception) {
            $data = ['error' => 507, 'message' => 'Exception reading certificate: ' . $exception->getMessage()];
            GenbaFunctionsHelper::json(json_encode($data));
        }
    }
    static function createJwt($certificateResult)
    {
        try {
            $audience = env('AUDIENCE');
            $identifierUrl = env('IDENTIFIREURL') . env('CUSTOMER_ACCOUNT_NUMBER');
            $header = array(
                "alg" => env('JWT_HASH'),
                "typ" => "JWT",
                "x5t" => $certificateResult['thumbprint']
            );
            $body = array(
                "aud" => $audience,
                "exp" => $certificateResult['validTo'],
                "iss" => $identifierUrl,
                "jti" => GenbaFunctionsHelper::newGuid(),
                "nbf" => $certificateResult['validFrom'],
                "sub" => $identifierUrl
            );
            $jwt = JWT::encode($body, $certificateResult['pkey'], env('JWT_HASH'), null, $header);
            return $jwt;
        } catch (\Exception $exception) {
            $data = ['error' => 508, 'message' => 'Exception creation JWT: ' . $exception->getMessage()];
            GenbaFunctionsHelper::json(json_encode($data));
        }
    }
    static function newGuid()
    {
        try {
            if (function_exists('com_create_guid') === true) {
                return trim(com_create_guid(), '{}');
            }

            return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }
    static function getToken($jwt)
    {
        try {
            $identifierUrl = env('IDENTIFIREURL') . env('CUSTOMER_ACCOUNT_NUMBER');
            $resourceID = env('RESOURCEID');
            $audience = env('AUDIENCE');
            $postdata = "resource=" . urlencode($resourceID);
            $postdata .= "&client_id=" . urlencode($identifierUrl);
            $postdata .= '&client_assertion_type=urn%3Aietf%3Aparams%3Aoauth%3Aclient-assertion-type%3Ajwt-bearer';
            $postdata .= "&grant_type=client_credentials";
            $postdata .= "&client_assertion=" . urlencode($jwt);

            $tokenRequest = curl_init();

            curl_setopt($tokenRequest, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($tokenRequest, CURLOPT_URL, $audience);
            curl_setopt($tokenRequest, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($tokenRequest, CURLOPT_SSL_VERIFYPEER, 0);
            $tokenResponse = curl_exec($tokenRequest);
            curl_close($tokenRequest);
            $data = json_decode($tokenResponse);
            $accessToken = $data->access_token;
            return $accessToken;
        } catch (Exception $exception) {
            $data = ['error' => 509, 'message' => 'Exception getting access token: ' . $exception->getMessage()];
            GenbaFunctionsHelper::json(json_encode($data));
        }
    }
}
