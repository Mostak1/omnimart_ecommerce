<?php

namespace Tests\Unit;

use App\Http\Requests\PaymentRequest;
use Tests\TestCase;

class PaymentRequestTest extends TestCase
{
    public function test_payment_request_rules_allow_nullable_district_and_thana(): void
    {
        session(['cart' => [
            1 => [
                'item_type' => 'normal',
                'main_price' => 100,
                'attribute_price' => 0,
                'qty' => 1,
            ],
        ]]);

        $request = new PaymentRequest();
        $request->merge([
            'single_page_checkout' => 1,
        ]);

        $rules = $request->rules();

        $this->assertEquals('nullable', $rules['bill_country'] ?? null);
        $this->assertEquals('nullable', $rules['bill_thana'] ?? null);
    }

    public function test_payment_request_passes_validation_without_district_and_thana(): void
    {
        session(['cart' => [
            1 => [
                'item_type' => 'normal',
                'main_price' => 100,
                'attribute_price' => 0,
                'qty' => 1,
            ],
        ]]);

        $validator = \Illuminate\Support\Facades\Validator::make([
            'single_page_checkout' => 1,
            'bill_first_name' => 'John Doe',
            'bill_email' => 'john@example.com',
            'bill_phone' => '01700000000',
            'bill_address1' => 'Dhaka, Bangladesh',
        ], (new PaymentRequest())->merge(['single_page_checkout' => 1])->rules());

        $this->assertFalse($validator->fails());
    }
}
