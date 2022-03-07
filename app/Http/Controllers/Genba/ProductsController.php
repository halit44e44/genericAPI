<?php

namespace App\Http\Controllers\Genba;

use App\Http\Controllers\Controller;
use App\Helpers\GenbaFunctionsHelper;
use App\Jobs\ProducDetailsJob;
use App\Models\Genba\GenbaProducts;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->productCount = 0;
        $this->gamesList = [];
    }

    public function index(Request $request, $continuationToken = '')
    {

        $data = [
            'newGames' => [],
            'removedGames' => [],
            'reavtivated' => [],
        ];
        if ($continuationToken != '') {
            $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.products'), ['continuationtoken' => $continuationToken]);
        } else {
            $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.products'));
        }


        if ($rowData) {
            if (!$request->get('sync') || $request->auth->name != 'synchroniser') {
                return json_decode($rowData)->Products;
            }

            if (count(json_decode($rowData)->Products) > 0) {
                $genbaActiveProducts = GenbaProducts::where('status', 'active')->pluck('sku')->toArray();
                $genbaStopedProducts = GenbaProducts::where('status', 'stoped')->pluck('sku')->toArray();
                $genbaInactiveProducts = GenbaProducts::where('status', 'inactive')->pluck('sku')->toArray();
                foreach (json_decode($rowData)->Products as $product) {

                    if (in_array($product->Sku, $genbaInactiveProducts)) {
                        continue;
                    }
                    if (in_array($product->Sku, $genbaActiveProducts)) {
                        $genbaActiveProducts = array_diff($genbaActiveProducts, [$product->Sku]);
                        continue;
                    }
                    if (in_array($product->Sku, $genbaStopedProducts)) {
                        $removedGame = GenbaProducts::where('sku', $product->Sku)->first();
                        $removedGame->status = 'active';
                        $removedGame->save();
                        $data['reavtivated'][] = $removedGame->name;
                        $genbaStopedProducts = array_diff($genbaStopedProducts, [$product->Sku]);
                        continue;
                    }
                    $genbaProduct = new GenbaProducts;
                    $genbaProduct->productID = $product->ProductID;
                    $genbaProduct->sku = $product->Sku;
                    $genbaProduct->regionCode = $product->RegionCode;
                    $genbaProduct->name = $product->Name;
                    $genbaProduct->isBundle = (isset($product->IsBundle)) ? $product->IsBundle : null;
                    $genbaProduct->save();

                    $newProduct = new Products;
                    $newProduct->product_id = $genbaProduct->id;
                    $newProduct->category_id = 1;
                    $newProduct->supplier_id = 1;
                    $newProduct->save();
                    $data['newGames'][] = $product->Name;
                }
            }
        }
        // if (count($genbaActiveProducts) > 0) {
        //     foreach ($genbaActiveProducts as $genbaProduct) {
        //         $removedGame = GenbaProducts::where('sku', $genbaProduct)->first();
        //         $removedGame->status = 'stoped';
        //         $removedGame->save();

        //         $newProduct = Products::where('product_id', $removedGame->id)
        //             ->where('supplier_id', 1)
        //             ->where('category_id', 1)
        //             ->first();
        //         $newProduct->status=0;
        //         $newProduct->save();

        //         $data['removedGames'][] = $removedGame->name;
        //     }
        // }
        if (isset(json_decode($rowData)->ContinuationToken)) {

            $this->index($request, json_decode($rowData)->ContinuationToken);
        }
        return $data;
    }

    public function single(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'sku' => 'required',
        ]);
        $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.products') . '/' . $request->get('sku'));
        if ($rowData) {
            GenbaFunctionsHelper::json($rowData);
        }
        return $data;
    }

    public function allProductsData()
    {
        $fofo = new ProducDetailsJob();
        dispatch($fofo);
        die();
    }
    public function gameList($continuationToken = '')
    {
        $rowData = [];
        if ($continuationToken != '') {
            $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.products'), ['continuationtoken' => $continuationToken]);
        } else {
            $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.products'));
        }
        if (count(json_decode($rowData)->Products) > 0) {
            foreach (json_decode($rowData)->Products as $product) {
                $this->gamesList[$product->Name] = [
                    'productId' => $product->ProductID,
                    'sku' => $product->Sku,
                    'isBundle' => $product->IsBundle,
                    'regionCode'=>$product->RegionCode
                ];
            }
        }
        //dd($this->gamesList);
        //$this->productCount += count(json_decode($rowData)->Products);
        if (isset(json_decode($rowData)->ContinuationToken)) {

            $this->gameList(json_decode($rowData)->ContinuationToken);
        }
        dump(count($this->gamesList));
        dump($this->gamesList);
        // return 1;
    }
}
