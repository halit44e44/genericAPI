<?php

namespace App\Http\Controllers\Genba;

use App\Http\Controllers\Controller;
use App\Helpers\GenbaFunctionsHelper;
use App\Models\Genba\GenbaPrice;
use App\Models\Genba\GenbaProducts;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PricesController extends Controller
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

    public function index(Request $request, $continuationToken = '')
    {

        $acceptedPricesCurrency = ['USD', 'TRY'];
        $acceptedPricesCurrencyDb = [
            'USD' => 'en_price',
            'TRY' => 'tr_price',
            //'EUR' => 'eur_price',
        ];
        $data = [];
        $rowData = null;
        if (!$request->get('sync')) {
            if ($continuationToken != '') {
                $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.prices'), ['continuationtoken' => $continuationToken]);
            } else {
                $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.prices'));
            }
            if ($rowData) {
                if (!$request->get('sync') || $request->auth->name != 'synchroniser') {
                    echo 'count all pricess in one page ';
                    echo count(json_decode($rowData)->Prices);
                    echo '<br/>';
                    $x = 0;
                    $j = 0;
                    if (count(json_decode($rowData)->Prices) > 0) {
                        foreach (json_decode($rowData)->Prices as $price) {
                            if ($price->CurrencyCode == 'TRY') {
                                $x++;
                            }
                            if ($price->CurrencyCode == 'USD') {
                                $j++;
                            }
                        }
                        echo 'count all try prices ';
                        echo $x;
                        echo '<br/>';
                        echo 'count all usd prices ';
                        echo $j;
                        echo '<br/>';
                    }
                    if (isset(json_decode($rowData)->ContinuationToken)) {
                        $this->index($request, json_decode($rowData)->ContinuationToken);
                    }
                    return 1;
                    return json_decode($rowData)->Prices;
                }
            }
        }

        $limit = ($request->get('perPage')) ? $request->get('perPage') : 1;
        $startId = ($request->get('startId')) ? $request->get('startId') : 1;
        $genbaProducts = GenbaProducts::where('status', 'active')
            ->where('id', '>=', $startId)
            //->where('id',745)
            ->limit($limit)
            ->get();
          //dd($genbaProducts); 
        if (count($genbaProducts) > 0) {
            foreach ($genbaProducts as $genbaProduct) {
                $priceRowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.prices'), ['sku' => $genbaProduct->sku]);
                
                if ($priceRowData) {
                    if (count(json_decode($priceRowData)->Prices) > 0) {
                        
                        foreach (json_decode($priceRowData)->Prices as $price) {
                            if (!in_array($price->CurrencyCode, $acceptedPricesCurrency)) {
                                continue;
                            }
                            if ($genbaProduct->sku != $price->Sku) {
                                
                                continue; 
                            }
                            if ($genbaProduct->regionCode != $price->RegionCode) {
                                continue; 
                            }
                            $genbaPrice = GenbaPrice::where('product_id', $genbaProduct->id)
                                //->where('currencyCode', $price->CurrencyCode)
                                ->first();
                            //dump($genbaPrice->toArray());
                            
                            if (!$genbaPrice) {
                                $genbaPrice = new GenbaPrice;
                                $genbaPrice->product_id = $genbaProduct->id;
                                $genbaPrice->regionCode = $price->RegionCode;
                                $genbaPrice->currencyCode = $price->CurrencyCode;
                                $genbaPrice->wsp = $price->Wsp;
                                $genbaPrice->srp = $price->Srp;
                                $genbaPrice->isPromotion = $price->IsPromotion;
                                $genbaPrice->save();
                                $genbaProduct = GenbaProducts::where('id', $genbaProduct->id)->first();
                                $genbaProduct->tr_price = ($price->CurrencyCode == 'TRY') ? 1 : 0;
                                $genbaProduct->en_price = ($price->CurrencyCode == 'TRY') ? 0 : 1;
                                $genbaProduct->save();
                                continue;
                            } else {
                                if ($genbaPrice->currencyCode == 'TRY') {
                                    if ($price->CurrencyCode != 'TRY') {
                                        continue;
                                    }
                                    
                                    $genbaPrice->product_id = $genbaProduct->id;
                                    $genbaPrice->regionCode = $price->RegionCode;
                                    $genbaPrice->currencyCode = $price->CurrencyCode;
                                    $genbaPrice->wsp = $price->Wsp;
                                    $genbaPrice->srp = $price->Srp;
                                    $genbaPrice->isPromotion = $price->IsPromotion;
                                    $genbaPrice->save();
                                    $genbaProduct = GenbaProducts::where('id', $genbaProduct->id)->first();
                                    $genbaProduct->tr_price = 1;
                                    $genbaProduct->en_price = 0;
                                    $genbaProduct->save();
                                } else {
                                    if ($genbaPrice->regionCode == 'ROW') {
                                        if ($price->RegionCode != 'ROW') {
                                            continue;
                                        }
                                        $genbaPrice->product_id = $genbaProduct->id;
                                        $genbaPrice->regionCode = $price->RegionCode;
                                        $genbaPrice->currencyCode = $price->CurrencyCode;
                                        $genbaPrice->wsp = $price->Wsp;
                                        $genbaPrice->srp = $price->Srp;
                                        $genbaPrice->isPromotion = $price->IsPromotion;
                                        $genbaPrice->save();
                                        $genbaProduct = GenbaProducts::where('id', $genbaProduct->id)->first();
                                        $genbaProduct->tr_price = 0;
                                        $genbaProduct->en_price = 1;
                                        $genbaProduct->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // if (count(json_decode($rowData)->Prices) > 0) {
        //     $x = 0;
        //     foreach (json_decode($rowData)->Prices as $price) {
        //         if (!in_array($price->CurrencyCode, $acceptedPricesCurrency)) {
        //             continue;
        //         }
        //         // $genbaProduct = GenbaProducts::where('status', 'active')
        //         //     ->where('sku', $price->Sku)
        //         //     ->first();
        //         // if (!$genbaProduct) {
        //         //     continue;
        //         // }
        //         // $x++;
        //     }
        //     dump($x);
        // }

        //     if (count($genbaProducts) > 0) {
        //         foreach ($genbaProducts as $genbaProduct) {
        //             if (count(json_decode($rowData)->Prices) > 0) {
        //                 foreach (json_decode($rowData)->Prices as $price) {
        //                     if ($genbaProduct->sku == $price->Sku) {
        //                         if (in_array($price->CurrencyCode, $acceptedPricesCurrency)) {
        //                             $genbaPrice = GenbaPrice::where('wsp', $price->Wsp)
        //                                 ->where('srp', $price->Srp)
        //                                 ->where('currencyCode', $price->CurrencyCode)
        //                                 ->where('regionCode', $price->RegionCode)
        //                                 ->where('isPromotion', $price->IsPromotion)
        //                                 ->where('product_id', $genbaProduct->id)
        //                                 ->first();
        //                             if ($genbaPrice) {
        //                                 continue;
        //                             }
        //                             $genbaPrice = new GenbaPrice;
        //                             $genbaPrice->product_id = $genbaProduct->id;
        //                             $genbaPrice->regionCode = $price->RegionCode;
        //                             $genbaPrice->currencyCode = $price->CurrencyCode;
        //                             $genbaPrice->wsp = $price->Wsp;
        //                             $genbaPrice->srp = $price->Srp;
        //                             $genbaPrice->isPromotion = $price->IsPromotion;
        //                             $genbaPrice->save();

        //                             $tempTemp = $acceptedPricesCurrencyDb[$price->CurrencyCode];
        //                             $genbaSingleProduct = GenbaProducts::where('id', $genbaProduct->id)->first();
        //                             $genbaSingleProduct->$tempTemp = 1;
        //                             $genbaSingleProduct->price_sync = Carbon::now();
        //                             $genbaSingleProduct->save();
        //                         }
        //                     } else {
        //                         // if (in_array($price->CurrencyCode, $acceptedPricesCurrency)) {
        //                         //     //dump([$price->CurrencyCode=>$price->Sku]);
        //                         // }
        //                     }
        //                     continue;
        //                     $data[] = [
        //                         'sku' => $price->Sku,
        //                         'product' => $price->Product,
        //                         'pegionCode' => $price->RegionCode,
        //                         'currencyCode' => $price->CurrencyCode,
        //                         'wsp' => $price->Wsp,
        //                         'srp' => $price->Srp,
        //                         'isPromotion' => $price->IsPromotion,
        //                     ];
        //                 }
        //             }
        //         }
        //     }
        // }
        // if (isset(json_decode($rowData)->ContinuationToken)) {
        //     $this->index($request, json_decode($rowData)->ContinuationToken);
        // }
        // return $data;
    }

    public function singleSku(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'sku' => 'required',
        ]);
        $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.prices'), ['sku' => $request->get('sku')]);
        if ($rowData) {
            if (count(json_decode($rowData)->Prices) > 0) {
                foreach (json_decode($rowData)->Prices as $price) {
                    $data[] = [
                        'sku' => $price->Sku,
                        'product' => $price->Product,
                        'pegionCode' => $price->RegionCode,
                        'currencyCode' => $price->CurrencyCode,
                        'wsp' => $price->Wsp,
                        'srp' => $price->Srp,
                        'isPromotion' => $price->IsPromotion,
                    ];
                }
            }
        }
        return $data;
    }
    public function singleId(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'productid' => 'required',
        ]);
        $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.prices'), ['productid' => $request->get('productid')]);
        if ($rowData) {
            if (count(json_decode($rowData)->Prices) > 0) {
                foreach (json_decode($rowData)->Prices as $price) {
                    $data[] = [
                        'sku' => $price->Sku,
                        'product' => $price->Product,
                        'pegionCode' => $price->RegionCode,
                        'currencyCode' => $price->CurrencyCode,
                        'wsp' => $price->Wsp,
                        'srp' => $price->Srp,
                        'isPromotion' => $price->IsPromotion,
                    ];
                }
            }
        }
        return $data;
    }
}
