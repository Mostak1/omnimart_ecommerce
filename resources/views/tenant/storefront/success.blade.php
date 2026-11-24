<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অর্ডার কনফার্মেশন | {{ $settings['shop_name'] ?? tenant('shop_name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .order-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            max-width: 550px;
            width: 100%;
            overflow: hidden;
            text-align: center;
        }
        .success-banner {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #ffffff;
            padding: 35px 20px 25px;
        }
    </style>
</head>
<body>

    <div class="order-card">
        <div class="success-banner">
            <div class="mb-2">
                <i class="fas fa-check-circle fa-4x"></i>
            </div>
            <h3 class="font-weight-bold mb-1">ধন্যবাদ! আপনার অর্ডার সম্পন্ন হয়েছে</h3>
            <p class="mb-0 small" style="opacity: 0.95;">অর্ডার ট্র্যাকিং আইডি / ইনভয়েস নম্বর:</p>
            <h4 class="font-weight-bold mt-1 mb-0 bg-white text-success d-inline-block px-3 py-1 rounded shadow-sm">
                {{ $sale->invoice_no }}
            </h4>
        </div>

        <div class="p-4 text-left">
            <h6 class="font-weight-bold border-bottom pb-2 mb-3 text-dark">
                <i class="fas fa-receipt text-primary mr-1"></i> অর্ডারের বিবরণী:
            </h6>

            <div class="table-responsive mb-3">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $item->product_name }} ({{ (float)$item->quantity }}x)</td>
                            <td class="text-right font-weight-bold">৳{{ number_format((float)$item->total, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="border-top font-weight-bold" style="font-size: 15px;">
                            <td>সর্বমোট বিল (ক্যাশ অন ডেলিভারি):</td>
                            <td class="text-right text-success">৳{{ number_format((float)$sale->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-light p-3 rounded mb-4 border">
                <div class="small"><b>গ্রাহকের নাম:</b> {{ $sale->customer_name }}</div>
                <div class="small"><b>মোবাইল নম্বর:</b> {{ $sale->customer_phone }}</div>
                <div class="small"><b>ডেলিভারি নোট:</b> {{ $sale->notes }}</div>
            </div>

            <div class="alert alert-info py-2 px-3 small text-center mb-4">
                <i class="fas fa-info-circle mr-1"></i> খুব শীঘ্রই আমাদের প্রতিনিধি আপনার ঠিকানায় পণ্য পৌঁছে দেওয়ার জন্য যোগাযোগ করবেন।
            </div>

            <div class="row">
                <div class="col-6 pr-1">
                    <a href="{{ url('/') }}" class="btn btn-primary btn-block font-weight-bold">
                        <i class="fas fa-shopping-bag mr-1"></i> আরও পণ্য দেখুন
                    </a>
                </div>
                <div class="col-6 pl-1">
                    @php $phone = $settings['phone'] ?? tenant('phone'); @endphp
                    @if($phone)
                        <a href="tel:{{ $phone }}" class="btn btn-outline-success btn-block font-weight-bold">
                            <i class="fas fa-phone-alt mr-1"></i> কল করুন
                        </a>
                    @else
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-block font-weight-bold">
                            হোমপেজ
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

</body>
</html>
