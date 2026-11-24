<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>ক্যাশ মেমো #{{ $sale->invoice_no }} | {{ tenant('shop_name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace, -apple-system, sans-serif;
            margin: 0;
            padding: 10px;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
        }

        .receipt-container {
            width: 80mm;
            background: #ffffff;
            padding: 12px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 12px;
            line-height: 1.3;
            color: #000000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-weight-bold { font-weight: bold; }

        .shop-title {
            font-size: 16px;
            font-weight: 900;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .shop-info {
            font-size: 11px;
            margin-bottom: 6px;
        }

        .divider {
            border-top: 1px dashed #000000;
            margin: 6px 0;
        }

        .meta-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .items-table th {
            border-bottom: 1px solid #000;
            padding: 4px 2px;
            font-weight: 700;
        }

        .items-table td {
            padding: 4px 2px;
            vertical-align: top;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 2px 0;
        }

        .grand-total {
            font-size: 14px;
            font-weight: 900;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 0;
            margin: 4px 0;
        }

        .barcode-container {
            margin-top: 8px;
            text-align: center;
        }

        .barcode-container svg {
            max-width: 90%;
            height: auto;
            max-height: 38px;
        }

        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #1572e8;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-weight: bold;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .receipt-container {
                width: 100% !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 2mm;
                size: 80mm auto;
            }
        }
    </style>
</head>
<body>

    <button class="no-print" onclick="window.print()">🖨️ প্রিন্ট করুন</button>

    <div class="receipt-container">
        <!-- Header -->
        <div class="text-center">
            <div class="shop-title">{{ tenant('shop_name') ?? 'OmniMart Store' }}</div>
            <div class="shop-info">
                {{ tenant('address') ?? 'দোকানের ঠিকানা' }}<br>
                মোবাইল: {{ tenant('phone') ?? '০১৭xxxxxxxx' }}
            </div>
            <div class="font-weight-bold" style="font-size: 13px;">*** ক্যাশ মেমো ***</div>
        </div>

        <div class="divider"></div>

        <!-- Invoice Details -->
        <table class="meta-table">
            <tr>
                <td><b>চালান নং:</b> {{ $sale->invoice_no }}</td>
                <td class="text-right"><b>তারিখ:</b> {{ $sale->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><b>কাস্টমার:</b> {{ $sale->customer_name }}</td>
                <td class="text-right"><b>সময়:</b> {{ $sale->created_at->format('h:i A') }}</td>
            </tr>
            @if($sale->customer_phone)
            <tr>
                <td colspan="2"><b>ফোন:</b> {{ $sale->customer_phone }}</td>
            </tr>
            @endif
            @if($sale->creator)
            <tr>
                <td colspan="2"><b>ক্যাশিয়ার:</b> {{ $sale->creator->name }}</td>
            </tr>
            @endif
        </table>

        <div class="divider"></div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 45%;">বিবরণ</th>
                    <th class="text-right" style="width: 15%;">দর</th>
                    <th class="text-center" style="width: 15%;">পরিমাণ</th>
                    <th class="text-right" style="width: 25%;">মোট (৳)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-right">{{ number_format((float)$item->unit_price, 2) }}</td>
                    <td class="text-center">{{ (float)$item->quantity }}</td>
                    <td class="text-right">{{ number_format((float)$item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Summary -->
        <div class="summary-row">
            <span>মোট আইটেম:</span>
            <span>{{ $sale->items->count() }} টি</span>
        </div>
        <div class="summary-row">
            <span>উপমোট (Subtotal):</span>
            <span>৳{{ number_format((float)$sale->total_amount + (float)$sale->discount_amount - (float)$sale->tax_amount, 2) }}</span>
        </div>
        @if((float)$sale->discount_amount > 0)
        <div class="summary-row">
            <span>ছাড় (Discount):</span>
            <span>-৳{{ number_format((float)$sale->discount_amount, 2) }}</span>
        </div>
        @endif
        @if((float)$sale->tax_amount > 0)
        <div class="summary-row">
            <span>ভ্যাট / ট্যাক্স:</span>
            <span>+৳{{ number_format((float)$sale->tax_amount, 2) }}</span>
        </div>
        @endif

        <div class="summary-row grand-total">
            <span>সর্বমোট বিল:</span>
            <span>৳{{ number_format((float)$sale->total_amount, 2) }}</span>
        </div>

        <div class="summary-row">
            <span>পরিশোধ ({{ ucfirst($sale->payment_method) }}):</span>
            <span>৳{{ number_format((float)$sale->paid_amount, 2) }}</span>
        </div>

        @if((float)$sale->due_amount > 0)
        <div class="summary-row font-weight-bold" style="color: #000;">
            <span>বকেয়া (Due):</span>
            <span>৳{{ number_format((float)$sale->due_amount, 2) }}</span>
        </div>
        @endif

        <div class="divider"></div>

        <!-- Barcode of Invoice -->
        <div class="barcode-container">
            {!! $barcodeSvg !!}
            <div style="font-size: 10px; margin-top: 2px;">{{ $sale->invoice_no }}</div>
        </div>

        <!-- Footer -->
        <div class="text-center" style="margin-top: 10px; font-size: 11px;">
            ধন্যবাদ, আবার আসবেন!<br>
            <small style="font-size: 9px; opacity: 0.8;">Powered by OmniMart Multi-Tenant POS</small>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
