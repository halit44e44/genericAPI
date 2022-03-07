<?php

namespace App\Http\Controllers\Epin;

use App\Helpers\EpinFunctionsHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersReportController extends Controller
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

    public function orderReport(Request $request)
    {

        $data = [];
        $this->validate($request, [
            'startDate' => 'required',
            'endDate' => 'required',
        ]);
        $rowData = EpinFunctionsHelper::getRequestResult(
            'post',
            config('constants.epin.orderReport'),
            [],
            [
                'startDate' => $request->get('startDate'),
                'endDate' => $request->get('endDate'),
            ],
        );
        dd($rowData);
        $data = $rowData;
        return $data;
    }
    public function details(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'startDate' => 'required',
            'endDate' => 'required',
        ]);
        $rowData = EpinFunctionsHelper::getRequestResult(
            'post',
            config('constants.epin.orderReportDetail'),
            [],
            [
                'startDate' => $request->get('startDate'),
                'endDate' => $request->get('endDate'),
            ],
        );
        dd($rowData);
        $data = $rowData;
        return $data;
    }
    public function hourly(Request $request)
    {
        $data = [];
        $this->validate($request, [
            'startDate' => 'required',
            'endDate' => 'required',
        ]);
        $rowData = EpinFunctionsHelper::getRequestResult(
            'post',
            config('constants.epin.orderReportDetail'),
            [],
            [
                'startDate' => $request->get('startDate'),
                'endDate' => $request->get('endDate'),
            ],
        );
        dd($rowData);
        $data = $rowData;
        return $data;
    }
}
