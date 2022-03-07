<?php

namespace App\Http\Controllers;

use App\Models\Genba\GenbaProducts;
use App\Models\Genres;
use Illuminate\Http\Request;

class ProductsController extends Controller
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

    public function index(Request $request)
    {
        $dollarValue = 9;
        $data = [];
        $this->validate($request, [
            'category' => 'required',
        ]);
        if ($request->get('category') == 'cdKeys') {
            $products = GenbaProducts::where('status', 'active')
                ->where('id', '>', '616')
                ->groupBy('productId')
                ->where('deleted_at', null)
                ->whereHas('productDetails')
                ->with([
                    'productDetails',
                    'languagesEnglish',
                    'metaData',
                    'instractionsEnglish',
                    'images',
                    'videos',
                    'ageRestrictions',
                    'gameLanguage',
                    'pricesUsd',
                    'pricesTRY',
                ])
                //->orderBy('id','DESC')
                ->limit(10)
                ->get();
            //dump(count($products));
            //dd($products->toArray());
            if (count($products) > 0) {
                foreach ($products as $product) {
                    //dd($product);
                    if ($product->productDetails) {
                        $metaData = [];
                        if (count($product->metaData) > 0) {
                            foreach ($product->metaData as $meta) {
                                $metaData[] = [
                                    'key' => $meta->parentCategory,
                                    'value' => $meta->values,
                                ];
                            }
                        }
                        $videosArr = [];
                        if (count($product->videos) > 0) {
                            foreach ($product->videos as $videos) {
                                $videosArr[] = [
                                    'videoUrl' => $videos->video_url,
                                    'videoImage' => $videos->posterFrameURL,
                                ];
                            }
                        }
                        $gameImage = '';
                        $gallary = [];
                        if (count($product->images) > 0) {
                            $x = 0;
                            foreach ($product->images as $images) {

                                if ($images->graphicType == 'Packshot' && $x == 0) {
                                    $gameImage = 'https://cdn.epin.com.tr/' . $images->cdnImageUrl;
                                    $x++;
                                    continue;
                                } else {
                                    $gallary[] = 'https://cdn.epin.com.tr/' . $images->cdnImageUrl;
                                }
                            }
                            if ($gameImage == '') {
                                $gameImage = (isset($gallary[0])) ? $gallary[0] : '';
                            }
                        }
                        $price = null;
                        if ($product->pricesTRY) {
                            $price = $product->pricesTRY->srp;
                        } elseif ($product->pricesUSD) {
                            $product->pricesUSD->srp * $dollarValue;
                        }

                        $data[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'price' => $price,
                            'releaseDate' => $product->productDetails->releaseDate,
                            'genres' => Genres::whereIn('id', json_decode($product->productDetails->genres))->pluck('name'),
                            'publisher' => ($product->productDetails->publisher) ? $product->productDetails->publisher->name : '',
                            'developer' => ($product->productDetails->developer) ? $product->productDetails->developer->name : '',
                            'platform' => ($product->productDetails->platform) ? $product->productDetails->platform->name : '',
                            //'language' => $product->languages,
                            'description' => ($product->languagesEnglish) ? $product->languagesEnglish->localisedDescription : '',
                            'metaData' => $metaData,
                            //'instractions' => $product->instractions,
                            'instractions' => ($product->instractionsEnglish) ? $product->instractionsEnglish->value : '',
                            'image' => $gameImage,
                            'gallery' => $gallary,
                            'videos' => $videosArr,
                            'ageRestriction' => ($product->ageRestrictions) ? $product->ageRestrictions->age : null,
                            'menuLanguages' => ($product->gameLanguage)
                                ? json_decode($product->gameLanguage->spokenLanguageSet)
                                + json_decode($product->gameLanguage->subtitleLanguageSet)
                                + json_decode($product->gameLanguage->menuLanguageSet)
                                : null,


                        ];
                    }
                }
            }
        }
        return $data;
    }
}
