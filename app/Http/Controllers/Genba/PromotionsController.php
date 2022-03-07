<?php

namespace App\Http\Controllers\Genba;

use App\Http\Controllers\Controller;
use App\Helpers\GenbaFunctionsHelper;
use App\Models\CdkeyDiscount;
use App\Models\EpinSiteProducts;
use Carbon\Carbon;

class PromotionsController extends Controller
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

    public function index()
    {
        $dataTemp = [];
        $data = [];
        $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.promotions'));
        if ($rowData) {
            $dataTemp = [
                'promotions' => json_decode($rowData)->Promotions,
            ];
        }
        if (count($dataTemp['promotions']) > 0) {
            foreach ($dataTemp['promotions'] as $promotion) {

                $from = Carbon::createFromFormat('Y-m-d h:i:s', date('Y-m-d h:i:s', strtotime($promotion->From)));
                $to = Carbon::createFromFormat('Y-m-d h:i:s', date('Y-m-d h:i:s', strtotime($promotion->To)));
                $now = Carbon::now()->format('Y-m-d h:i:s');
                if ($from->lte($now) && $to->gte($now)) {
                    // dump($promotion->Name);
                    // dump($promotion->From);
                    // dump($promotion->To);
                    if (count($promotion->PromotionItems)) {
                        foreach ($promotion->PromotionItems as $item) {
                            if ($item->CurrencyCode == 'TRY' || $item->CurrencyCode == 'USD') {

                                $epinProduct = EpinSiteProducts::where('sku', $item->Sku)->first();
                                if (!$epinProduct) {
                                    continue;
                                }
                                $cdkeyDiscount = CdkeyDiscount::where('sku', $epinProduct->product_id)->first();
                                if (!$cdkeyDiscount) {
                                    $cdkeyDiscount = new CdkeyDiscount();
                                }
                                $cdkeyDiscount->product_id=$epinProduct->product_id;
                                $cdkeyDiscount->sku=$item->Sku;
                                $cdkeyDiscount->old_price=$epinProduct->price;
                                $cdkeyDiscount->last_date=$to;
                                $cdkeyDiscount->save();
                                dump($cdkeyDiscount);
                                // dd($cdkeyDiscount);
                                // dump($promotion->Name);
                                // dump($promotion->From);
                                // dump($promotion->To);
                                $data['promotions'][] = $item;
                                dump($epinProduct->toArray()['sku']);
                            } else {
                                continue;
                            }
                        }
                    }
                }
            }
        }
        dump($data);
        //return $data;
    }
}
