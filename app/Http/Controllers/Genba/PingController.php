<?php

namespace App\Http\Controllers\Genba;

use App\Http\Controllers\Controller;
use App\Helpers\GenbaFunctionsHelper;

class PingController extends Controller
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
    
    public function checkConnection()
    {
        $data = [];
        $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.ping'));
        if ($rowData) {
            $data = [
                'etailerName' => json_decode($rowData)->EtailerName,
                'customerAccountNumber' => json_decode($rowData)->CustomerAccountNumber,
                'tokenValidFrom' => json_decode($rowData)->TokenValidFrom,
                'tokenValidTo' => json_decode($rowData)->TokenValidTo,
            ];
        }
        return $data;
    }
}
