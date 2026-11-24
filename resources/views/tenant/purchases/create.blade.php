@extends('master.tenant')

@section('title', 'নতুন পাইকারি ক্রয় চালান এন্ট্রি')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="page-title mb-1 font-weight-bold">
            <i class="fas fa-truck-loading text-success mr-2"></i> নতুন পাইকারি ক্রয় চালান এন্ট্রি
        </h4>
        <p class="text-muted mb-0 font-14">সাপ্লায়ার থেকে মাল ক্রয়ের তথ্য দিলে স্বয়ংক্রিয়ভাবে দোকানের স্টক বেড়ে যাবে</p>
    </div>
    <div>
        <a href="{{ route('tenant.purchases.index') }}" class="btn btn-secondary font-weight-bold">
            <i class="fas fa-arrow-left mr-1"></i> চালানের তালিকায় ফিরুন
        </a>
    </div>
</div>

<form action="{{ route('tenant.purchases.store') }}" method="POST" id="purchaseForm">
    @csrf
    <div class="row">
        <!-- Left: Supplier Info & Purchase Details -->
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light font-weight-bold">
                    <i class="fas fa-user-tie text-primary mr-1"></i> সাপ্লায়ার ও চালান তথ্য
                </div>
                <div class="card-body">
                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">সাপ্লায়ার / মহাজনের নাম <span class="text-danger">*</span></label>
                        <input type="text" name="supplier_name" class="form-control" placeholder="যেমন: মেসার্স রহিম ট্রেডার্স" required value="{{ old('supplier_name') }}">
                    </div>

                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">সাপ্লায়ারের মোবাইল নম্বর</label>
                        <input type="text" name="supplier_phone" class="form-control" placeholder="০১XXXXXXXXX" value="{{ old('supplier_phone') }}">
                    </div>

                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">সাপ্লায়ার ইনভয়েস / মেমো নং</label>
                        <input type="text" name="invoice_no" class="form-control" placeholder="ফাঁকা রাখলে অটো জেনারেট হবে" value="{{ old('invoice_no') }}">
                    </div>

                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">চালানের তারিখ</label>
                        <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">মন্তব্য / নোট (ঐচ্ছিক)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="চালানের কোনো বিশেষ তথ্য..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Items Selection, Calculations & Submit -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold"><i class="fas fa-boxes text-success mr-1"></i> ক্রয়কৃত পণ্যের তালিকা</span>
                    <button type="button" class="btn btn-sm btn-primary" id="btnAddRow">
                        <i class="fas fa-plus mr-1"></i> পণ্য যোগ করুন
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="itemsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th style="min-width: 220px;">পণ্য নির্বাচন করুন <span class="text-danger">*</span></th>
                                    <th style="width: 130px;">ক্রয় দর (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 120px;">পরিমাণ <span class="text-danger">*</span></th>
                                    <th style="width: 130px;" class="text-right">মোট (৳)</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Box -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="font-weight-bold small text-muted">সর্বমোট বিল (Total):</label>
                            <h3 class="font-weight-bold text-dark mb-0" id="grandTotalDisplay">৳0.00</h3>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="font-weight-bold small text-success">জমা দেওয়া টাকা (Paid Amount):</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" class="form-control font-weight-bold" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="font-weight-bold small text-danger">সাপ্লায়ারের পাওনা / বাকি (Due):</label>
                            <h3 class="font-weight-bold text-danger mb-0" id="dueAmountDisplay">৳0.00</h3>
                        </div>
                    </div>

                    <hr class="my-3">

                    <button type="submit" class="btn btn-success btn-lg btn-block font-weight-bold py-3 shadow">
                        <i class="fas fa-check-circle mr-2"></i> চালান সংরক্ষণ ও স্টক বৃদ্ধি করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    const availableProducts = @json($products);
    let rowIndex = 0;

    function addRow(productId = '', cost = 0, qty = 1) {
        let options = '<option value="">-- পণ্য সিলেক্ট করুন --</option>';
        availableProducts.forEach(p => {
            let selected = (p.id == productId) ? 'selected' : '';
            options += `<option value="${p.id}" data-cost="${p.cost_price}" data-unit="${p.unit || 'pcs'}" ${selected}>${p.name} (মজুদ: ${p.stock_quantity})</option>`;
        });

        let lineTotal = (cost * qty).toFixed(2);

        let rowHtml = `
        <tr id="row-${rowIndex}">
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-control product-select" required onchange="onProductSelect(${rowIndex}, this)">
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" class="form-control text-right unit-cost-input" value="${cost}" required min="0" oninput="calculateRow(${rowIndex})">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="items[${rowIndex}][quantity]" class="form-control text-center quantity-input" value="${qty}" required min="0.01" oninput="calculateRow(${rowIndex})">
                    <div class="input-group-append">
                        <span class="input-group-text row-unit" id="unit-${rowIndex}">pcs</span>
                    </div>
                </div>
            </td>
            <td class="text-right font-weight-bold line-total" id="total-${rowIndex}">
                ৳${lineTotal}
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeRow(${rowIndex})">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>`;

        $('#itemsTableBody').append(rowHtml);
        rowIndex++;
        calculateOverallTotals();
    }

    function onProductSelect(idx, selectEl) {
        let $selected = $(selectEl).find(':selected');
        let cost = parseFloat($selected.data('cost')) || 0;
        let unit = $selected.data('unit') || 'pcs';

        $(`#row-${idx} .unit-cost-input`).val(cost);
        $(`#unit-${idx}`).text(unit);
        calculateRow(idx);
    }

    function calculateRow(idx) {
        let cost = parseFloat($(`#row-${idx} .unit-cost-input`).val()) || 0;
        let qty = parseFloat($(`#row-${idx} .quantity-input`).val()) || 0;
        let total = (cost * qty).toFixed(2);
        $(`#total-${idx}`).text('৳' + total);
        calculateOverallTotals();
    }

    function removeRow(idx) {
        if ($('#itemsTableBody tr').length > 1) {
            $(`#row-${idx}`).remove();
            calculateOverallTotals();
        } else {
            alert('চালানে অন্তত একটি পণ্য থাকা আবশ্যক!');
        }
    }

    function calculateOverallTotals() {
        let grandTotal = 0;
        $('#itemsTableBody tr').each(function() {
            let cost = parseFloat($(this).find('.unit-cost-input').val()) || 0;
            let qty = parseFloat($(this).find('.quantity-input').val()) || 0;
            grandTotal += (cost * qty);
        });

        $('#grandTotalDisplay').text('৳' + grandTotal.toFixed(2));

        let paid = parseFloat($('#paidAmountInput').val()) || 0;
        let due = Math.max(0, grandTotal - paid);
        $('#dueAmountDisplay').text('৳' + due.toFixed(2));
    }

    $('#paidAmountInput').on('input', function() {
        calculateOverallTotals();
    });

    $('#btnAddRow').click(function() {
        addRow();
    });

    // Add initial row on load
    $(document).ready(function() {
        if (availableProducts.length > 0) {
            addRow(availableProducts[0].id, availableProducts[0].cost_price, 10);
        } else {
            addRow();
        }
    });
</script>
@endsection
