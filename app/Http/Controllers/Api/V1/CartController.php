<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cart as ShoppingCart;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Product;
use App\Models\User;
use App\Models\UserCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request, int $buyerId): JsonResponse
    {
        $this->assertBuyer($request, $buyerId);

        $cart = $this->loadCart($buyerId);

        return response()->json([
            'data' => $this->transformCart($cart),
        ]);
    }

    public function store(Request $request, int $buyerId): JsonResponse
    {
        $this->assertBuyer($request, $buyerId);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'string', 'max:191'],
            'color' => ['nullable', 'string', 'max:191'],
            'size_qty' => ['nullable', 'integer', 'min:0'],
            'size_price' => ['nullable', 'numeric'],
            'size_key' => ['nullable', 'string', 'max:191'],
            'attribute_keys' => ['nullable', 'array'],
            'attribute_keys.*' => ['string', 'max:191'],
            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*' => ['string', 'max:191'],
            'attribute_prices' => ['nullable', 'array'],
            'attribute_prices.*' => ['numeric'],
            'affiliate_user' => ['nullable', 'integer', 'min:0'],
        ]);

        $currency = $this->resolveCurrency();
        $settings = $this->generalSettings();

        $qty = (int) ($validated['qty'] ?? 1);
        $qty = max($qty, 1);

        $product = Product::query()
            ->select([
                'id',
                'user_id',
                'slug',
                'name',
                'photo',
                'size',
                'size_qty',
                'size_price',
                'color',
                'price',
                'stock',
                'type',
                'file',
                'link',
                'license',
                'license_qty',
                'measure',
                'whole_sell_qty',
                'whole_sell_discount',
                'attributes',
                'minimum_qty',
                'stock_check',
                'size_all',
                'color_all',
            ])
            ->findOrFail($validated['product_id']);

        if ($product->type !== 'Physical') {
            $qty = 1;
        }

        if ($product->user_id != 0) {
            $product->price = round(
                $product->price + $settings->fixed_commission + ($product->price / 100) * $settings->percentage_commission,
                2
            );
        }

        $attributePrices = $validated['attribute_prices'] ?? [];
        foreach ($attributePrices as $priceAdjustment) {
            $product->price += ((float) $priceAdjustment) / ($currency->value ?: 1);
        }

        if (!empty($product->license_qty)) {
            $hasLicenseStock = false;
            foreach ($product->license_qty as $licenseQty) {
                if ((int) $licenseQty > 0) {
                    $hasLicenseStock = true;
                    break;
                }
            }

            if (!$hasLicenseStock) {
                throw ValidationException::withMessages([
                    'product_id' => __('Out of stock.'),
                ]);
            }
        }

        $size = isset($validated['size']) ? str_replace(' ', '-', (string) $validated['size']) : null;
        if (!$size && !empty($product->size)) {
            $size = str_replace(' ', '-', trim((string) $product->size[0]));
        }

        $color = $validated['color'] ?? null;
        if (!$color && !empty($product->color)) {
            $color = (string) $product->color[0];
        }

        if ($product->stock_check == 0) {
            if (!$size && !empty($product->size_all)) {
                $size = str_replace(' ', '-', trim(explode(',', $product->size_all)[0]));
            }

            if (!$color && !empty($product->color_all)) {
                $color = trim(explode(',', $product->color_all)[0]);
            }
        }

        $sizeQty = $validated['size_qty'] ?? null;
        if ($sizeQty === null && !empty($product->size_qty)) {
            $sizeQty = (int) $product->size_qty[0];
        }

        if ($product->stock_check == 1) {
            if ($sizeQty === 0) {
                throw ValidationException::withMessages([
                    'size_qty' => __('Out of stock.'),
                ]);
            }

            if ($sizeQty !== null && $qty > $sizeQty) {
                throw ValidationException::withMessages([
                    'qty' => __('Requested quantity exceeds available stock for the selected size.'),
                ]);
            }

            if ($sizeQty === null && $product->stock !== null && $qty > $product->stock) {
                throw ValidationException::withMessages([
                    'qty' => __('Requested quantity exceeds available stock.'),
                ]);
            }
        }

        $sizePrice = ((float) ($validated['size_price'] ?? 0)) / ($currency->value ?: 1);
        $sizeKey = (string) ($validated['size_key'] ?? '');

        $keys = $validated['attribute_keys'] ?? [];
        $values = $validated['attribute_values'] ?? [];
        $keysString = $this->stringifyAttributes($keys);
        $valuesString = $this->stringifyAttributes($values);

        $affiliateUser = (int) ($validated['affiliate_user'] ?? 0);

        $cart = $this->loadCart($buyerId);
        $cartSnapshot = $this->cloneCart($cart);

        $this->seedDiscountSession($cart);

        $cart->addnum(
            $product,
            $product->id,
            $qty,
            (string) ($size ?? ''),
            (string) ($color ?? ''),
            $sizeQty,
            $sizePrice,
            $sizeKey,
            $keysString,
            $valuesString,
            $affiliateUser
        );

        $itemKey = $this->makeItemKey($product->id, $size, $color, $valuesString);

        if ($product->stock_check == 1) {
            if (isset($cart->items[$itemKey]['stock']) && $cart->items[$itemKey]['stock'] < 0) {
                $cart = $cartSnapshot;
                $this->recalculateTotals($cart);
                $this->storeCart($buyerId, $cart);

                throw ValidationException::withMessages([
                    'qty' => __('Out of stock.'),
                ]);
            }

            if (!empty($sizeQty) && $cart->items[$itemKey]['qty'] > $cart->items[$itemKey]['size_qty']) {
                $cart = $cartSnapshot;
                $this->recalculateTotals($cart);
                $this->storeCart($buyerId, $cart);

                throw ValidationException::withMessages([
                    'qty' => __('Requested quantity exceeds available stock for the selected size.'),
                ]);
            }
        }

        $this->recalculateTotals($cart);
        $this->storeCart($buyerId, $cart);

        return response()->json([
            'message' => __('Item added to cart.'),
            'data' => $this->transformCart($cart),
        ], 201);
    }

    public function update(Request $request, int $buyerId, string $itemId): JsonResponse
    {
        $this->assertBuyer($request, $buyerId);

        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->loadCart($buyerId);

        if (empty($cart->items) || !array_key_exists($itemId, $cart->items)) {
            abort(404, __('Cart item not found.'));
        }

        $item = $cart->items[$itemId];
        $targetQty = (int) $validated['qty'];

        /** @var Product $product */
        $product = Product::query()
            ->select([
                'id',
                'user_id',
                'slug',
                'name',
                'photo',
                'size',
                'size_qty',
                'size_price',
                'color',
                'price',
                'stock',
                'type',
                'file',
                'link',
                'license',
                'license_qty',
                'measure',
                'whole_sell_qty',
                'whole_sell_discount',
                'attributes',
                'minimum_qty',
                'stock_check',
                'size_all',
                'color_all',
            ])
            ->findOrFail($item['item']->id ?? $item['item']['id'] ?? null);

        if ($product->type !== 'Physical') {
            $targetQty = 1;
        }

        $sizeQtyLimit = isset($item['size_qty']) && $item['size_qty'] !== '' ? (int) $item['size_qty'] : null;

        if ($product->stock_check == 1) {
            if ($sizeQtyLimit !== null && $targetQty > $sizeQtyLimit) {
                throw ValidationException::withMessages([
                    'qty' => __('Requested quantity exceeds available stock for the selected size.'),
                ]);
            }

            if ($sizeQtyLimit === null && $product->stock !== null && $targetQty > $product->stock) {
                throw ValidationException::withMessages([
                    'qty' => __('Requested quantity exceeds available stock.'),
                ]);
            }
        }

        $cartSnapshot = $this->cloneCart($cart);

        $this->seedDiscountSession($cart);
        $cart->removeItem($itemId);

        $size = $item['size'] ?? '';
        $color = $item['color'] ?? '';
        $valuesString = $item['values'] ?? '';
        $sizePrice = (float) ($item['size_price'] ?? 0);
        $sizeKey = (string) ($item['size_key'] ?? '');
        $affiliateUser = (int) ($item['affilate_user'] ?? 0);

        $cart->addnum(
            $product,
            $product->id,
            $targetQty,
            (string) $size,
            (string) $color,
            $sizeQtyLimit,
            $sizePrice,
            $sizeKey,
            (string) ($item['keys'] ?? ''),
            $valuesString,
            $affiliateUser
        );

        $itemKey = $this->makeItemKey($product->id, $size, $color, $valuesString);

        if ($product->stock_check == 1) {
            if (isset($cart->items[$itemKey]['stock']) && $cart->items[$itemKey]['stock'] < 0) {
                $cart = $cartSnapshot;
                $this->recalculateTotals($cart);
                $this->storeCart($buyerId, $cart);

                throw ValidationException::withMessages([
                    'qty' => __('Out of stock.'),
                ]);
            }

            if (!empty($sizeQtyLimit) && $cart->items[$itemKey]['qty'] > $sizeQtyLimit) {
                $cart = $cartSnapshot;
                $this->recalculateTotals($cart);
                $this->storeCart($buyerId, $cart);

                throw ValidationException::withMessages([
                    'qty' => __('Requested quantity exceeds available stock for the selected size.'),
                ]);
            }
        }

        $this->recalculateTotals($cart);
        $this->storeCart($buyerId, $cart);

        return response()->json([
            'message' => __('Cart item updated.'),
            'data' => $this->transformCart($cart),
        ]);
    }

    public function destroy(Request $request, int $buyerId, string $itemId): JsonResponse
    {
        $this->assertBuyer($request, $buyerId);

        $cart = $this->loadCart($buyerId);

        if (empty($cart->items) || !array_key_exists($itemId, $cart->items)) {
            abort(404, __('Cart item not found.'));
        }

        $this->seedDiscountSession($cart);
        $cart->removeItem($itemId);

        $this->recalculateTotals($cart);
        $this->storeCart($buyerId, $cart);

        return response()->json([
            'message' => __('Item removed from cart.'),
            'data' => $this->transformCart($cart),
        ]);
    }

    private function assertBuyer(Request $request, int $buyerId): void
    {
        $authenticated = $request->user();

        if ($authenticated && (int) $authenticated->id !== (int) $buyerId) {
            abort(403, __('You are not allowed to access this cart.'));
        }

        User::query()->findOrFail($buyerId);
    }

    private function loadCart(int $buyerId): ShoppingCart
    {
        $record = UserCart::query()->where('user_id', $buyerId)->first();

        $oldCart = null;
        if ($record && $record->data) {
            $oldCart = @unserialize($record->data, ['allowed_classes' => true]);
        }

        $cart = new ShoppingCart($oldCart);

        if (!is_array($cart->items)) {
            $cart->items = [];
        }

        $this->seedDiscountSession($cart);
        $this->recalculateTotals($cart);

        return $cart;
    }

    private function storeCart(int $buyerId, ShoppingCart $cart): void
    {
        if (empty($cart->items)) {
            UserCart::query()->where('user_id', $buyerId)->delete();
            Session::forget('current_discount');

            return;
        }

        UserCart::query()->updateOrCreate(
            ['user_id' => $buyerId],
            ['data' => serialize($cart)]
        );

        Session::forget('current_discount');
    }

    private function resolveCurrency(): Currency
    {
        return cache()->remember('api_default_currency', now()->addMinutes(10), function () {
            return Currency::query()->where('is_default', 1)->firstOrFail();
        });
    }

    private function generalSettings(): Generalsetting
    {
        return cache()->remember('api_generalsettings', now()->addMinutes(10), function () {
            return Generalsetting::query()->firstOrFail();
        });
    }

    private function transformCart(ShoppingCart $cart): array
    {
        $items = [];

        if (!empty($cart->items)) {
            foreach ($cart->items as $itemId => $item) {
                $items[] = [
                    'item_id' => $itemId,
                    'product_id' => $item['item']->id ?? null,
                    'name' => $item['item']->name ?? null,
                    'quantity' => (int) ($item['qty'] ?? 0),
                    'unit_price' => round((float) ($item['item_price'] ?? 0), 2),
                    'line_total' => round((float) ($item['price'] ?? 0), 2),
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'attributes' => $this->parseAttributes($item['keys'] ?? '', $item['values'] ?? ''),
                    'discount_percentage' => $item['discount'] ?? 0,
                    'affiliate_user' => $item['affilate_user'] ?? 0,
                    'is_digital' => $item['dp'] ?? '0',
                ];
            }
        }

        return [
            'items' => $items,
            'total_quantity' => (int) ($cart->totalQty ?? 0),
            'total_price' => round((float) ($cart->totalPrice ?? 0), 2),
        ];
    }

    private function parseAttributes(string $keys, string $values): array
    {
        if ($keys === '' || $values === '') {
            return [];
        }

        $keysArray = array_values(array_filter(explode(',', $keys), static fn ($value) => $value !== ''));
        $valuesArray = array_values(array_filter(explode(',', $values), static fn ($value) => $value !== ''));

        $attributes = [];
        foreach ($keysArray as $index => $key) {
            $attributes[] = [
                'key' => trim($key),
                'value' => $valuesArray[$index] ?? null,
            ];
        }

        return $attributes;
    }

    private function stringifyAttributes(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        return implode(',', array_map(static fn ($value) => trim((string) $value), $attributes));
    }

    private function makeItemKey(int $productId, ?string $size, ?string $color, ?string $values): string
    {
        $size = $size ?? '';
        $color = $color ?? '';
        $cleanValues = $values ? str_replace(str_split(' ,'), '', $values) : '';

        return $productId . $size . $color . $cleanValues;
    }

    private function recalculateTotals(ShoppingCart $cart): void
    {
        $cart->totalPrice = 0;
        $cart->totalQty = 0;

        if (!empty($cart->items)) {
            foreach ($cart->items as $item) {
                $cart->totalPrice += $item['price'] ?? 0;
                $cart->totalQty += $item['qty'] ?? 0;
            }
        }
    }

    private function seedDiscountSession(ShoppingCart $cart): void
    {
        $discounts = [];

        if (!empty($cart->items)) {
            foreach ($cart->items as $itemId => $item) {
                if (!empty($item['discount'])) {
                    $discounts[$itemId] = $item['discount'];
                }
            }
        }

        if (!empty($discounts)) {
            Session::put('current_discount', $discounts);
        } else {
            Session::forget('current_discount');
        }
    }

    private function cloneCart(ShoppingCart $cart): ShoppingCart
    {
        $serialized = serialize($cart);
        $snapshot = @unserialize($serialized, ['allowed_classes' => true]);

        if ($snapshot === false) {
            $snapshot = $cart;
        }

        return new ShoppingCart($snapshot);
    }
}
