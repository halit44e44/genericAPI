<?php

namespace App\Http\Controllers\OldApi;

use App\Helpers\OldApiFunctionsHelper;
use App\Helpers\SmsFunctionsHelper;
use App\Http\Controllers\Controller;
use App\Models\Epin\EpinProductEntities;
use App\Models\Epin\EpinProducts;
use App\Models\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SebastianBergmann\CodeCoverage\Percentage;

class OldApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->companyInfo = OldApiFunctionsHelper::getCompanyInfo($request);
        $this->companyProducts = $this->companyInfo->products->pluck('product_id');
        $this->companyProductsPercentage = $this->companyInfo->products->pluck('percentage', 'product_id');
    }

    function getGameList(Request $request)
    {

        $data = [];
        $rowData = EpinProducts::where('status', 1)
            //->where('id',1)
            ->with(['entities'])
            ->whereIN('id', $this->companyProducts)
            ->whereNull('deleted_at')->get();
            //dd($rowData);
        if (count($rowData) > 0) {
            $x = 0;
            foreach ($rowData as $item) {
                $data[$x] = [
                    'Id' => $item->old_id,
                    'Name' => $item->title,
                    'Description' => $item->description,
                    'ImageUrl' => $item->imgUrl,
                    'GameItemsViewModel' => [],
                ];
                foreach ($item->entities as $entity) {
                    $data[$x]['GameItemsViewModel'][] = [
                        'Id' => $entity->old_id,
                        'StockCode' => $entity->stock_code,
                        'GameId' => $item->old_id,
                        'Name' => $entity->title,
                        'Description' => $entity->description,
                        'Price' => $entity->price,
                        'Percentage' => floatval($this->companyProductsPercentage[$item->id]),
                        'Stock' => 999,
                    ];
                }
                $x++;
            }
        }
        $result = OldApiFunctionsHelper::objectFormat(['GameViewModel' => $data], 'GameDto');
        return $result;
    }
    function getCategoryList(Request $request)
    {
        $data = [];
        $rowData = EpinProducts::where('status', 1)
            ->whereIN('id', $this->companyProducts)
            ->whereNull('deleted_at')->get();
            
        if (count($rowData) > 0) {
            foreach ($rowData as $item) {
                $data[] = [
                    'Id' => $item->old_id,
                    'Name' => $item->title,
                    'Description' => $item->description,
                    'ImageUrl' => $item->imgUrl,
                ];
            }
        }
        $result = OldApiFunctionsHelper::objectFormat(['GameViewModel' => $data], 'GameDto');
        return $result;
    }

    function gameItemListById(Request $request)
    {
        $data = [];
        if ($request->get('id')) {
            $rowData = EpinProducts::where('status', 1)
                ->where('old_id', $request->get('id'))
                ->whereIN('id', $this->companyProducts)
                ->whereHas('entities')
                ->whereNull('deleted_at')->first();
            if ($rowData && count($rowData->entities) > 0) {
                foreach ($rowData->entities as $entity) {
                    $data[] = [
                        'Id' => $entity->old_id,
                        'StockCode' => $entity->stock_code,
                        'GameId' => $request->get('id'),
                        'Name' => $entity->title,
                        'Description' => $entity->description,
                        'Price' => $entity->price,
                        'Percentage' => floatval($this->companyProductsPercentage[$rowData->id]),
                        'Stock' => 999,
                    ];
                }
            }
            $result = OldApiFunctionsHelper::objectFormat($data, 'GameDto');
            return $result;
        }
    }
    function saveOrder(Request $request)
    {
        if ($request->get('ClientServiceData')) {
            return 123;
        }
        $validator = Validator::make($request->json()->all(), [
            'TransactionId' => 'required|integer',
            'StockCode' => 'required',
            'PhoneNumber' => 'required',
            'Email' => '',
            'Quantity' => 'required|integer',
        ],);
        if ($validator->fails()) {
            $result = OldApiFunctionsHelper::errorFunction('JSON hatalı.', -200);
            return $result;
        }
        OldApiFunctionsHelper::checkOrderId($request->get('TransactionId'),$this->companyInfo->id);
        $productEnitity = OldApiFunctionsHelper::productEntityCheck($request->get('StockCode'), $this->companyInfo, $request);
        OldApiFunctionsHelper::companyStockCheck($this->companyInfo, $productEnitity, $request);
        $data = OldApiFunctionsHelper::makeOrder($this->companyInfo, $productEnitity, $request);

        if ($data['controlle']) {
            $result = OldApiFunctionsHelper::objectFormat([], '', true, 'İşleminiz kontrol ediliyor', 103);
        } else if ($data['preOrder']) {
            $result = OldApiFunctionsHelper::objectFormat($data['pins'], '', true, 'İşlem başarıyla gerçekleşti!', 301);
        } else {
            $result = OldApiFunctionsHelper::objectFormat($data['pins'], 'PinCode', true, 'İşlem başarıyla gerçekleşti!', 100);
            SmsFunctionsHelper::sendMessage($request->get('PhoneNumber'), json_encode($data['pins']));
        }
        return $result;
    }
    function rualive()
    {
        $result = OldApiFunctionsHelper::objectFormat([], 'PinCode', true, 'I am alive', 100);
        return $result;
    }
    function getBalance()
    {
        $result = OldApiFunctionsHelper::objectFormat([$this->companyInfo->ballance], 'Balance', true, 'İşlem Başarılı', 100);
        return $result;
    }
    function checkOrderProduct(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'StockCode' => 'required',
        ],);
        if ($validator->fails()) {
            $result = OldApiFunctionsHelper::errorFunction('JSON hatalı.', -200);
            return $result;
        }
        OldApiFunctionsHelper::productEntityCheck($request->get('StockCode'), $this->companyInfo, []);
        $data = [
            'Price' => EpinProductEntities::select(['price'])->where('stock_code', $request->get('StockCode'))->first()->price,
            'MaxQtyPerOrder' => ($this->companyInfo->maxTransactionLimit == 0) ? 999 : $this->companyInfo->maxTransactionLimit,
        ];
        $result = OldApiFunctionsHelper::objectFormat([], '', true, 'İşlem başarıyla gerçekleşti!', 100, $data);
        return $result;
    }
    function checkPin(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'QueryID' => 'required',
            'PinCode' => 'required',
        ],);
        if ($validator->fails()) {
            $result = OldApiFunctionsHelper::errorFunction('JSON hatalı.', -200);
            return $result;
        }
        $result = OldApiFunctionsHelper::objectFormat([], '', true, 'Kontrol Ediliyor', 0, []);
        return $result;
    }
    function orderReportDetail(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'startDate' => 'required',
            'endDate' => 'required',
        ],);
        if ($validator->fails()) {
            $result = OldApiFunctionsHelper::errorFunction('JSON hatalı.', -200);
            return $result;
        }
        $data = [];
        $startDate=explode('-',$request->get('startDate'));
        $endDate=explode('-',$request->get('endDate'));
        //dd($startDate);
        $rowData = Transactions::whereNull('deleted_at')
            ->where('company_id', $this->companyInfo->id)
            ->where('created_at','>=',$startDate[0].'-'.$startDate[2].'-'.$startDate[1].'%')
            ->where('created_at','<=',$endDate[0].'-'.$endDate[2].'-'.$endDate[1].'%')
            ->orderBy('created_at', 'desc')->get();
        if (count($rowData) > 0) {
            $x=0;
            foreach ($rowData as $item) {
                $data[] = [
                    'Id' => ++$x,
                    'OrderId' => intval($item->transactionId),
                    'ReferenceId' => '-',
                    'Name' => $item->productEntity->title,
                    'StockCode' => $item->productEntity->stock_code,
                    'Quantity' => $item->qty,
                    'Amount' => $item->percentage_total_price,
                    'Commission' => $item->percentage,
                    'TotalAmaount' => $item->percentage_total_price,
                    'OrderStatus' => $item->status,
                    'OrderDate' => Carbon::parse($item->created_at)->format('Y-d-m H:i:s'),
                    'PhoneNumber' => $item->clientInfo->phone,
                    'EmailAdress' => $item->clientInfo->email,
                ];
            }
        }
        $result = OldApiFunctionsHelper::objectFormat($data, 'OrderList', true, 'İşlem başarıyla gerçekleşti!', 100);
        return $result;
    }
}
