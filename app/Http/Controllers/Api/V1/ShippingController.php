<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shipping\ShippingQuoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingController extends Controller
{
    public function services(Request $request): JsonResponse
    {
        $languageId = (int) $request->query('language_id') ?: $this->defaultLanguageId();

        $services = DB::table('shippings')
            ->where(function ($query) use ($languageId) {
                $query->where('language_id', $languageId)
                    ->orWhere('language_id', 0);
            })
            ->orderBy('price')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => (int) $service->id,
                    'code' => (string) $service->id,
                    'title' => $service->title,
                    'subtitle' => $service->subtitle,
                    'price' => (float) $service->price,
                ];
            });

        return response()->json([
            'data' => $services,
        ]);
    }

    public function quote(ShippingQuoteRequest $request): JsonResponse
    {
        $languageId = $this->defaultLanguageId();
        $serviceCode = $request->input('service_code');

        $service = DB::table('shippings')
            ->where(function ($query) use ($languageId) {
                $query->where('language_id', $languageId)
                    ->orWhere('language_id', 0);
            })
            ->when($serviceCode, fn ($query) => $query->where('id', $serviceCode))
            ->orderBy('price')
            ->first();

        if (!$service) {
            return response()->json([
                'message' => 'Shipping service not found.',
            ], 404);
        }

        $items = collect($request->input('items', []));
        $totalWeight = $items->sum(function ($item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $weight = (float) ($item['weight'] ?? 0.5);
            return $qty * $weight;
        });

        $totalPrice = $items->sum(function ($item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['price'] ?? 0);
            return $qty * $price;
        });

        $base = (float) $service->price;
        $weightComponent = max($totalWeight, 0.5) * 0.5;
        $valueComponent = $totalPrice * 0.02;

        $estimated = round($base + $weightComponent + $valueComponent, 2);

        $currency = $this->defaultCurrency();

        return response()->json([
            'data' => [
                'service' => [
                    'id' => (int) $service->id,
                    'code' => (string) $service->id,
                    'title' => $service->title,
                    'subtitle' => $service->subtitle,
                ],
                'breakdown' => [
                    'base' => $base,
                    'weight_component' => round($weightComponent, 2),
                    'value_component' => round($valueComponent, 2),
                ],
                'estimated_cost' => $estimated,
                'currency' => $currency,
            ],
        ]);
    }

    public function hubs(Request $request): JsonResponse
    {
        $languageId = (int) $request->query('language_id') ?: $this->defaultLanguageId();

        $hubs = DB::table('pickups')
            ->where(function ($query) use ($languageId) {
                $query->where('language_id', $languageId)
                    ->orWhere('language_id', 0);
            })
            ->orderBy('location')
            ->get()
            ->map(function ($hub) {
                return [
                    'id' => (int) $hub->id,
                    'name' => $hub->location,
                ];
            });

        return response()->json([
            'data' => $hubs,
        ]);
    }

    private function defaultLanguageId(): int
    {
        return (int) (DB::table('languages')->where('is_default', 1)->value('id') ?? 1);
    }

    private function defaultCurrency(): array
    {
        $currency = DB::table('currencies')->where('is_default', 1)->first();

        if (!$currency) {
            return [
                'code' => 'USD',
                'sign' => '$',
                'value' => 1,
            ];
        }

        return [
            'code' => $currency->name,
            'sign' => $currency->sign,
            'value' => (float) $currency->value,
        ];
    }
}

