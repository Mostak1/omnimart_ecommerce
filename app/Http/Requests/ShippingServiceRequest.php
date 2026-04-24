<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingServiceRequest extends FormRequest
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
        return  [
           'title' => 'required|max:255',
           'price'  => 'nullable|numeric|max:9999999999',
           'minimum_price' => 'nullable|numeric|max:9999999999',
           'default_base_shipping_charge' => 'nullable|numeric|max:9999999999',
           'default_per_kg_extra_charge' => 'nullable|numeric|max:9999999999',
           'district_base_shipping_charge.*' => 'nullable|numeric|max:9999999999',
           'district_per_kg_extra_charge.*' => 'nullable|numeric|max:9999999999',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => __('Title field is required.'),
        ];
    }
}
