<?php

namespace App\Http\Controllers\Epin;

use App\Helpers\EpinFunctionsHelper;
use App\Http\Controllers\Controller;
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

    public function index()
    {
        $data = [];
        $rowData = EpinFunctionsHelper::getRequestResult('get', config('constants.epin.getCategoryList'));
        dd($rowData);
        $data = $rowData;
        return $data;
    }
    public function productsDetails()
    {
        $data = [];
        $rowData = EpinFunctionsHelper::getRequestResult('get', config('constants.epin.getGameList'));
        dd($rowData);
        $data = $rowData;
        return $data;
    }
    public function single(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'id' => 'required',
        ]);
        $rowData = EpinFunctionsHelper::getRequestResult(
            'post',
            config('constants.epin.gameItemListById'),
            [],
            ['id' => $request->get('id'),]

        );
        dd($rowData);
        $data = $rowData;
        return $data;
    }
}
