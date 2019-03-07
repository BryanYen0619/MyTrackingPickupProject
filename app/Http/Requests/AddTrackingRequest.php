<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTrackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pick_up_no'=> 'required|string',
            'shipper'=> 'required|string',
            'shipper_phone'=> 'required|string',
            'shipper_post'=> 'required|string',
            'shipper_address'=> 'required|string',
            'consignee'=> 'required|string',
            'consignee_phone'=> 'required|string',
            'consignee_post'=> 'required|string',
            'consignee_address'=> 'required|string',
            'transport_date'=> 'string',
            'delivery_period'=> 'required|string',
            'remark'=> 'string',
            'tracking_number'=> 'string',
            'pickup_content'=> 'required'
        ];
    }
}
