<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Livewire;

use CupNoodles\PriceByWeight\Classes\Weight;
use Exception;
use Igniter\Cart\CartItem;
use Igniter\Cart\Classes\CartManager;
use Igniter\Local\Facades\Location;
use Igniter\Orange\Contracts\ModalComponent;
use Igniter\Orange\Data\MenuItemData;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

/**
 * Add-to-cart popup for items sold by weight, opened in place of Orange's cart item popup
 * (whose quantity is a whole number). Menu options use Orange's own option partials; the save
 * and option validation follow Orange's CartItemModal (v4.4).
 */
class WeightItemModal extends ModalComponent
{
    public const string CODE = 'cupnoodles-pricebyweight::weight-item-modal';

    public ?int $menuId = null;

    public ?string $rowId = null;

    public float|string|null $weight = null;

    public ?string $comment = null;

    public ?array $menuOptions = [];

    /** Price per unit, eg. per lb */
    public float $price = 0;

    public float $minWeight = 0;

    public float $step = 1;

    public int $decimals = 2;

    public string $unit = '';

    /** Same display options as Orange's popup */
    public bool $showThumb = true;

    public int $thumbWidth = 720;

    public int $thumbHeight = 300;

    public bool $hideZeroOptionPrices = false;

    public ?int $limitOptionsValues = 6;

    protected $cartManager;

    protected ?CartItem $cartItem = null;

    protected ?MenuItemData $menuItemData = null;

    public function boot(): void
    {
        $this->cartManager = resolve(CartManager::class);
    }

    public function mount(int $menuId, ?string $rowId = null): void
    {
        $this->menuId = $menuId;
        $this->rowId = $rowId;

        $menu = $this->getMenuItemData()->model;
        $unit = Weight::unit($menu);

        $this->price = $this->getCartItem()->price ?? $this->getMenuItemData()->price();
        $this->minWeight = Weight::minimum($menu);
        $this->step = Weight::step($menu);
        $this->decimals = $unit?->decimal_places ?? 0;
        $this->unit = $unit?->abbreviation ?? '';
        $this->weight = $this->getCartItem()->qty ?? $this->minWeight;
        $this->comment = $this->getCartItem()?->comment;
    }

    public function render(): View
    {
        return view('cupnoodles.pricebyweight::livewire.weight-item-modal', [
            'cartItem' => $this->getCartItem(),
            'menuItemData' => $this->getMenuItemData(),
        ]);
    }

    public function onSave(): void
    {
        $this->validate([
            'weight' => ['required', 'numeric', 'min:'.$this->minWeight],
        ], [], ['weight' => lang('cupnoodles.pricebyweight::default.label_weight')]);

        $this->validateMenuOptions();

        try {
            $cartItem = $this->cartManager->addOrUpdateCartItem([
                'menuId' => $this->menuId,
                'rowId' => $this->rowId,
                'quantity' => (float)$this->weight,
                'comment' => $this->comment,
                'menu_options' => $this->menuOptions,
            ]);

            $this->rowId = $cartItem->rowId;

            $this->dispatch('hideModal');
        } catch (Exception $exception) {
            throw ValidationException::withMessages(['menuOptions' => strip_tags($exception->getMessage())]);
        }
    }

    public function getLocationId()
    {
        return Location::getId();
    }

    /** Used by Orange's quantity-type option partial */
    public function getOptionQuantityTypeValue($menuOptionValueId)
    {
        $value = 0;
        $this->getCartItem()?->options->each(function($option) use ($menuOptionValueId, &$value): void {
            $option->values->each(function($opt) use ($menuOptionValueId, &$value): void {
                if ($opt->id == $menuOptionValueId) {
                    $value = $opt->qty;
                }
            });
        });

        return $value;
    }

    protected function validateMenuOptions(): void
    {
        $errors = [];
        $selectedOptions = collect($this->menuOptions);

        foreach ($this->getMenuItemData()->getOptions() as $menuOption) {
            $selectedOption = $selectedOptions->get($menuOption->getKey());
            $selectedValues = array_filter((array)array_get($selectedOption, 'option_values', []));

            if (!in_array($menuOption->option->display_type, ['quantity', 'checkbox'])) {
                $selectedValues = array_filter($selectedValues, is_numeric(...));
            }

            try {
                $this->cartManager->validateMenuItemOption($menuOption, $selectedValues);
            } catch (Exception $exception) {
                $errors['menuOptions.'.$menuOption->getKey()] = strip_tags($exception->getMessage());
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function getCartItem(): ?CartItem
    {
        if (!is_null($this->cartItem)) {
            return $this->cartItem;
        }

        return $this->cartItem = $this->rowId ? $this->cartManager->getCartItem($this->rowId) : null;
    }

    protected function getMenuItemData(): MenuItemData
    {
        return $this->menuItemData ??= new MenuItemData($this->cartManager->findMenuItem($this->menuId));
    }
}
