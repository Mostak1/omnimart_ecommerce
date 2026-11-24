<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>বারকোড লেবেল - {{ $product->name }} | {{ tenant('shop_name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('assets/back/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .no-print-toolbar {
            background: #ffffff;
            border-radius: 8px;
            padding: 15px 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: flex-start;
        }

        /* 38mm x 25mm barcode sticker format */
        .barcode-sticker {
            width: 220px;
            height: 140px;
            background: #ffffff;
            border: 1px dashed #ced4da;
            border-radius: 6px;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .sticker-shop-name {
            font-size: 11px;
            font-weight: 700;
            color: #333333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sticker-product-name {
            font-size: 12px;
            font-weight: 600;
            color: #111111;
            line-height: 1.2;
            max-width: 100%;
            height: 28px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .sticker-barcode-svg {
            width: 100%;
            max-height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sticker-barcode-svg svg {
            max-width: 100%;
            height: auto;
            max-height: 48px;
        }

        .sticker-price {
            font-size: 13px;
            font-weight: 800;
            color: #000000;
            line-height: 1;
            margin-top: 2px;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .labels-grid {
                gap: 6px;
            }
            .barcode-sticker {
                border: 1px dotted #ccc !important;
                margin: 0 !important;
            }
            @page {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- Controls / Action Toolbar -->
    <div class="no-print-toolbar d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h4 class="mb-1 font-weight-bold">
                <i class="fas fa-barcode text-primary mr-2"></i> বারকোড লেবেল প্রিন্টার
            </h4>
            <p class="text-muted mb-0 small">
                দোকান: <b>{{ tenant('shop_name') }}</b> | পণ্য: <b>{{ $product->name }}</b> (বারকোড: <code>{{ $barcodeCode }}</code>)
            </p>
        </div>

        <div class="d-flex align-items-center mt-3 mt-md-0">
            <form action="{{ route('tenant.products.print-barcode', $product->id) }}" method="GET" class="form-inline mr-3">
                <label class="mr-2 font-weight-bold small">কপি সংখ্যা:</label>
                <select name="qty" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="1" {{ $copies == 1 ? 'selected' : '' }}>১ টি লেবেল</option>
                    <option value="4" {{ $copies == 4 ? 'selected' : '' }}>৪ টি লেবেল</option>
                    <option value="12" {{ $copies == 12 ? 'selected' : '' }}>১২ টি লেবেল (১ পাতা)</option>
                    <option value="24" {{ $copies == 24 ? 'selected' : '' }}>২৪ টি লেবেল (২ পাতা)</option>
                    <option value="36" {{ $copies == 36 ? 'selected' : '' }}>৩৬ টি লেবেল</option>
                    <option value="48" {{ $copies == 48 ? 'selected' : '' }}>৪৮ টি লেবেল</option>
                </select>
            </form>

            <button type="button" class="btn btn-primary font-weight-bold mr-2" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> প্রিন্ট করুন
            </button>
            <button type="button" class="btn btn-secondary font-weight-bold" onclick="window.close()">
                <i class="fas fa-times mr-1"></i> বন্ধ করুন
            </button>
        </div>
    </div>

    <!-- Printable Barcode Stickers Grid -->
    <div class="labels-grid">
        @for($i = 0; $i < $copies; $i++)
            <div class="barcode-sticker">
                <div class="sticker-shop-name">{{ tenant('shop_name') ?? 'OmniMart Store' }}</div>
                <div class="sticker-product-name">{{ $product->name }}</div>
                <div class="sticker-barcode-svg">
                    {!! $barcodeSvg !!}
                </div>
                <div class="sticker-price">
                    মূল্য: ৳{{ number_format((float)$product->retail_price, 2) }}
                </div>
            </div>
        @endfor
    </div>

</body>
</html>

