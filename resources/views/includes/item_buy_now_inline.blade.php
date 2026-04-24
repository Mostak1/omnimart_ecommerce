@if ($sitem->item_type != 'affiliate')
    @if ($sitem->is_stock())
        <a class="btn btn-outline-primary btn-sm product-buy-now-inline buy_now_single_cart"
            data-target="{{ $sitem->id }}" href="javascript:;">
            {{ __('Buy Now') }}
        </a>
    @endif
@else
    <a class="btn btn-outline-primary btn-sm product-buy-now-inline" href="{{ $sitem->affiliate_link }}" target="_blank">
        {{ __('Buy Now') }}
    </a>
@endif
