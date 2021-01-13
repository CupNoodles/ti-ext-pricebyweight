<div
    data-control="cart-box"
    data-load-item-handler="cartBoxByWeight::onLoadItemPopup"
    data-update-item-handler="{{ $updateCartItemEventHandler }}"
    data-apply-coupon-handler="{{ $applyCouponEventHandler }}"
    data-apply-tip-handler="{{ $applyTipEventHandler }}"
    data-remove-item-handler="{{ $removeCartItemEventHandler }}"
    data-remove-condition-handler="{{ $removeConditionEventHandler }}"
    data-refresh-cart-handler="{{ $refreshCartEventHandler }}"
>
    <div id="cart-box" class="module-box">
        <div id="cart-items">
            @partial('@items')
        </div>

        <div id="cart-coupon">
            @partial('cartBoxAlias::coupon_form')
        </div>

        <?php if ($__SELF__->tippingEnabled()) { ?>
        <div id="cart-tip">
            @partial('cartBoxAlias::tip_form')
        </div>
        <?php } ?>

        <div id="cart-totals">
            @partial('cartBoxAlias::totals')
        </div>

        <div id="cart-buttons" class="mt-3">
            @partial('cartBoxAlias::buttons')
        </div>
    </div>
</div>