<?php

namespace App\Http\Controllers;

use App\Models\EpinSiteProducts;
use App\Models\Genba\GenbaPrice;
use App\Models\Genba\GenbaSkuCheck;

class EpinSiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->usdPrice=9;
        $this->kdv=1;
    }

    public function index()
    {
        $genbaPrices = GenbaPrice::with(['product'])->get();
        if (count($genbaPrices) > 0) {
            foreach ($genbaPrices as $genbaPrice) {
                $finalPrice=0;
                if ($genbaPrice->currencyCode == 'TRY') {
                    //dump($genbaPrice->srp);
                    $finalPrice=$genbaPrice->srp*$this->kdv;
                }
                else{
                    //dump($genbaPrice->srp);
                    //dump($genbaPrice->srp*$this->usdPrice);
                    $finalPrice=$genbaPrice->srp*$this->usdPrice*$this->kdv;
                }
                $siteProduct = EpinSiteProducts::where('sku', $genbaPrice->product->sku)
                    ->first();
                if ($siteProduct) {
                    //dump($siteProduct->price);
                    $siteProduct->price=$finalPrice;
                    //dump($siteProduct->alis_fiyati);
                    $siteProduct->alis_fiyati=$finalPrice;
                    $siteProduct->save();
                }
            }
        }
    }
    public function skuMatch()
    {
        $skus=GenbaSkuCheck::get();
        if(count($skus)>0){
            foreach($skus as $sku)
            {
                $siteProduct = EpinSiteProducts::where('product_id', $sku->epin_product_id)->first();
                if($siteProduct)
                {
                    $siteProduct->sku=$sku->sku;
                    $siteProduct->save();
                }
            }
        }
    }
}
