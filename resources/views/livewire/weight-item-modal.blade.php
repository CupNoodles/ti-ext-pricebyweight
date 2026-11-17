{{--
    Orange's cart item popup (igniter-orange::livewire.cart-item-modal, v4.4) with the quantity
    replaced by a weight. Alpine state reuses Orange's OrangeCartItem() so option prices and the
    total work the same way; only the quantity handling differs.
--}}
<div
    x-data="{
        ...OrangeCartItem(),
        step: 1,
        decimals: 0,
        roundWeight(value) {
            const factor = Math.pow(10, this.decimals);
            const rounded = Math.round((parseFloat(value) || 0) * factor) / factor;
            return Math.max(this.minQuantity, rounded);
        },
        setWeight(value) {
            this.quantity = this.roundWeight(value);
            this.$wire.set('weight', this.quantity, false);
        },
        incrementQuantity() { this.setWeight(this.quantity + this.step); },
        decrementQuantity() { this.setWeight(this.quantity - this.step); },
        init() {
            this.minQuantity = parseFloat(this.$wire.get('minWeight'));
            this.step = parseFloat(this.$wire.get('step'));
            this.decimals = parseInt(this.$wire.get('decimals'));
            this.price = parseFloat(this.$wire.get('price'));
            this.quantity = parseFloat(this.$wire.get('weight'));
            this.comment = this.$wire.get('comment');
            this.$nextTick(() => this.calculateTotal());
            this.$watch('quantity', () => this.calculateTotal());
        },
    }"
    class="modal-dialog"
    data-control="cart-item"
>
    <form method="POST" wire:submit="onSave">
        <div class="modal-content border-0">
            <div class="modal-header border-0">
                <h4 class="modal-title">{{ $menuItemData->name }}</h4>
                <button type="button" class="btn-close px-2" data-bs-dismiss="modal"></button>
            </div>
            @if ($showThumb)
                <div class="modal-top">
                    <img
                        class="img-fluid w-100"
                        src="{!! $menuItemData->getThumb(['width' => $thumbWidth, 'height' => $thumbHeight]) !!}"
                        alt="{{ $menuItemData->name }}"
                    />
                </div>
            @endif

            <div class="modal-body">
                @if (strlen($menuItemData->description))
                    <p class="text-muted">{!! $menuItemData->description !!}</p>
                @endif
                <p class="fw-bold">{!! currency_format($price) !!} / {{ $unit }}</p>

                <input type="hidden" wire:model="menuId" />
                <input type="hidden" wire:model="rowId" />

                <div id="menu-options" class="menu-options" x-ref="item-options">
                    @include('igniter-orange::includes.cartbox.item-options')
                </div>
                <x-igniter-orange::forms.error field="menuOptions" class="text-danger mb-3"/>

                <div class="menu-comment">
                    <textarea
                        wire:model="comment"
                        name="comment"
                        class="form-control"
                        rows="2"
                        placeholder="@lang('igniter.cart::default.label_add_comment')"
                    >{{ $cartItem ? $cartItem->comment : null }}</textarea>
                    <x-igniter-orange::forms.error field="comment" class="text-danger"/>
                </div>
            </div>

            <div class="modal-footer border-0">
                <div class="row g-0 w-100">
                    <div class="col-sm-5 pb-3 pb-sm-0">
                        <div class="input-group input-group-lg align-items-center flex-nowrap">
                            <button
                                x-on:click="decrementQuantity()"
                                class="btn btn-outline-secondary border-2 rounded-circle p-0"
                                type="button"
                                style="width:44px;height:44px;"
                            ><i class="fa fa-minus"></i></button>
                            <input
                                x-model="quantity"
                                x-on:change="setWeight($event.target.value)"
                                type="text"
                                name="weight"
                                class="form-control bg-transparent shadow-none border-0 text-end p-0"
                                inputmode="decimal"
                                autocomplete="off"
                                style="max-width:70px;"
                                aria-label="@lang('cupnoodles.pricebyweight::default.label_weight')"
                            >
                            <span class="px-2">{{ $unit }}</span>
                            <button
                                x-on:click="incrementQuantity()"
                                class="btn btn-outline-secondary border-2 rounded-circle p-0"
                                type="button"
                                style="width:44px;height:44px;"
                            ><i class="fa fa-plus fa-fw"></i></button>
                        </div>
                        <x-igniter-orange::forms.error field="weight" class="text-danger"/>
                    </div>
                    <div class="col-sm-7 ps-sm-3">
                        <button type="submit" class="btn btn-primary btn-lg text-white w-100" data-attach-loading>
                            <div class="d-flex align-items-center">
                                <div class="col text-nowrap">{!! $cartItem
                                    ? lang('igniter.cart::default.button_update')
                                    : lang('igniter.cart::default.button_add_to_order')
                                !!}</div>
                                <div class="col text-end fw-normal fs-6" x-text="total"></div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
