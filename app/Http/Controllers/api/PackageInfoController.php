<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\AddPackageRequest;
use App\Http\Requests\KerryPackageInfoRequest;
use App\Repositories\PackageInfoRepository;
use App\Repositories\TrackingPickUpReceiptRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * @resource 包裹運送狀態
 *
 * 物流包裹運送狀態相關API
 *
 */
class PackageInfoController extends Controller
{
    protected $postRepo;

    public function __construct(PackageInfoRepository $postRepo)
    {
        $this->postRepo = $postRepo;
    }

    /**
     * @api {get} /api/KerryPackageInfo   取得物流包裹列表
     * @apiVersion 1.0.0
     * @apiName KerryPackageInfo
     * @apiGroup Package
     * @apiDescription 取得物流包裹列表
     */

    /**
     * @return \Illuminate\Http\Response
     */
    public function index($communityId, $userId)
    {
        $posts = $this->postRepo->index();

        if (!$posts) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => $posts]);
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
     * @api {post} /api/KerryPackageInfo   新增物流包裹
     * @apiVersion 1.0.1
     * @apiName KerryPackageInfo
     * @apiGroup Package
     * @apiDescription 新增物流包裹
     *
     * @apiParam {String} [piece] 件數
     * @apiParam {String} [carton_size] 材積
     */

    /**
     * @param  AddPackageRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddPackageRequest $request)
    {
        $data = $this->postRepo->create(
            $request->only(
                'tracking_id',
                'piece',
                'carton_id'
            )
        );

        if (!$data) {
            return response()->json(['errorCode' => 504, 'errorMessage' => 'create error'], 403);
        }

        return response()->json(['message' => 'create success.']);
    }

//    /**
//     * Display the specified resource.
//     *
//     * @param  int $id
//     * @return \Illuminate\Http\Response
//     */
//    public function show($id)
//    {
//        // 關閉get data from id
//
//        $post = $this->postRepo->find($id);
//
//        if (!$post) {
//            return response()->json(['data' => null]);
//        }
//
//        $trackingDataId = array_get($post, 'tracking_pick_up_id');
//        $trackingNumber = array_get($post, 'tracking_number');
//
//        // get customer_no
//        $trackingData = (new \App\Repositories\TrackingPickUpReceiptRepository)->find($trackingDataId);
//        $customerNo = array_get($trackingData, 'customer_no');
//
//        $data = self::getPackageStatusByTrackingId($trackingDataId, $customerNo, $trackingNumber);
//
//        if ($data == null) {
//            return response()->json(['data' => $post]);
//        }
//
//        return response()->json(['data' => $this->postRepo->find($id)]);
//    }

