<?php

namespace App\Http\Requests;

use App\Helpers\PriceHelper;
use App\Models\ShippingService;
use App\Models\State;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        return  true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        if(PriceHelper::CheckDigital() == false){
            return [];
        }
        $state = 'nullable';
        
        $shipping = 'nullable';

        if($this->single_page_checkout == 1){
            return [
                'state_id' => $state,
                "shipping_id" => $shipping,
                'bill_first_name' => 'required',
                'bill_email' => 'nullable|email',
                'bill_phone' => 'required',
                'bill_address1' => 'required',
                'bill_country' => 'required',
                'bill_thana' => 'required',
            ];
        }else{
            return [
                'state_id' => $state,
                "shipping_id" => $shipping,
            ];
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'state_id.required'   => __('Please select your shipping state.'),
            'shipping_id.required'   => __('Please select your shipping method.'),
        ];
    }

}
