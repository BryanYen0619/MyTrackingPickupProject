<?php
/**
 * Created by PhpStorm.
 * User: bryan.yen
 * Date: 2018/9/27
 * Time: 上午11:50
 */

namespace App\Repositories;

use App\Entities\KerryTrackingPickUpReceipt;

class TrackingPickUpReceiptRepository
{
    public function index()
    {
        return KerryTrackingPickUpReceipt::orderBy('created_at','desc')->get(['id', 'community_id', 'user_id', 'pick_up_no', 'shipper', 'shipper_phone', 'shipper_post', 'shipper_address', 'consignee', 'consignee_phone', 'consignee_post', 'consignee_address', 'transport_date', 'delivery_period', 'tax_id_number', 'remark', 'tracking_number', 'total_amount', 'created_at', 'updated_at']);
    }

    public function create(array $data)
    {
        return KerryTrackingPickUpReceipt::create($data);
    }

    public function find($id)
    {
        return KerryTrackingPickUpReceipt::find($id);
    }

    public function delete($id)
    {
        return KerryTrackingPickUpReceipt::destroy($id);
    }

    public function update($id, array $data)
    {
        $post = KerryTrackingPickUpReceipt::find($id);

        if (!$post) {
            return false;
        }

        return $post->update($data);
    }
}