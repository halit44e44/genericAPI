<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EpinApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getOrder(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'transactionId' => 'required', //int Bu sipariş işlemi için Epin API tarafından belirlenen ID 
            'stockCode' => 'required', //string Ürünün Gemba stok kodu 
            'quantity' => 'required', //int Sipariş adedi 
            'price' => 'required', //float Ürünün Epin API'de kayıtlı fiyatı (TRY) 
            'souceId' => 'required', //int Siparişi oluşturan kurum ID (Epin, YYG, Papara vs) 
            'customerIp' => 'required', //string Sipariş sahibinin IP adresi

        ]);

        return $data;
        return $request->all();
    }

    function stokCheck()
    {
        $data = [
            'transactionId'=>1123424,
            'Price'=>2.2,
            'Keys'=>[],
        ];
        return ['data' => $data, 'statusCode' => 2, 'statusMsg' => 'StockCode err'];
    }

    function priceCheck()
    {
        $data = [
            'transactionId'=>1123424,
            'Price'=>2.2,
            'Keys'=>[],
        ];
        return ['data' => $data, 'statusCode' => 5, 'statusMsg' => 'Price err'];
    }

    function countCheck()
    {
        $data = [
            'transactionId'=>1123424,
            'Price'=>2.2,
            'Keys'=>[],
        ];
        return ['data' => $data, 'statusCode' => 9, 'statusMsg' => 'Quantity err'];
    }

    function sourceCustomerIpCheck()
    {
        $data = [
            'transactionId'=>1123424,
            'Price'=>2.2,
            'Keys'=>[],
        ];
        return ['data' => $data, 'statusCode' => 18, 'statusMsg' => 'Limit err'];
    }

    function orderSuccess()
    {
        $data=[
            'transactionId'=>1123424,
            'Price'=>2.2,
            'Keys'=>[
                ['key'=>'43534-34534-345345-345','Serial'=>'4323423423'],
                ['key'=>'43534-34534-345345-345','Serial'=>'4323423423'],
                ['key'=>'43534-34534-345345-345','Serial'=>'4323423423'],
                ['key'=>'43534-34534-345345-345','Serial'=>'4323423423']
            ],
        ];
        return ['data' => $data, 'statusCode' => 100, 'statusMsg' => 'OK'];
    }

    function orderFailed()
    {
        $data=[
            'transactionId'=>1123424,
            'Price'=>2.2,
            'Keys'=>[],
        ];
        return ['data' => $data, 'statusCode' => 90, 'statusMsg' => '??? err'];
    }
    
}
