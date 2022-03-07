<?php

namespace App\Http\Controllers\Hepsiburada;

use App\Helpers\HepsiburadaHelper;
use App\Http\Controllers\Controller;

class ProductController extends Controller
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

    public function saveProducts()
    {

        //$productFilePath=HepsiburadaHelper::jsonFileGenerate();x
        
        $productFilePath='/Applications/Ampps/www/api/public/upload/json/1632320789_file.json';
        
        //dd($productFilePath);
        $result=HepsiburadaHelper::getRequestResult('post',config('constants.hepsiburada.importProducts'),[],[],$productFilePath);
        return $result;
    }
    public function categoriesList()
    {
        $result=HepsiburadaHelper::getRequestResult('get',config('constants.hepsiburada.getAllCategories'),);
        return $result;
    }
    public function productStatusInquiry()
    {
        $result=HepsiburadaHelper::getRequestResult('get',config('constants.hepsiburada.getAllCategories'),);
        return $result;
    }
    
}
