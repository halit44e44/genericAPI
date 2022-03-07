<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->post('auth/login', 'AuthorizationController@authenticate');
$router->post('auth/refresh', 'AuthorizationController@refreshToken');


$router->group(
    ['middleware' => 'jwt.auth'],
    function () use ($router) {
        $router->group(['prefix' => 'genba'], function () use ($router) {

            $router->get('ping', 'Genba\PingController@checkConnection');

            $router->group(['prefix' => 'products'], function () use ($router) {
                $router->get('/', 'Genba\ProductsController@index');
                $router->get('/productsData', 'Genba\ProductsController@allProductsData');
                $router->get('/single', 'Genba\ProductsController@single');
                $router->get('/gemeList', 'Genba\ProductsController@gameList');

            });
            $router->group(['prefix' => 'prices'], function () use ($router) {
                $router->get('/', 'Genba\PricesController@index');
                $router->get('/singleSku', 'Genba\PricesController@singleSku');
                $router->get('/singleId', 'Genba\PricesController@singleId');
            });

            $router->get('promotions', 'Genba\PromotionsController@index');
            $router->group(['prefix' => 'orders'], function () use ($router) {
                $router->post('/create', 'Genba\OrderController@create');
                $router->get('/getById', 'Genba\OrderController@getById');
                $router->get('/getByCtid', 'Genba\OrderController@getByCtid');
                $router->post('/return', 'Genba\OrderController@returnOrder');
            });
            $router->group(['prefix' => 'reservations'], function () use ($router) {
                $router->post('/create', 'Genba\ReservationsController@create');
                $router->get('/getById', 'Genba\ReservationsController@getById');
                $router->post('/complete', 'Genba\ReservationsController@completeReservation');
            });
            $router->post('/token', 'Genba\OrderController@clientToken');
            $router->post('/getkey', 'Genba\OrderController@buyKey');

        });
        $router->group(['prefix' => 'epin'], function () use ($router) {
            $router->group(['prefix' => 'products'], function () use ($router) {
                $router->get('/', 'Epin\ProductsController@index');
                $router->get('/details', 'Epin\ProductsController@productsDetails');
                $router->post('/single', 'Epin\ProductsController@single');
            });
            $router->group(['prefix' => 'orders'], function () use ($router) {
                $router->group(['prefix' => 'reports'], function () use ($router) {
                    $router->post('/', 'Epin\OrdersReportController@orderReport');
                    $router->post('/details', 'Epin\OrdersReportController@details');
                    $router->post('/hourly', 'Epin\OrdersReportController@hourly');
                });
            });
           
        });


        $router->group(['prefix' => 'hepsiburada'], function () use ($router) {
            $router->post('/saveProducts', 'Hepsiburada\ProductController@saveProducts');
            $router->post('/productStatusInquiry', 'Hepsiburada\ProductController@productStatusInquiry');
            $router->get('/categoriesList', 'Hepsiburada\ProductController@categoriesList');
            $router->post('/listingCompletedOrders', 'Hepsiburada\OrderController@listingCompletedOrders');
            $router->post('/packingPen', 'Hepsiburada\OrderController@packingPen');
        });



        $router->get('/epinSite/price', 'EpinSiteController@index');
        $router->get('/epinSite/skuMatch', 'EpinSiteController@skuMatch');
        $router->get('products', 'ProductsController@index');
    }
);

$router->group(['prefix' => 'fra/apv2/'], function () use ($router) {

    $router->get('GetGameList', 'OldApi\OldApiController@getGameList');
    $router->get('GetCategoryList', 'OldApi\OldApiController@getCategoryList');
    $router->post('GameItemListById', 'OldApi\OldApiController@gameItemListById');
    $router->post('SaveOrder', 'OldApi\OldApiController@saveOrder');
    $router->post('SaveOrderExt', 'OldApi\OldApiController@saveOrder');
    $router->post('CheckOrderProduct', 'OldApi\OldApiController@checkOrderProduct');
    $router->post('CheckPin', 'OldApi\OldApiController@checkPin');
    $router->get('GetBalance', 'OldApi\OldApiController@getBalance');
    $router->post('OrderReportDetail', 'OldApi\OldApiController@orderReportDetail');

});
$router->get('rualive', 'OldApi\OldApiController@rualive');
