@if ($cart->count())
    <div class="cart-items">
        <ul>
            @foreach ($cart->content()->reverse() as $cartItem)
                @php

                $uom_info = CupNoodles\PriceByWeight\Models\Units::getUnitForMenuId($cartItem->id);
                @endphp
                <li>
                    <button
                        type="button"
                        class="cart-btn btn btn-light btn-sm text-muted"
                        data-request="cartBoxByWeight::onRemoveItem"
                        data-replace-loading="fa fa-spinner fa-spin"
                        data-request-data="rowId: '{{ $cartItem->rowId }}', menuId: '{{ $cartItem->id }}'"
                    ><i class="fa fa-minus"></i></button>

                    <span class="price pull-right">
                        <?php if ($cartItem->hasConditions()) { ?>
                            <s class="text-muted"><?= currency_format($cartItem->subtotalWithoutConditions()); ?></s>/
                        <?php } ?>
                        <?= currency_format($cartItem->subtotal); ?>
                    </span>
                    <a
                        class="name-image"
                        data-cart-control="load-item"
                        data-row-id="{{ $cartItem->rowId }}"
                        data-menu-id="{{ $cartItem->id }}"
                    >
                        <span class="name">
                            <span class="quantity font-weight-bold">
                                {{ $cartItem->qty }} 
                                @if( isset( $uom_info->price_by_weight ) && $uom_info->price_by_weight  )
                                    {!! $uom_info->short_name !!}
                                @endif
                                @lang('igniter.cart::default.text_times')
                            </span>
                            {{ $cartItem->name }}
                        </span>
                        @if ($cartItem->hasOptions())
                            @partial('cartBoxAlias::cart_item_options', ['itemOptions' => $cartItem->options])
                        @endif
                        @if (!empty($cartItem->comment))
                            <p class="comment text-muted small">
                                {{ $cartItem->comment }}
                            </p>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@else
    <div class="panel-body">@lang('igniter.cart::default.text_no_cart_items')</div>
@endif
