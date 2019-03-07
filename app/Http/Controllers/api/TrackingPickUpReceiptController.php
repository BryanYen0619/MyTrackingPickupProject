<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\AddTrackingRequest;
use App\Repositories\TrackingPickUpReceiptRepository;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 *
 * @apiDefine KerryTrackingPickUpReceipt
 * 宅配單相關API
 *
 */
class TrackingPickUpReceiptController extends Controller
{
    // 大榮貨運測試用客戶編號
//    const KERRY_CUSTOM_NO = '';

    // 大榮貨運正式客戶編號
    const KERRY_CUSTOM_NO = '';

    protected $postRepo;

    public function __construct(TrackingPickUpReceiptRepository $postRepo)
    {
        $this->postRepo = $postRepo;
    }

    /**
     * @api {get} /api/KerryTrackingPickUpReceipt   取得宅配單列表
     * @apiVersion 1.0.1
     * @apiName KerryTrackingPickUpReceipt
     * @apiGroup Tracking
     * @apiDescription 取得宅配單列表
     *
     * @apiParam {Integer} [community_id] 社區ID
     * @apiParam {Integer} [user_id] 使用者ID
     *
     */

    /**
     * @return \Illuminate\Http\Response
     */
    public function index($communityId, $userId)
    {
        $sqlData = $this->postRepo->index()
            ->where('community_id', '=', $communityId)
            ->where('user_id', '=', $userId);

        // SQL 搜尋結果Format
        $tempData = array();
        $i = 0;
        foreach ($sqlData as $item) {
            $tempData[$i] = $item;
            $i++;
        }

        return TrackingPickUpReceiptController::mappingPackageDataByList($tempData);
    }

//    /**
//     * Show the form for creating a new resource.
//     *
//     * @return \Illuminate\Http\Response
//     */
//    public function create()
//    {
//        //
//    }

    /**
     * @api {post} /api/KerryTrackingPickUpReceipt   新增宅配單
     * @apiVersion 1.0.0
     * @apiName KerryTrackingPickUpReceipt
     * @apiGroup Tracking
     * @apiDescription 新增宅配單
     *
     * @apiParam {Integer} [community_id] 社區ID
     * @apiParam {Integer} [user_id] 使用者ID
     * @apiParam {String} [pick_up_no] 取件編號
     * @apiParam {String} [shipper] 寄件人姓名
     * @apiParam {String} [shipper_phone] 寄件人電話
     * @apiParam {String} [shipper_post] 寄件人郵遞區號
     * @apiParam {String} [shipper_address] 寄件人地址
     * @apiParam {String} [consignee] 收件人姓名
     * @apiParam {String} [consignee_phone] 收件人電話
     * @apiParam {String} [consignee_post] 收件人郵遞區號
     * @apiParam {String} [consignee_address] 收件人地址
     * @apiParam {String} [transport_date] 指送日期
     * @apiParam {String} [delivery_period] 希望配送時段
     * @apiParam {String} [remark] 備註
     * @apiParam {JSON} [pickup_content] 包裹列表
     *
     */

