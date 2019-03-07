<?php
/**
 * Created by PhpStorm.
 * User: bryan.yen
 * Date: 2018/11/15
 * Time: 11:44 AM
 */

namespace App\Http\Controllers\api;

use App\Repositories\PackageCartonInfoRepository;
use App\Http\Controllers\Controller;

/**
 * @resource 大榮材積規格
 *
 * 物流包裹材積規格相關API
 *
 */
class PackageCartonInfoController extends Controller
{
    protected $postRepo;

    public function __construct(PackageCartonInfoRepository $postRepo)
    {
        $this->postRepo = $postRepo;
    }

    /**
     * @api {get} /api/KerryPackageCartonInfo   取得物流包裹材積規格
     * @apiVersion 1.0.0
     * @apiName KerryPackageCartonInfo
     * @apiGroup Package
     * @apiDescription 取得物流包裹材積規格
     */

    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = $this->postRepo->index();

        if (!$posts) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => $posts]);
    }
}