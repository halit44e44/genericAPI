<?php

return [
    'genba' => [
        'ping' => 'ping',
        'products' => 'products',
        'prices' => 'prices',
        'promotions' => 'promotions',
        'orders' => 'orders',
        'ordersByCtid' => 'orders/ctid',
        'reservations' => 'reservations',
    ],
    'epin' => [
        'getGameList'=>'GetGameList',
        'getCategoryList'=>'GetCategoryList',
        'gameItemListById'=>'GameItemListById',
        'orderReport'=>'OrderReport',
        'orderReportDetail'=>'OrderReportDetail',
        'checkOrderProduct'=>'CheckOrderProduct',
        'checkPin'=>'CheckPin',
        'saveOrder'=>'SaveOrder',
        'saveOrderExt'=>'SaveOrderExt',
        'getBalance'=>'GetBalance',
    ],


    'hepsiburada'=>[
        'getAllCategories'=>'/product/api/categories/get-all-categories',
        'importProducts'=>'/product/api/products/import',
        'productStatusInquiry'=>'/product/api/products/status/d1ba0213-7795-4286-a372-5252b43c5fb7?page=0&size=20&version=1',
        'packingPen'=>'/packages/merchantid/'.env('HEPSIBURADA_ID'),
        'listingCompletedOrders'=>'/orders/merchantid/'.env('HEPSIBURADA_MERCHANT_ID'),
        'packingPen'=>'/packages/merchantid/'.env('HEPSIBURADA_MERCHANT_ID'),
        
    ]


];
