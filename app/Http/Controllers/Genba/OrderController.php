<?php

namespace App\Http\Controllers\Genba;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\GenbaFunctionsHelper;
use App\Models\ClientToken;
use App\Models\Genba\GenbaOrderLogs;
use App\Models\Genba\GenbaPrice;
use App\Models\Genba\GenbaProducts;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->maxCount = 3;
        $this->usdPrice = 9;
        $this->kdv = 1;
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'sku' => 'required',
            'clientTransactionID' => 'required',
            'sellingPriceNetAmount' => 'required',
            'sellingPriceGrossAmount' => 'required',
            'sellingPriceCurrencyCode' => 'required',
            'countryCode' => 'required',
            'consumerIP' => 'required',
            'buyingPriceAmount' => 'required',
            'buyingPriceCurrencyCode' => 'required',
        ]);
        $data = [];
        $rowData = GenbaFunctionsHelper::getRequestResult(
            'post',
            config('constants.genba.orders'),
            [],
            [
                //'ClientTransactionID' => $request->get('clientTransactionID'),
                'ClientTransactionID' => uniqid(),
                'Properties' => [
                    'sku' => $request->get('sku'),
                    'BuyingPrice' => [
                        'Amount' => $request->get('buyingPriceAmount'),
                        'CurrencyCode' => $request->get('buyingPriceCurrencyCode'),
                    ],
                    'SellingPrice' => [
                        'NetAmount' => $request->get('sellingPriceNetAmount'),
                        'GrossAmount' => $request->get('sellingPriceGrossAmount'),
                        'CurrencyCode' => $request->get('sellingPriceCurrencyCode'),
                    ],
                    'CountryCode' => $request->get('countryCode'),
                    'ConsumerIP' => $request->get('consumerIP'),
                ],

            ]
        );
        $data = $this->orderArray($rowData);
        return $data;
    }

    public function getById(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'orderId' => 'required',
        ]);
        $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.orders') . '/' . $request->get('orderId'));
        return $rowData;
        $data = $this->orderArray($rowData);
        return $data;
    }

    public function getByCtid(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'ctid' => 'required',
        ]);
        $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.ordersByCtid') . '/' . $request->get('ctid'));
        $data = $this->orderArray($rowData);
        return $data;
    }

    public function returnOrder(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'orderId' => 'required',
            'reason' => 'required',
        ]);
        $rowData = GenbaFunctionsHelper::getRequestResult(
            'post',
            config('constants.genba.orders') . '/' . $request->get('orderId'),
            [],
            [
                'action' => [
                    'Action' => 'Return',
                    //'Reason' => $request->get('reason'),
                ],
            ],
        );
        $data = $this->orderArray($rowData);
        return $data;
    }

    function orderArray($rowData)
    {
        $data = [];
        if ($rowData) {
            $temp = json_decode($rowData);
            $data = [
                'id' => $temp->ID,
                'clientTransactionID' => $temp->ClientTransactionID,
                'sku' => $temp->Sku,
                'created' => $temp->Created,
                'state' => $temp->State,
                'communicatedBuyingPrice' => $temp->CommunicatedBuyingPrice,
                'actualBuyingPrice' => $temp->ActualBuyingPrice,
                'sellingPrice' => $temp->SellingPrice,
            ];


            if (isset($temp->Keys) && count($temp->Keys) > 0) {
                $x = 0;
                foreach ($temp->Keys as $key) {
                    $data['keys'][$x] = [
                        'sku' => $key->Sku,
                        'otherDetails' => [],
                    ];
                    if (count($key->OtherDetails) > 0) {
                        foreach ($key->OtherDetails as $otherDetail) {
                            $data['keys'][$x]['otherDetails'][] = [
                                'name' => $otherDetail->Name,
                                'value' => $otherDetail->Value,
                            ];
                        }
                    }
                    $x++;
                }
            }
        }
        return $data;
    }
    public function clientToken(Request $request)
    {
        
        $fofoIp=long2ip(rand(0, 255*255)*rand(0, 255*255));
        $fofoUserId=rand(3,256659);

        $this->validate($request, [
            //'ip' => 'required',
            //'user_id' => 'required',
            'sku' => 'required',
            'price' => 'required',
        ]);
        if ($request->get('price')==0) {
            return [
                'error' => 708,
                'message' => 'price not matched'
            ];
        }
        //$oldTokens = ClientToken::where('user_id', $request->get('user_id'))
        $oldTokens = ClientToken::where('user_id', $fofoUserId)
            ->where('sku', $request->get('sku'))
            ->where('status', 1)
            ->get();

        $countOldTokens = count($oldTokens);
        if ($countOldTokens == $this->maxCount) {
            return [
                'error' => 705,
                'message' => 'out of limit'
            ];
        }
        $genbaProduct = GenbaProducts::where('sku', $request->get('sku'))->first();
        if (!$genbaProduct) {
            return [
                'error' => 706,
                'message' => 'the sku not exist'
            ];
        }
        $productPrice = GenbaPrice::where('product_id', $genbaProduct->id)->first();
        if (!$productPrice) {
            return [
                'error' => 707,
                'message' => 'no price founded'
            ];
        }
        $priceflag = false;
        if ($productPrice->currencyCode == 'TRY') {
            //dump($productPrice->srp);
            $fofo=($request->get('price') / $this->kdv);
            //dump($fofo);
            //dump($productPrice->srp != $fofo);
            if ($productPrice->srp != $fofo) {
                $priceflag = true;
            }
        } else {
            if ($productPrice->srp != ((($request->get('price') * $this->usdPrice)) / $this->kdv)) {
                $priceflag = true;
            }
        }
        if (!$priceflag) {
            // return [
            //     'error' => 708,
            //     'message' => 'price not matched'
            // ];
        }
        //return $productPrice;
        //ClientToken::where('user_id', $request->get('user_id'))
        ClientToken::where('user_id', $fofoUserId)
            ->where('sku', $request->get('sku'))
            ->where('status', 0)
            ->delete();
        $token = new ClientToken();
        //$token->ip = $request->get('ip');
        $token->ip = $fofoIp;
        //$token->user_id = $request->get('user_id');
        $token->user_id = $fofoUserId;
        $token->sku = $request->get('sku');
        $token->price = $request->get('price');
        $token->one_time_token = app('hash')->make(uniqid());;
        $token->save();

        GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.ping'));

        return [
            'code' => 200,
            'status' => true,
            'token' => $token->one_time_token,
        ];
    }
    public function buyKey(Request $request)
    {
        //return 'we are off line ';
        $this->validate($request, [
            'token' => 'required',
            // 'ip' => 'required',
            // 'user_id' => 'required',
            // 'price' => 'required',
            //  'sku' => 'required',
            // 'clientTransactionID' => 'required',
            // 'sellingPriceNetAmount' => 'required',
            // 'sellingPriceGrossAmount' => 'required',
            // 'sellingPriceCurrencyCode' => 'required',
            // 'countryCode' => 'required',
            // 'consumerIP' => 'required',
            // 'buyingPriceAmount' => 'required',
            // 'buyingPriceCurrencyCode' => 'required',
        ]);
        $data = [];

        $requestData = ClientToken::where('one_time_token', $request->get('token'))->first();
        if(!$requestData)
        {
            return [
                'error' => 709,
                'message' => 'token expired',
            ];
        }
        $date = $requestData->created_at;
        if ($date->diffInMinutes(Carbon::now()) > 5) {
            $requestData->status = 2;
            $requestData->save();
            return [
                'error' => 709,
                'message' => 'token expired',
            ];
        }
        $genbaProduct = GenbaProducts::where('sku', $requestData->sku)->first();
        $genbaProductPrice = GenbaPrice::where('product_id', $genbaProduct->id)->first();

        $rowData = GenbaFunctionsHelper::getRequestResult(
            'post',
            config('constants.genba.orders'),
            [],
            [
                //'ClientTransactionID' => $request->get('clientTransactionID'),
                'ClientTransactionID' => $requestData->one_time_token,
                'Properties' => [
                    'sku' => $genbaProduct->sku,
                    'BuyingPrice' => [
                        'Amount' => $genbaProductPrice->wsp,
                        'CurrencyCode' => $genbaProductPrice->currencyCode,
                    ],
                    'SellingPrice' => [
                        'NetAmount' => $genbaProductPrice->srp,
                        'GrossAmount' => $genbaProductPrice->srp * $this->kdv,
                        'CurrencyCode' => $genbaProductPrice->currencyCode,
                    ],
                    'CountryCode' => 'TR',
                    'ConsumerIP' => $requestData->ip,
                ],

            ]
        );
        if ($rowData) {
            $data['code'] = 200;
            $data['status'] = true;
            $data['token'] = $requestData->one_time_token;
            $data['keys'] = json_decode($rowData)->Keys;
        }
        $requestData->status = 1;
        $requestData->save();

        $orderLog = new GenbaOrderLogs;
        $orderLog->one_time_token = $requestData->one_time_token;
        $orderLog->order_log = $rowData;
        $orderLog->save();
        return $data;
    }
}
