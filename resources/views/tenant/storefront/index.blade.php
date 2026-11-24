<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings['shop_name'] ?? tenant('shop_name') ?? 'অনলাইন শপ' }}</title>

    <!-- Bootstrap 4.5 & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            background-color: #f7f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .store-header {
            background: linear-gradient(135deg, #1572e8 0%, #0d52a8 100%);
            color: #ffffff;
            padding: 25px 0 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .product-card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #eef2f5;
            transition: all 0.2s ease-in-out;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-color: #1572e8;
        }
        .product-img-box {
            height: 170px;
            background: #fdfdfd;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #f0f0f0;
            padding: 10px;
        }
        .product-img-box img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .floating-cart-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #28a745;
            color: #ffffff;
            border-radius: 50px;
            padding: 14px 24px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 8px 25px rgba(40,167,69,0.4);
            z-index: 1050;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .floating-cart-btn:hover {
            background: #218838;
            transform: scale(1.05);
            color: #ffffff;
        }
        .cart-qty-btn {
            width: 28px;
            height: 28px;
            padding: 0;
            line-height: 26px;
            font-weight: bold;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <!-- Storefront Header -->
    <header class="store-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7 text-center text-md-left mb-3 mb-md-0">
                    <h2 class="font-weight-bold mb-1">
                        <i class="fas fa-store-alt mr-2"></i> {{ $settings['shop_name'] ?? tenant('shop_name') ?? 'আমার দোকান' }}
                    </h2>
                    <p class="mb-1" style="opacity: 0.9;">
                        {{ $settings['shop_slogan'] ?? 'আপনার বিশ্বস্ত অনলাইন ও লোকাল শপ' }}
                    </p>
                    <div class="small" style="opacity: 0.85;">
                        <i class="fas fa-map-marker-alt mr-1"></i> {{ $settings['address'] ?? tenant('address') ?? 'দোকানের ঠিকানা' }}
                        @if(!empty($settings['phone']) || tenant('phone'))
                            | <i class="fas fa-phone-alt ml-2 mr-1"></i> {{ $settings['phone'] ?? tenant('phone') }}
                        @endif
                    </div>
                </div>

                <div class="col-md-5 text-center text-md-right">
                    <div class="d-inline-block bg-white text-dark p-2 px-3 rounded-pill shadow-sm">
                        <i class="fas fa-shipping-fast text-success mr-1"></i> <b>ক্যাশ অন ডেলিভারি (COD)</b> সুবিধা
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('tenant.login') }}" class="text-white small" style="opacity: 0.85; text-decoration: underline;">
                            <i class="fas fa-lock mr-1"></i> দোকানদার লগইন
                        </a>
                    </div>
                </div>
            </div>

            <!-- Search Bar in Container -->
            <div class="row justify-content-center mt-3">
                <div class="col-lg-8">
                    <form action="{{ url('/') }}" method="GET">
                        <div class="input-group shadow-sm">
                            <input type="text" name="q" class="form-control form-control-lg border-0" 
                                   placeholder="পণ্যের নাম লিখে সার্চ করুন..." value="{{ request('q') }}">
                            <div class="input-group-append">
                                <button class="btn btn-warning px-4 font-weight-bold" type="submit">
                                    <i class="fas fa-search mr-1"></i> খুঁজুন
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content: Products Grid -->
    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-th-large text-primary mr-2"></i> সকল পণ্য ও স্টক
            </h4>
            <div class="text-muted small">
                মোট {{ $products->total() }} টি পণ্য প্রদর্শিত হচ্ছে
            </div>
        </div>

        <div class="row">
            @forelse($products as $p)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="product-card">
                    <div class="product-img-box">
                        @if($p->photo)
                            <img src="{{ url('/storage/images/' . $p->photo) }}" alt="{{ $p->name }}">
                        @else
                            <i class="fas fa-box fa-4x text-muted" style="opacity: 0.4;"></i>
                        @endif
                    </div>
                    <div class="card-body p-3 d-flex flex-column">
                        <h6 class="font-weight-bold text-dark mb-1 text-truncate" title="{{ $p->name }}">
                            {{ $p->name }}
                        </h6>
                        <small class="text-muted mb-2">প্রতি {{ $p->unit ?? 'pcs' }}</small>

                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                            <div>
                                <span class="font-weight-bold text-primary" style="font-size: 18px;">
                                    ৳{{ number_format((float)$p->retail_price, 2) }}
                                </span>
                            </div>
                            <div>
                                @if((float)$p->stock_quantity > 0)
                                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" 
                                            onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->retail_price }}, '{{ $p->unit ?? 'pcs' }}')">
                                        <i class="fas fa-cart-plus mr-1"></i> যোগ করুন
                                    </button>
                                @else
                                    <span class="badge badge-secondary py-1 px-2">স্টক শেষ</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3 d-block"></i>
                <h4 class="text-muted font-weight-bold">কোনো পণ্য পাওয়া যায়নি!</h4>
                <p class="text-muted">দোকানে নতুন পণ্য যুক্ত হলে এখানে প্রদর্শিত হবে।</p>
                @if(request('q'))
                    <a href="{{ url('/') }}" class="btn btn-primary btn-sm mt-2">সকল পণ্য দেখুন</a>
                @endif
            </div>
            @endforelse
        </div>

        @if($products->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
        @endif
    </main>

    <!-- Floating Cart Button -->
    <button type="button" class="floating-cart-btn" id="btnOpenCart" data-toggle="modal" data-target="#cartModal" style="display: none;">
        <i class="fas fa-shopping-basket mr-2"></i> কার্ট: <span id="floatingCartCount">0</span> টি | <span id="floatingCartTotal">৳0.00</span>
    </button>

    <!-- Cart & Checkout Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-shopping-cart mr-2"></i> আপনার অর্ডার কার্ট ও চেকআউট
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <!-- Left: Cart Items Table -->
                        <div class="col-md-7 mb-4 mb-md-0 border-right">
                            <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">নির্বাচিত পণ্যসমূহ:</h6>
                            <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <tbody id="customerCartTableBody">
                                        <!-- Injected by JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between font-weight-bold border-top pt-2 mt-2" style="font-size: 16px;">
                                <span>মোট মূল্য:</span>
                                <span class="text-success" id="modalCartTotal">৳0.00</span>
                            </div>
                        </div>

                        <!-- Right: Delivery Details Form -->
                        <div class="col-md-5">
                            <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">ডেলিভারি তথ্য:</h6>
                            <form id="customerCheckoutForm">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">আপনার নাম <span class="text-danger">*</span></label>
                                    <input type="text" id="custName" class="form-control form-control-sm" placeholder="যেমন: মো: আরিফুল ইসলাম" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                    <input type="text" id="custPhone" class="form-control form-control-sm" placeholder="০১XXXXXXXXX" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">সম্পূর্ণ ডেলিভারি ঠিকানা <span class="text-danger">*</span></label>
                                    <textarea id="custAddress" class="form-control form-control-sm" rows="2" placeholder="বাড়ি নং, রোড, এলাকা..." required></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">বিশেষ নোট (ঐচ্ছিক)</label>
                                    <input type="text" id="custNotes" class="form-control form-control-sm" placeholder="যেমন: বিকেলে ডেলিভারি দিন">
                                </div>

                                <div class="alert alert-info py-2 px-3 small mb-3">
                                    <i class="fas fa-info-circle mr-1"></i> পেমেন্ট মেথড: <b>ক্যাশ অন ডেলিভারি (পণ্য পেয়ে টাকা দিন)</b>
                                </div>

                                <button type="submit" class="btn btn-success btn-block font-weight-bold py-2 shadow" id="btnPlaceOrder">
                                    <i class="fas fa-check-circle mr-1"></i> অর্ডার কনফার্ম করুন
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container small">
            &copy; {{ date('Y') }} <b>{{ $settings['shop_name'] ?? tenant('shop_name') }}</b> | Powered by OmniMart Multi-Tenant Platform
        </div>
    </footer>

    <!-- jQuery & Bootstrap Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let customerCart = [];

        function addToCart(id, name, price, unit) {
            let existing = customerCart.find(i => i.product_id === id);
            if (existing) {
                existing.quantity += 1;
            } else {
                customerCart.push({
                    product_id: id,
                    name: name,
                    unit_price: parseFloat(price),
                    quantity: 1,
                    unit: unit
                });
            }
            renderCustomerCart();
            // Pulse floating button
            $('#btnOpenCart').addClass('animate__animated animate__pulse');
        }

        function updateCustomerQty(index, delta) {
            customerCart[index].quantity += delta;
            if (customerCart[index].quantity <= 0) {
                customerCart.splice(index, 1);
            }
            renderCustomerCart();
        }

        function removeCustomerItem(index) {
            customerCart.splice(index, 1);
            renderCustomerCart();
        }

        function renderCustomerCart() {
            let totalQty = 0;
            let grandTotal = 0;
            let html = '';

            customerCart.forEach((item, idx) => {
                let itemTotal = item.unit_price * item.quantity;
                totalQty += item.quantity;
                grandTotal += itemTotal;

                html += `
                <tr>
                    <td>
                        <div class="font-weight-bold text-truncate" style="max-width: 130px;">${item.name}</div>
                        <small class="text-muted">৳${item.unit_price}</small>
                    </td>
                    <td class="text-center" style="width: 100px;">
                        <button type="button" class="btn btn-sm btn-light border cart-qty-btn" onclick="updateCustomerQty(${idx}, -1)">-</button>
                        <span class="mx-1 font-weight-bold">${item.quantity}</span>
                        <button type="button" class="btn btn-sm btn-light border cart-qty-btn" onclick="updateCustomerQty(${idx}, 1)">+</button>
                    </td>
                    <td class="text-right font-weight-bold" style="width: 75px;">
                        ৳${itemTotal.toFixed(2)}
                    </td>
                    <td style="width: 25px;">
                        <a href="javascript:void(0)" class="text-danger" onclick="removeCustomerItem(${idx})"><i class="fas fa-times"></i></a>
                    </td>
                </tr>`;
            });

            $('#customerCartTableBody').html(html);
            $('#modalCartTotal').text('৳' + grandTotal.toFixed(2));
            $('#floatingCartCount').text(totalQty);
            $('#floatingCartTotal').text('৳' + grandTotal.toFixed(2));

            if (customerCart.length > 0) {
                $('#btnOpenCart').fadeIn();
            } else {
                $('#btnOpenCart').fadeOut();
                $('#cartModal').modal('hide');
            }
        }

        // Handle customer online order submit
        $('#customerCheckoutForm').submit(function(e) {
            e.preventDefault();
            if (customerCart.length === 0) {
                alert('আপনার কার্ট খালি!');
                return;
            }

            let $btn = $('#btnPlaceOrder');
            let origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> অর্ডার সম্পন্ন হচ্ছে...');

            let payload = {
                customer_name: $('#custName').val(),
                customer_phone: $('#custPhone').val(),
                delivery_address: $('#custAddress').val(),
                notes: $('#custNotes').val(),
                items: customerCart
            };

            $.ajax({
                url: "{{ route('tenant.storefront.checkout') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function(res) {
                    if (res.success) {
                        customerCart = [];
                        window.location.href = res.redirect_url;
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(origHtml);
                    alert('অর্ডার সম্পন্ন হতে ব্যর্থ: ' + (xhr.responseJSON?.message || 'সমস্যা হয়েছে'));
                }
            });
        });
    </script>
</body>
</html>
