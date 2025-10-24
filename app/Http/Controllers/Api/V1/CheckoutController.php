<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\CheckoutCreateOrderRequest;
use App\Http\Requests\Api\V1\Checkout\CheckoutEstimateRequest;
use App\Http\Resources\Api\V1\OrderDetailResource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\UserNotification;
use App\Models\VendorOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function estimate(CheckoutEstimateRequest $request): JsonResponse
    {
        $cart = $this->buildCart($request->input('items'));
        $shippingCost = (float) ($request->input('shipping_cost') ?? 0);
        $currency = $this->resolveCurrency($request->input('currency'));

        $subtotal = $cart['totalPrice'];
        $tax = 0;
        $total = $subtotal + $shippingCost + $tax;

        return response()->json([
            'message' => 'Checkout estimate generated successfully.',
            'data' => [
                'items' => array_values($cart['items']),
                'subtotal' => $this->formatMoney($subtotal),
                'shipping_cost' => $this->formatMoney($shippingCost),
                'tax' => $this->formatMoney($tax),
                'total' => $this->formatMoney($total),
                'currency' => [
                    'code' => $currency->name,
                    'sign' => $currency->sign,
                    'value' => (float) $currency->value,
                ],
                'totals' => [
                    'quantity' => $cart['totalQty'],
                    'subtotal_raw' => $subtotal,
                    'total_raw' => $total,
                ],
            ],
        ]);
    }

    public function create(CheckoutCreateOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = $this->buildCart($request->input('items'));
        $shippingCost = (float) ($request->input('shipping_cost') ?? 0);
        $currency = $this->resolveCurrency($request->input('currency'));

        $subtotal = $cart['totalPrice'];
        $tax = 0;
        $total = $subtotal + $shippingCost + $tax;

        $customer = $request->input('customer', []);
        $shipping = $request->input('shipping', []) ?: $customer;

        $orderData = [
            'user_id' => $user?->id,
            'method' => $request->input('payment_method'),
            'shipping' => $request->input('shipping_method'),
            'shipping_cost' => $shippingCost,
            'totalQty' => $cart['totalQty'],
            'pay_amount' => $total,
            'order_number' => Str::upper(Str::random(4)) . time(),
            'payment_status' => 'Pending',
            'status' => 'pending',
            'cart' => json_encode($cart, JSON_THROW_ON_ERROR),
            'customer_name' => Arr::get($customer, 'name'),
            'customer_email' => Arr::get($customer, 'email'),
            'customer_phone' => Arr::get($customer, 'phone'),
            'customer_address' => Arr::get($customer, 'address'),
            'customer_city' => Arr::get($customer, 'city'),
            'customer_zip' => Arr::get($customer, 'zip'),
            'customer_state' => Arr::get($customer, 'state'),
            'customer_country' => Arr::get($customer, 'country'),
            'shipping_name' => Arr::get($shipping, 'name'),
            'shipping_email' => Arr::get($shipping, 'email'),
            'shipping_phone' => Arr::get($shipping, 'phone'),
            'shipping_address' => Arr::get($shipping, 'address'),
            'shipping_city' => Arr::get($shipping, 'city'),
            'shipping_zip' => Arr::get($shipping, 'zip'),
            'shipping_state' => Arr::get($shipping, 'state'),
            'shipping_country' => Arr::get($shipping, 'country'),
            'order_note' => $request->input('notes'),
            'currency_sign' => $currency->sign,
            'currency_name' => $currency->name,
            'currency_value' => (float) $currency->value,
        ];

        $order = DB::transaction(function () use ($orderData, $cart, $shippingCost, $total) {
            $order = Order::create($orderData);

            $order->tracks()->create([
                'title' => 'Pending',
                'text' => 'Order created via API checkout.',
            ]);

            $order->notifications()->create();

            $this->syncVendorOrders($order, $cart['items']);
            $this->syncStock($cart['items']);

            return $order;
        });

        $order->refresh();

        return (new OrderDetailResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{items: array<string, array<string, mixed>>, totalQty: int, totalPrice: float}
     */
    private function buildCart(array $items): array
    {
        $productIds = collect($items)->pluck('product_id')->unique()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $cartItems = [];
        $totalQty = 0;
        $subtotal = 0;

        foreach ($items as $index => $payload) {
            $productId = (int) $payload['product_id'];
            $quantity = (int) $payload['quantity'];
            $options = $payload['options'] ?? [];

            /** @var \App\Models\Product|null $product */
            $product = $products->get($productId);

            if (!$product) {
                abort(422, "Product {$productId} is unavailable.");
            }

            if ($product->stock !== null && $product->stock_check && $product->stock < $quantity) {
                abort(422, "Product {$product->name} has insufficient stock.");
            }

            $unitPrice = isset($payload['price'])
                ? (float) $payload['price']
                : (float) $product->price;

            $lineTotal = $unitPrice * $quantity;
            $totalQty += $quantity;
            $subtotal += $lineTotal;

            $cartItems["{$productId}_{$index}"] = [
                'product_id' => $productId,
                'qty' => $quantity,
                'price' => $lineTotal,
                'item_price' => $unitPrice,
                'options' => $options,
                'item' => [
                    'id' => $product->id,
                    'user_id' => $product->user_id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'type' => $product->type,
                    'sku' => $product->sku,
                    'stock' => $product->stock,
                ],
            ];
        }

        return [
            'items' => $cartItems,
            'totalQty' => $totalQty,
            'totalPrice' => $subtotal,
        ];
    }

    private function resolveCurrency(?string $code): Currency
    {
        if ($code) {
            $currency = Currency::whereRaw('LOWER(name) = ?', [strtolower($code)])->first();
            if ($currency) {
                return $currency;
            }
        }

        return Currency::where('is_default', 1)->first()
            ?? Currency::firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $items
     */
    private function syncVendorOrders(Order $order, array $items): void
    {
        $vendorGroups = collect($items)
            ->groupBy(fn ($item) => $item['item']['user_id'] ?? null)
            ->filter(fn ($group, $vendorId) => $vendorId);

        foreach ($vendorGroups as $vendorId => $group) {
            $qty = $group->sum('qty');
            $price = $group->sum('price');

            VendorOrder::create([
                'order_id' => $order->id,
                'user_id' => $vendorId,
                'qty' => $qty,
                'price' => $price,
                'order_number' => $order->order_number,
                'status' => 'pending',
            ]);

            UserNotification::create([
                'user_id' => $vendorId,
                'order_number' => $order->order_number,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $items
     */
    private function syncStock(array $items): void
    {
        foreach ($items as $item) {
            $productId = $item['item']['id'] ?? null;
            $qty = $item['qty'] ?? 0;

            if (!$productId || $qty <= 0) {
                continue;
            }

            /** @var \App\Models\Product|null $product */
            $product = Product::find($productId);

            if ($product && $product->type === 'Physical' && $product->stock !== null) {
                $newStock = max(0, (int) $product->stock - $qty);
                $product->stock = $newStock;
                $product->save();
            }
        }
    }

    private function formatMoney(float $value): float
    {
        return round($value, 2);
    }
}