//    /**
//     * Show the form for editing the specified resource.
//     *
//     * @param  int  $id
//     * @return \Illuminate$postTrackingRequest\Http\Response
//     */
//    public function edit($id)
//    {
//        //
//    }

    /**
     * 更新物流包裹
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
     * 刪除物流包裹
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

    public static function findAsTrackingPickupId($id)
    {
        return DB::table('kerry_package_infos')
            ->select('*')
            ->where('tracking_id', '=', $id)
            ->join('kerry_package_carton_infos', 'kerry_package_infos.carton_id', '=', 'kerry_package_carton_infos.id')
            ->get();
    }

    public static function findAsPackageStatusById($trackingId)
    {
        return DB::table('kerry_package_statuses')
            ->select('*')
            ->where('tracking_id', '=', $trackingId)
            ->get();
    }

    public static function checkPackageStatusById($trackingId, $status)
    {
        return DB::table('kerry_package_statuses')
            ->select('id')
            ->where('tracking_id', '=', $trackingId)
            ->where('status', '=', $status)
            ->get();
    }

    public static function updateAsTrackingPickupId($id, $request)
    {
        return DB::table('kerry_package_infos')
            ->where('tracking_id', '=', $id)
            ->update($request);
    }

    /**
     * @api {get} /api/PackageInfoByTrackingId/:$trackingId   取得物流包裹配送狀態
     * @apiVersion 1.0.1
     * @apiName PackageInfoByTrackingId
     * @apiGroup Package
     * @apiDescription 取得物流包裹配送狀態
     *
     * @apiParam {Number} id Tracking unique ID.
     *
     */

    /**
     * @param $trackingId
     *      宅配單 Id
     * @return \Illuminate\Support\Collection
     *      列表
     */
    public static function getPackageInfoByTrackingId($communityId, $userId, $trackingId)
    {
        // get Tracking Number
        $trackingNumberData = TrackingPickUpReceiptController::getTrackingNumberById($trackingId);
        if (sizeof($trackingNumberData) > 0) {
            $trackingNumber = $trackingNumberData[0]->tracking_number;

                $isUpdateStatus = self::getPackageStatusById($trackingId, TrackingPickUpReceiptController::KERRY_CUSTOM_NO, $trackingNumber, false);
            if (!$isUpdateStatus) {
                return response()->json(['data' => array()]);
            } else {
                $selResponse = self::findAsPackageStatusById($trackingId);
                return response()->json(['data' => $selResponse]);
            }
        } else {
            return response()->json(['errorCode' => 505, 'errorMessage' => 'not find tracking number'], 403);
        }
        // 測試
//        return response()->json(['sqlData' => $result, 'tracking_id' => $trackingId, 'customer_no' => $customerNo, 'tracking_number' => $trackingNumber, 'data' => $isUpdateStatus]);
    }

    public static function getPackageStatusById($trackingId, $customerNo, $trackingNumber, $isJsonFormat)
    {
        $requestData = array();
        // mapping custom_no
        $requestData = array_add($requestData, 'customer_no', $customerNo);
        $requestData = array_add($requestData, 'tracking_number', $trackingNumber);

        $postTrackingRequest = (new KerryPickUpController)->postTrackingRequest($requestData);

        // post data to Kerry Tracking
        if (!$postTrackingRequest) {
            if ($isJsonFormat) {
                return response()->json(['errorCode' => 999, 'errorMessage' => $postTrackingRequest], 403);
            } else {
                return false;
            }
        }

        // response get JSON
        $jsonData = json_decode($postTrackingRequest, true);

        if ($jsonData['Data'] != null) {
            // rename
            $tempPackageStatus = array();

            foreach ($jsonData['Data'] as $item) {
                $receiveDateMapping = array_get($item, 'ReceiveDate').array_get($item, 'ReceiveTime');
                $receiveDate = date_create_from_format('YmdHis', $receiveDateMapping)->format('Y-m-d H:i:s');

                $tempPackageStatus = array_add($tempPackageStatus, 'tracking_id', $trackingId);
                $tempPackageStatus = array_add($tempPackageStatus, 'receive_date', $receiveDate);
                $tempPackageStatus = array_add($tempPackageStatus, 'status', array_get($item, 'Status'));
                $tempPackageStatus = array_add($tempPackageStatus, 'station', array_get($item, 'Station'));
                $tempPackageStatus = array_add($tempPackageStatus, 'message', array_get($item, 'Message'));

                $checkStatus = self::checkPackageStatusById($trackingId, array_get($item, 'Status'));
                if (count($checkStatus) == 0) {
                    $packageListDataFlag = (new \App\Repositories\PackageStatusRepository())->create($tempPackageStatus);
                    if (!$packageListDataFlag) {
                        return response()->json(['errorCode' => 508, 'errorMessage' => 'create package status error'], 404);
                    }
                } else {
                    (new \App\Repositories\PackageStatusRepository())->update(array_get($checkStatus, 'id'), $tempPackageStatus);
                }
            }

            return response()->json(['data' => self::findAsPackageStatusById($trackingId)]);
        } else {
            if ($isJsonFormat) {
                if ($jsonData['ErrorData'] != null) {
                    return response()->json(['errorCode' => 506, 'errorMessage' => $jsonData['ErrorData']], 404);
                } else {
                    return response()->json($postTrackingRequest, 404);
                }
            } else {
                return false;
            }
        }
    }

    /**
     * @api {post} /api/RefreshPackageInfo  更新物流包裹運送狀態 From 大榮
     * @apiVersion 1.0.1
     * @apiName RefreshPackageInfo
     * @apiGroup Package
     * @apiDescription 更新物流包裹運送狀態 From 大榮
     *
     * @apiParam {Number} id Tracking unique ID.
     *
     */

    /**
     * @param KerryPackageInfoRequest $request
     *      參數
     * @return \Illuminate\Http\JsonResponse
     *      列表
     */
    public static function refreshPackageInfo($communityId, $userId, KerryPackageInfoRequest $request)
    {
        $request->only(
            'tracking_id',
            'tracking_number',
            'kerry_beta_test'
        );

        $trackingId = $request->get('tracking_id');

        if (!$request) {
            return response()->json(['errorCode' => 505, 'errorMessage' => '參數不足'], 403);
        }

        $request = self::getPackageStatusById($trackingId, TrackingPickUpReceiptController::KERRY_CUSTOM_NO, $request->get('tracking_number'), true);
        if (!$request) {
            return response()->json(['data' => array()]);
        } else {
            return $request;
        }
    }

}
