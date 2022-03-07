<?php

namespace App\Http\Controllers\Hepsiburada;

use App\Helpers\HepsiburadaHelper;
use App\Http\Controllers\Controller;

class OrderController extends Controller
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

    public function listingCompletedOrders()
    {
        $result=HepsiburadaHelper::getRequestResult('get',config('constants.hepsiburada.listingCompletedOrders'),[],[],null,true);
        return $result;
    }
    public function packingPen()
    {
        $result=HepsiburadaHelper::getRequestResult('get',config('constants.hepsiburada.productStatusInquiry'),[],[],null,true);
        return $result;
    }
}
