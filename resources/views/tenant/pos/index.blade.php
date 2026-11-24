@extends('master.tenant')

@section('title', 'পিওএস ও ক্যাশ মেমো (POS Billing)')

@section('styles')
<style>
    .pos-container {
        height: calc(100vh - 150px);
        min-height: 600px;
    }
    .pos-product-card {
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }
    .pos-product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #1572e8;
    }
    .pos-cart-panel {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .pos-cart-items {
        flex: 1;
        overflow-y: auto;
        max-height: calc(100vh - 430px);
    }
    .pos-cart-table th {
        background: #f8f9fa;
        font-size: 13px;
        font-weight: 700;
        border-top: none;
    }
    .pos-cart-table td {
        vertical-align: middle;
        font-size: 13px;
    }
    .qty-btn {
        width: 26px;
        height: 26px;
        padding: 0;
        font-size: 12px;
        font-weight: bold;
        line-height: 26px;
        text-align: center;
        border-radius: 4px;
    }
    .grand-total-box {
        background: linear-gradient(135deg, #1572e8, #125ac2);
        color: #ffffff;
        border-radius: 8px;
        padding: 12px 18px;
    }
    .payment-pill {
        cursor: pointer;
        border: 2px solid #ced4da;
        border-radius: 6px;
        padding: 8px 12px;
        text-align: center;
        font-weight: 600;
        transition: all 0.2s;
    }
    .payment-pill.active {
        border-color: #1572e8;
        background: #eaf2fd;
        color: #1572e8;
    }
</style>
@endsection

@section('content')
<div class="row pos-container">
    <!-- Left Column: Barcode Scanner, Search, Product Catalog -->
    <div class="col-lg-7 d-flex flex-column mb-3 mb-lg-0">
        <!-- Top Search & Barcode Bar -->
        <div class="card shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-barcode fa-lg"></i>
                        </span>
                    </div>
                    <input type="text" id="barcodeScannerInput" class="form-control form-control-lg font-weight-bold" 
                           placeholder="বারকোড স্ক্যান করুন অথবা পণ্যের নাম লিখুন..." autofocus autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="btnClearSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                    <span><i class="fas fa-keyboard mr-1"></i> বারকোড স্ক্যানার দিয়ে স্ক্যান করলে স্বয়ংক্রিয়ভাবে কার্টে যোগ হবে।</span>
                    <span>আজকের মোট বিক্রয়: <b class="text-success font-14">৳{{ number_format($todaySalesAmount, 2) }}</b> ({{ $todaySalesCount }} মেমো)</span>
                </div>
            </div>
        </div>

        <!-- Product Grid / Tiles -->
        <div class="card shadow-sm flex-grow-1 overflow-auto p-3" style="max-height: calc(100vh - 270px);">
            <div class="row" id="productGridContainer">
                @forelse($products as $product)
                <div class="col-md-4 col-sm-6 mb-3 product-grid-item" 
                     data-id="{{ $product->id }}"
                     data-name="{{ $product->name }}"
                     data-price="{{ $product->retail_price }}"
                     data-stock="{{ $product->stock_quantity }}"
                     data-unit="{{ $product->unit ?? 'pcs' }}"
                     data-barcode="{{ $product->barcode }}"
                     onclick="addToCartFromTile(this)">
                    <div class="card pos-product-card h-100 p-2 text-center">
                        <div class="font-weight-bold text-dark text-truncate mb-1" title="{{ $product->name }}">
                            {{ $product->name }}
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                            <span class="badge badge-light border text-muted" style="font-size: 11px;">
                                {{ $product->barcode ?: 'No Barcode' }}
                            </span>
                            <span class="text-primary font-weight-bold" style="font-size: 15px;">
                                ৳{{ number_format((float)$product->retail_price, 2) }}
                            </span>
                        </div>
                        <div class="small mt-1 text-muted">
                            মজুদ: <span class="{{ (float)$product->stock_quantity <= (float)$product->alert_quantity ? 'text-danger font-weight-bold' : '' }}">
                                {{ (float)$product->stock_quantity }} {{ $product->unit }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-2 text-secondary"></i>
                    <h5>কোনো পণ্য পাওয়া যায়নি!</h5>
                    <p>প্রথমে প্রোডাক্ট ও স্টক মেনু থেকে পণ্য যুক্ত করুন।</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column: Live POS Cart, Summary & Checkout -->
    <div class="col-lg-5">
        <div class="pos-cart-panel p-3">
            <!-- Customer & Action Header -->
            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                <h5 class="font-weight-bold text-dark mb-0">
                    <i class="fas fa-shopping-cart text-primary mr-2"></i> বিক্রয় কার্ট
                </h5>
                <div>
                    <a href="{{ route('tenant.pos.history') }}" class="btn btn-sm btn-outline-info mr-1" title="বিক্রয় তালিকা">
                        <i class="fas fa-history mr-1"></i> হিস্ট্রি
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCart" title="কার্ট খালি করুন (F4)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>

            <!-- Customer Info Dropdown / Quick Input -->
            <div class="row no-gutters mb-2">
                <div class="col-6 pr-1">
                    <input type="text" id="customerNameInput" class="form-control form-control-sm" placeholder="কাস্টমারের নাম" value="সাধারণ কাস্টমার">
                </div>
                <div class="col-6 pl-1">
                    <input type="text" id="customerPhoneInput" class="form-control form-control-sm" placeholder="মোবাইল নম্বর">
                </div>
            </div>

            <!-- Cart Table Items -->
            <div class="pos-cart-items mb-2">
                <table class="table pos-cart-table mb-0">
                    <thead>
                        <tr>
                            <th>পণ্য</th>
                            <th class="text-right" style="width: 75px;">মূল্য</th>
                            <th class="text-center" style="width: 100px;">পরিমাণ</th>
                            <th class="text-right" style="width: 80px;">মোট</th>
                            <th style="width: 25px;"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <!-- Dynamic Cart Rows Injected Here -->
                    </tbody>
                </table>
                <div id="cartEmptyPlaceholder" class="text-center py-5 text-muted">
                    <i class="fas fa-cart-arrow-down fa-3x text-secondary mb-2 d-block"></i>
                    <p class="mb-0">কার্ট খালি আছে। পণ্য স্ক্যান করুন বা ক্লিক করে যোগ করুন।</p>
                </div>
            </div>

            <!-- Financial Summary Box -->
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>উপমোট (Subtotal):</span>
                    <span class="font-weight-bold" id="cartSubtotalText">৳0.00</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted">ছাড় / ডিসকাউন্ট (৳):</span>
                    <input type="number" step="0.01" id="cartDiscountInput" class="form-control form-control-sm text-right font-weight-bold" style="width: 100px;" value="0" min="0">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">ভ্যাট / ট্যাক্স (৳):</span>
                    <input type="number" step="0.01" id="cartTaxInput" class="form-control form-control-sm text-right font-weight-bold" style="width: 100px;" value="0" min="0">
                </div>

                <!-- Grand Total Banner -->
                <div class="grand-total-box d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="small" style="opacity: 0.9;">সর্বমোট পরিশোধযোগ্য:</div>
                        <h3 class="mb-0 font-weight-bold" id="cartGrandTotalText">৳0.00</h3>
                    </div>
                    <span class="badge badge-light text-primary font-weight-bold px-3 py-2" id="cartTotalItemsBadge">
                        ০ টি আইটেম
                    </span>
                </div>

                <!-- Payment Methods Grid -->
                <div class="row no-gutters mb-2">
                    <div class="col-3 pr-1">
                        <div class="payment-pill active" data-method="cash">
                            <i class="fas fa-money-bill-wave d-block mb-1"></i> ক্যাশ
                        </div>
                    </div>
                    <div class="col-3 px-1">
                        <div class="payment-pill" data-method="bkash">
                            <i class="fas fa-mobile-alt d-block mb-1"></i> বিকাশ
                        </div>
                    </div>
                    <div class="col-3 px-1">
                        <div class="payment-pill" data-method="nagad">
                            <i class="fas fa-wallet d-block mb-1"></i> নগদ
                        </div>
                    </div>
                    <div class="col-3 pl-1">
                        <div class="payment-pill" data-method="due">
                            <i class="fas fa-clock d-block mb-1"></i> বাকি (Due)
                        </div>
                    </div>
                </div>

                <!-- Amount Received & Change -->
                <div class="row no-gutters mb-3 align-items-center">
                    <div class="col-6 pr-1">
                        <label class="small text-muted mb-0 font-weight-bold">জমা নেওয়া টাকা (Paid):</label>
                        <input type="number" step="0.01" id="paidAmountInput" class="form-control font-weight-bold text-success" value="0">
                    </div>
                    <div class="col-6 pl-1">
                        <label class="small text-muted mb-0 font-weight-bold">ফেরতযোগ্য / বাকি:</label>
                        <div class="form-control bg-light font-weight-bold text-danger text-right" id="changeAmountText">৳0.00</div>
                    </div>
                </div>

                <!-- Final Action Button -->
                <button type="button" class="btn btn-success btn-block btn-lg font-weight-bold py-3 shadow" id="btnCompleteSale">
                    <i class="fas fa-print mr-2"></i> বিক্রয় ও ক্যাশ মেমো প্রিন্ট (F2)
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let cart = [];
    let selectedPaymentMethod = 'cash';
    let debounceTimer;

    // Hotkey bindings: F2 -> Complete Sale, F4 -> Clear Cart
    $(document).keydown(function(e) {
        if (e.keyCode === 113) { // F2
            e.preventDefault();
            $('#btnCompleteSale').click();
        } else if (e.keyCode === 115) { // F4
            e.preventDefault();
            $('#btnClearCart').click();
        }
    });

    // Payment method pill selection
    $('.payment-pill').click(function() {
        $('.payment-pill').removeClass('active');
        $(this).addClass('active');
        selectedPaymentMethod = $(this).data('method');
        if (selectedPaymentMethod === 'due') {
            $('#paidAmountInput').val(0);
        } else {
            let total = calculateGrandTotal();
            $('#paidAmountInput').val(total);
        }
        updateChangeCalculation();
    });

    // Barcode scanner & search input
    $('#barcodeScannerInput').on('keyup', function(e) {
        if (e.keyCode === 13) {
            let code = $(this).val().trim();
            if (code) {
                searchAndAddToCart(code, true);
            }
        } else {
            clearTimeout(debounceTimer);
            let q = $(this).val().trim();
            if (q.length >= 2) {
                debounceTimer = setTimeout(function() {
                    searchAndAddToCart(q, false);
                }, 300);
            }
        }
    });

    $('#btnClearSearch').click(function() {
        $('#barcodeScannerInput').val('').focus();
    });

    function searchAndAddToCart(query, isEnter) {
        $.ajax({
            url: "{{ route('tenant.pos.search-products') }}",
            type: 'GET',
            data: { q: query },
            success: function(res) {
                if (res.exact_match && res.items.length > 0) {
                    // Exact barcode match: Auto add directly to cart!
                    addToCart(res.items[0]);
                    $('#barcodeScannerInput').val('').focus();
                } else if (isEnter && res.items.length === 1) {
                    addToCart(res.items[0]);
                    $('#barcodeScannerInput').val('').focus();
                } else if (res.items.length > 0) {
                    // Render filtered products in grid
                    renderProductGrid(res.items);
                }
            }
        });
    }

    function renderProductGrid(items) {
        let html = '';
        items.forEach(function(item) {
            html += `
            <div class="col-md-4 col-sm-6 mb-3 product-grid-item" 
                 data-id="${item.id}"
                 data-name="${item.name}"
                 data-price="${item.retail_price}"
                 data-stock="${item.stock_quantity}"
                 data-unit="${item.unit || 'pcs'}"
                 data-barcode="${item.barcode || ''}"
                 onclick="addToCartFromTile(this)">
                <div class="card pos-product-card h-100 p-2 text-center">
                    <div class="font-weight-bold text-dark text-truncate mb-1" title="${item.name}">
                        ${item.name}
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                        <span class="badge badge-light border text-muted" style="font-size: 11px;">
                            ${item.barcode || 'No Barcode'}
                        </span>
                        <span class="text-primary font-weight-bold" style="font-size: 15px;">
                            ৳${parseFloat(item.retail_price).toFixed(2)}
                        </span>
                    </div>
                </div>
            </div>`;
        });
        $('#productGridContainer').html(html);
    }

    function addToCartFromTile(el) {
        let item = {
            id: $(el).data('id'),
            name: $(el).data('name'),
            retail_price: $(el).data('price'),
            stock_quantity: $(el).data('stock'),
            unit: $(el).data('unit'),
            barcode: $(el).data('barcode')
        };
        addToCart(item);
    }

    function addToCart(product) {
        let existing = cart.find(c => c.product_id === product.id);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                product_id: product.id,
                name: product.name,
                unit_price: parseFloat(product.retail_price),
                quantity: 1,
                unit: product.unit || 'pcs'
            });
        }
        renderCart();
    }

    function updateQty(index, delta) {
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        renderCart();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function updateItemPrice(index, price) {
        cart[index].unit_price = parseFloat(price) || 0;
        renderCart();
    }

    $('#btnClearCart').click(function() {
        if (cart.length > 0 && confirm('আপনি কি নিশ্চিত যে সম্পূর্ণ কার্ট খালি করতে চান?')) {
            cart = [];
            renderCart();
        }
    });

    $('#cartDiscountInput, #cartTaxInput').on('input', function() {
        updateTotals();
    });

    $('#paidAmountInput').on('input', function() {
        updateChangeCalculation();
    });

    function calculateSubtotal() {
        return cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
    }

    function calculateGrandTotal() {
        let subtotal = calculateSubtotal();
        let discount = parseFloat($('#cartDiscountInput').val()) || 0;
        let tax = parseFloat($('#cartTaxInput').val()) || 0;
        return Math.max(0, (subtotal - discount) + tax);
    }

    function updateTotals() {
        let subtotal = calculateSubtotal();
        let grandTotal = calculateGrandTotal();

        $('#cartSubtotalText').text('৳' + subtotal.toFixed(2));
        $('#cartGrandTotalText').text('৳' + grandTotal.toFixed(2));
        $('#cartTotalItemsBadge').text(cart.length + ' টি আইটেম');

        if (selectedPaymentMethod !== 'due') {
            $('#paidAmountInput').val(grandTotal.toFixed(2));
        }
        updateChangeCalculation();
    }

    function updateChangeCalculation() {
        let grandTotal = calculateGrandTotal();
        let paid = parseFloat($('#paidAmountInput').val()) || 0;
        let diff = paid - grandTotal;

        if (diff >= 0) {
            $('#changeAmountText').removeClass('text-danger').addClass('text-success').text('ফেরত: ৳' + diff.toFixed(2));
        } else {
            $('#changeAmountText').removeClass('text-success').addClass('text-danger').text('বাকি: ৳' + Math.abs(diff).toFixed(2));
        }
    }

    function renderCart() {
        if (cart.length === 0) {
            $('#cartEmptyPlaceholder').removeClass('d-none');
            $('#cartTableBody').empty();
        } else {
            $('#cartEmptyPlaceholder').addClass('d-none');
            let html = '';
            cart.forEach(function(item, index) {
                let total = item.unit_price * item.quantity;
                html += `
                <tr>
                    <td>
                        <div class="font-weight-bold text-dark text-truncate" style="max-width: 140px;" title="${item.name}">
                            ${item.name}
                        </div>
                    </td>
                    <td class="text-right">
                        <input type="number" step="0.01" class="form-control form-control-sm text-right px-1" 
                               value="${item.unit_price}" style="width: 70px; display: inline-block;" 
                               onchange="updateItemPrice(${index}, this.value)">
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <button type="button" class="btn btn-sm btn-light border qty-btn" onclick="updateQty(${index}, -1)">-</button>
                            <span class="mx-2 font-weight-bold">${item.quantity}</span>
                            <button type="button" class="btn btn-sm btn-light border qty-btn" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td class="text-right font-weight-bold">
                        ৳${total.toFixed(2)}
                    </td>
                    <td class="text-center">
                        <a href="javascript:void(0)" class="text-danger" onclick="removeCartItem(${index})" title="বাদ দিন">
                            <i class="fas fa-times"></i>
                        </a>
                    </td>
                </tr>`;
            });
            $('#cartTableBody').html(html);
        }
        updateTotals();
    }

    // Complete Sale & Print Cash Memo
    $('#btnCompleteSale').click(function() {
        if (cart.length === 0) {
            alert('কার্টে কোনো পণ্য নেই! প্রথমে পণ্য স্ক্যান বা যুক্ত করুন।');
            $('#barcodeScannerInput').focus();
            return;
        }

        let $btn = $(this);
        let origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> প্রসেসিং হচ্ছে...');

        let payload = {
            items: cart,
            customer_name: $('#customerNameInput').val(),
            customer_phone: $('#customerPhoneInput').val(),
            discount_amount: parseFloat($('#cartDiscountInput').val()) || 0,
            tax_amount: parseFloat($('#cartTaxInput').val()) || 0,
            paid_amount: parseFloat($('#paidAmountInput').val()) || 0,
            payment_method: selectedPaymentMethod,
        };

        $.ajax({
            url: "{{ route('tenant.pos.checkout') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(res) {
                $btn.prop('disabled', false).html(origHtml);
                if (res.success) {
                    // Open thermal invoice print in popup
                    window.open(res.invoice_url, '_blank', 'width=420,height=600');

                    // Reset cart
                    cart = [];
                    renderCart();
                    $('#customerNameInput').val('সাধারণ কাস্টমার');
                    $('#customerPhoneInput').val('');
                    $('#cartDiscountInput').val(0);
                    $('#cartTaxInput').val(0);
                    $('#barcodeScannerInput').focus();
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(origHtml);
                alert('বিক্রয় সম্পন্ন হতে ত্রুটি: ' + (xhr.responseJSON?.message || 'সমস্যা হয়েছে'));
            }
        });
    });
</script>
@endsection
