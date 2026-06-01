@php
    $gtmBeginCheckoutItems = [];
    $gtmBeginCheckoutValue = 0;

    foreach (Session::get('cart', []) as $gtmCartKey => $gtmCartItem) {
        $gtmItemPrice = (float) data_get($gtmCartItem, 'main_price', 0) + (float) data_get($gtmCartItem, 'attribute_price', 0);
        $gtmQuantity = (int) data_get($gtmCartItem, 'qty', 0);

        $gtmBeginCheckoutValue += $gtmItemPrice * $gtmQuantity;
        $gtmBeginCheckoutItems[] = [
            'item_id' => (string) PriceHelper::GetItemId($gtmCartKey),
            'item_name' => (string) data_get($gtmCartItem, 'name', ''),
            'price' => $gtmItemPrice,
            'quantity' => $gtmQuantity,
        ];
    }

    $gtmBeginCheckoutPayload = [
        'event' => 'begin_checkout',
        'ecommerce' => [
            'currency' => PriceHelper::getCurrencyCode(),
            'value' => $gtmBeginCheckoutValue,
            'items' => $gtmBeginCheckoutItems,
        ],
    ];
@endphp
data-begin-checkout-trigger="1" data-begin-checkout-payload='@json($gtmBeginCheckoutPayload)'
