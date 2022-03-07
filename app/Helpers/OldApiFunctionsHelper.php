<?php

namespace App\Helpers;

use App\Models\ClientsInfo;
use App\Models\Companies\Company;
use App\Models\Epin\EpinProductEntities;
use App\Models\Epin\EpinProducts;
use App\Models\Transactions;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class OldApiFunctionsHelper
{

    public static function objectFormat($data, $parentKey = null, $resultStatus = true, $resultMessage = 'İşlem başarıyla gerçekleşti!', $resultCode = 100, $extraData = [])
    {
        if (count($data) > 0) {
            $formatedObj = [
                $parentKey => ($parentKey == 'Balance') ? $data[0] : $data,
                'ResultMessage' => $resultMessage,
                'ResultStatus' => $resultStatus,
                'ResultCode' => $resultCode,
            ];
        } else {
            $formatedObj = [
                'ResultMessage' => $resultMessage,
                'ResultStatus' => $resultStatus,
                'ResultCode' => $resultCode,
            ];
        }
        if (count($extraData) > 0) {
            foreach ($extraData as $key => $value) {
                $formatedObj[$key] = $value;
            }
        }
        header('Content-Type: application/json');
        return $formatedObj;
    }
    public static function checkOrderId($olderId, $companyId)
    {

        $olderIdFlag = Transactions::where('transactionId', $olderId)
            ->where('company_id', $companyId)
            ->first();
        if ($olderIdFlag) {
            OldApiFunctionsHelper::errorFunction('İşlem ID hatalı.', -212);
        }
        return true;
    }
    public static function getCompanyInfo($data)
    {
        $authorization = $data->header('Authorization');
        $apiName = $data->header('ApiName');
        $apiKey = $data->header('ApiKey');
        if (!$authorization || !$apiName || !$apiKey) {
            OldApiFunctionsHelper::errorFunction('Sistem hatası oluştu.');
        }
        $company = Company::whereHas('companyApi', function ($q) use ($authorization, $apiName, $apiKey) {
            $q->where('authorization', $authorization);
            $q->where('api_name', $apiName);
            $q->where('api_key', $apiKey);
        })
            ->where('status', 1)
            //->where('isActive',1)
            ->with(['products'])
            ->whereNull('deleted_at')
            ->first();
        if (!$company) {
            OldApiFunctionsHelper::errorFunction('Sistem hatası oluştu.');
        }
        return $company;
    }
    public static function errorFunction($message, $code = 500)
    {
        $result = OldApiFunctionsHelper::objectFormat([], '', false, $message, $code);
        header('Content-Type: application/json');
        echo json_encode($result);
        die();
    }
    public static function companyStockCheck($company, $productEnitity, $data)
    {

        if ($company->unlimited == 0 && $company->maxTransactionLimit != 0 && $data->Quantity > $company->maxTransactionLimit) {
            OldApiFunctionsHelper::errorFunction('İşlem limit aşımı.', -213);
        }

        if ($company->unlimited == 0 && ($productEnitity->price * $data->Quantity) > $company->ballance) {
            OldApiFunctionsHelper::errorFunction('Yetersiz bakiye.', -217);
        }
    }

    public static function productEntityCheck($productEntityId, $company, $data)
    {
        $productEnitity = EpinProductEntities::where('stock_code', $productEntityId)
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->first();
        if (!$productEnitity) {
            OldApiFunctionsHelper::errorFunction('Geçersiz stok kodu.', -215);
        }
        if (in_array($productEnitity->epinProduct_id, $company->products->pluck('product_id')->toArray())) {
            return $productEnitity;
        }
        OldApiFunctionsHelper::errorFunction('Ürün kaldırılmış veya erişim yetkileri değişmiş.', -216);
    }
    public static function makeOrder($company, $productEnitity, $data)
    {
        $data['preOrder'] = false;
        $data['controlle'] = false;
        $data['pins'] = [];
        //dd($company);
        $entityStokPins = OldApiFunctionsHelper::getPins();
        if (count($entityStokPins) == 0 && $company->preOrders == 0) {
            OldApiFunctionsHelper::errorFunction('Stok yetersiz..', -232);
        }
        if (count($entityStokPins) == 0) {
            $data['preOrder'] = true;
        }
        if ($company->limitControl != 0 && ($productEnitity->price * $data->Quantity) > $company->limitControl) {
            $data['controlle'] = true;
        }
        $data['pins'] = $entityStokPins;

        $clientId = OldApiFunctionsHelper::getClientId($data);
        $order = OldApiFunctionsHelper::createOrder($clientId, $entityStokPins, $company, $productEnitity, $data);


        return $data;
    }
    public static function getPins()
    {
        //return [];
        return ["YQ2V-P5N4-UY2N-SW5M"];
    }
    public static function getClientId($data)
    {
        $clientData = ClientsInfo::where('email', $data->get('Email'))
            ->where('phone', $data->get('PhoneNumber'))
            ->first();
        if ($clientData) {
            return $clientData->id;
        }
        $clientData = new ClientsInfo();
        $clientData->email = $data->get('Email');
        $clientData->phone = $data->get('PhoneNumber');
        $clientData->save();
        return $clientData->id;
    }
    public static function createOrder($clientId, $entityStokPins, $company, $productEnitity, $data)
    {

        $percentage = $company->percentage / 100;
        $flag = true;
        $decodedCodes = $entityStokPins;
        $status = (count($decodedCodes) > 0) ? 100 : 301;
        if ($data->controlle) {
            $status = 103;
            $flag = false;
        }
        if ($flag) {
            $companyInformation = Company::where('id',$company->id)->first();
            $companyInformation->ballance -= ($productEnitity->price * $data->get('Quantity')) - ($productEnitity->price * $data->get('Quantity') * $percentage);
            $companyInformation->save();
        }
        $transaction = new Transactions();
        $transaction->transactionId = $data->get('TransactionId');
        $transaction->company_id = $company->id;
        $transaction->epinProduct_entity_id = $productEnitity->id;
        $transaction->client_info_id = $clientId;
        $transaction->qty = $data->get('Quantity');
        $transaction->single_price = $productEnitity->price;
        $transaction->total_price = $productEnitity->price * $data->get('Quantity');
        $transaction->percentage = $percentage;
        $transaction->percentage_single_price = $productEnitity->price - ($productEnitity->price * $percentage);
        $transaction->percentage_total_price = ($productEnitity->price * $data->get('Quantity')) - ($productEnitity->price * $data->get('Quantity') * $percentage);
        $transaction->status = $status;
        $transaction->soldCodes = ($flag && count($decodedCodes) > 0) ? json_encode($decodedCodes) : null;
        $transaction->save();
        return $transaction;
    }
}