    /**
     * @param  AddTrackingRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $communityId, $userId)
    {
        $originData = $request->only(
            'shipper',
            'shipper_phone',
            'shipper_post',
            'shipper_address',
            'consignee',
            'consignee_phone',
            'consignee_post',
            'consignee_address',
            'transport_date',
            'delivery_period',
            'tax_id_number',
            'remark',
            'pickup_content'
        );

        $originData = array_add($originData, 'customer_no', self::KERRY_CUSTOM_NO);    //  顧客編號

        $pickUpNo = Carbon::now()->timestamp;
        $originData = array_add($originData, 'pick_up_no', $pickUpNo);

        $checkDeliveryPeriod = $request->get('delivery_period');
        if ($checkDeliveryPeriod != '1' && $checkDeliveryPeriod != '2' && $checkDeliveryPeriod != '4') {
            return response()->json(['errorCode' => 507, 'errorMessage' => 'delivery_period資料錯誤{ 1:上午(08-12), 2:下午(14-19), 4:不指定 }'], 404);
        }

        // 計算總金額
        $totalAmount = 0;

        // insert package_info table
        $packageList = $originData['pickup_content'];
        foreach ($packageList as $item) {
            $count = (int)$item['piece'];
            $totalAmount += $count * $item['price'];
        }

        $originData = array_add($originData, 'total_amount', $totalAmount);    // 夾帶總金額

        // post data to Kerry
        $postPickUpRequest = (new KerryPickUpController)->postPickUpRequest($originData);
        if (!$postPickUpRequest) {
            return response()->json(['errorCode' => 999, 'errorMessage' => $postPickUpRequest], 403);
        }

        // response get JSON
        $jsonData = json_decode($postPickUpRequest, true);

        if ($jsonData['Data'] != null) {
            // Add Data
            $originData = array_add($originData, 'community_id', $communityId);
            $originData = array_add($originData, 'user_id', $userId);
            $originData = array_add($originData, 'tracking_number', $jsonData['Data'][0]['BLN']);
            // insert tracking_pick_up table
            $trackingPickUpListDataFlag = $this->postRepo->create($originData);
            if (!$trackingPickUpListDataFlag) {
                return response()->json(['errorCode' => 508, 'errorMessage' => 'create tracking error'], 404);
            }

            // insert package_info table
            $packageList = $originData['pickup_content'];
            foreach ($packageList as $item) {
                $item = array_add($item, 'tracking_id', $trackingPickUpListDataFlag->id);

                $packageListDataFlag = (new \App\Repositories\PackageInfoRepository)->create($item);

                if (!$packageListDataFlag) {
                    return response()->json(['errorCode' => 508, 'errorMessage' => 'create package error'], 404);
                }
            }

            // 更新總金額
            self::updateTotalAmountById($trackingPickUpListDataFlag->id, $totalAmount);

        } else {
            if ($jsonData['ErrorData'] != null) {
                return response()->json(['errorCode' => 506, 'errorMessage' => $jsonData['ErrorData']], 404);
            } else {
                return response()->json(['errorCode' => 507, 'errorMessage' => $postPickUpRequest], 404);
            }
        }

        // 日期格式轉換
        $responseDate = date_create($jsonData['ReceiveDate']);
        $formatDateDate = date_format($responseDate, "Y-m-d H:i:s");

        return response()->json(['pick_up_no' => $jsonData['Data'][0]['PICKUP_NO'], 'tracking_number' => $jsonData['Data'][0]['BLN'], 'created_at' => $formatDateDate, 'pickup_content' => count($packageList)]);
    }

    /**
     * @api {get} /api/KerryTrackingPickUpReceipt/:$id   取得宅配單
     * @apiVersion 1.0.0
     * @apiName KerryTrackingPickUpReceipt
     * @apiGroup Tracking
     * @apiDescription 取得宅配單
     *
     * @apiParam {Number} id Tracking unique ID.
     *
     */

    /**
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = $this->postRepo->find($id);

        if (!$post) {
            return response()->json(['status' => 1, 'message' => 'post not found'], 404);
        }

        return TrackingPickUpReceiptController::mappingPackageInfoData($post);

    }

//    /**
//     * Show the form for editing the specified resource.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function edit($id)
//    {
//        //
//    }

    /**
     *
     * 更新宅配單
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $post = $this->postRepo->find($id);

        if (!$post) {
            return response()->json(['status' => 1, 'message' => 'post not found'], 404);
        }

        return response()->json(['status' => 0, 'post' => $post]);
    }

    /**
     *
     * 刪除宅配單
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->postRepo->destroy($id);

        if (!$result) {
            return response()->json(['status' => 1, 'message' => 'post not found'], 404);
        }

        return response()->json(['status' => 0, 'message' => 'success']);
    }

    private static function mappingPackageDataByList($trackingPickUpReceiptsList)
    {
        $outputDataList = $trackingPickUpReceiptsList;

        // set pickup_content data From KerryPackageInfo
        foreach ($outputDataList as $trackingData) {
            array_add($trackingData, 'pickup_content', PackageInfoController::findAsTrackingPickupId($trackingData->id));
        }

        // set package_status data From KerryPackageStatus
        foreach ($outputDataList as $trackingData) {
            array_add($trackingData, 'package_status', PackageInfoController::findAsPackageStatusById($trackingData->id));
        }

        return response()->json(['data' => $outputDataList]);
    }

    private static function mappingPackageInfoData($trackingPickUpReceipts)
    {
        $outputData = $trackingPickUpReceipts;

        // set pickup_content data From KerryPackageInfo
        array_add($outputData, 'pickup_content', PackageInfoController::findAsTrackingPickupId($outputData->id));

        return response()->json(['data' => $outputData]);
    }

    public static function getTrackingNumberById($id)
    {
        return DB::table('kerry_tracking_pick_up_receipts')
            ->select('tracking_number')
            ->where('id', '=', $id)
            ->get();
    }

    public static function updateTotalAmountById($id, $totalAmount)
    {
        return DB::table('kerry_tracking_pick_up_receipts')
            ->where('id', '=', $id)
            ->update(['total_amount' => $totalAmount]);
    }

}
