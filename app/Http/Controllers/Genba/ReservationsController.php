<?php

namespace App\Http\Controllers\Genba;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\GenbaFunctionsHelper;

class ReservationsController extends Controller
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
            config('constants.genba.reservations'),
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
        $data = $this->reservationArray($rowData);
        return $data;
    }

    public function getById(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'reservationId' => 'required',
        ]);
        $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.reservations') . '/' . $request->get('reservationId'));
        $data = $this->reservationArray($rowData);
        return $data;
    }

    public function completeReservation(Request $request)
    {
        $this->validate($request, [
            'sku' => 'required',
            'clientTransactionID' => 'required',
            'reservationID'=>'required',
        ]);
        $data = [];
        $rowData = GenbaFunctionsHelper::getRequestResult(
            'post',
            config('constants.genba.orders'),
            [],
            [
                //'ClientTransactionID' => $request->get('clientTransactionID'),
                'ClientTransactionID' => uniqid(),
                'ReservationID' => $request->get('reservationID'),

            ]
        );
        return $rowData;
        $data = $this->reservationArray($rowData);
        return $data;
    }
    
    function reservationArray($rowData)
    {
        $data = [];
        if ($rowData) {
            $temp = json_decode($rowData);
            $data = [
                'id' => $temp->ID,
                'clientTransactionID' => $temp->ClientTransactionID,
                'sku' => $temp->Sku,
                'created' => $temp->Created,
                'expiration' => $temp->Expiration,
                'state' => $temp->State,
                'communicatedBuyingPrice' => $temp->CommunicatedBuyingPrice,
                'actualBuyingPrice' => $temp->ActualBuyingPrice,
                'sellingPrice' => $temp->SellingPrice,
            ];

        }
        return $data;
    }
}
