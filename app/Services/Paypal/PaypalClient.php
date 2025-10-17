<?php

namespace App\Services\Paypal;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaypalClient
{
    private string $clientId;
    private string $clientSecret;
    private string $mode;
    private string $baseUrl;
    private string $cacheKey;

    /**
     * @param array|null $config ['client_id' => string, 'secret' => string, 'mode' => 'sandbox'|'live']
     */
    public function __construct(?array $config = null)
    {
        $config = array_merge(config('paypal', []), $config ?? []);

        $this->clientId = (string) data_get($config, 'client_id');
        $this->clientSecret = (string) data_get($config, 'secret');
        $this->mode = data_get($config, 'mode', 'sandbox') === 'live' ? 'live' : 'sandbox';
        $this->baseUrl = $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
        $this->cacheKey = 'paypal_access_token_' . $this->mode . '_' . md5($this->clientId);
    }

    public function createOrder(
        float $amount,
        string $currency,
        string $returnUrl,
        string $cancelUrl,
        array $options = []
    ): array {
        $payload = [
            'intent' => Arr::get($options, 'intent', 'CAPTURE'),
            'purchase_units' => [
                array_filter([
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => $this->formatAmount($amount, $currency),
                    ],
                    'description' => Arr::get($options, 'description'),
                    'custom_id' => Arr::get($options, 'custom_id'),
                ]),
            ],
            'application_context' => array_filter([
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => Arr::get($options, 'brand_name', config('app.name')),
                'shipping_preference' => Arr::get($options, 'shipping_preference', 'NO_SHIPPING'),
                'user_action' => Arr::get($options, 'user_action', 'PAY_NOW'),
            ]),
        ];

        $response = $this->send(function (PendingRequest $request) use ($payload) {
            return $request->post($this->baseUrl . '/v2/checkout/orders', $payload);
        });

        return $response->json();
    }

    public function captureOrder(string $orderId): array
    {
        $response = $this->send(function (PendingRequest $request) use ($orderId) {
            return $request->post($this->baseUrl . "/v2/checkout/orders/{$orderId}/capture");
        });

        return $response->json();
    }

    private function send(callable $callback)
    {
        $request = $this->authorizedRequest();
        $response = $callback($request);

        if ($this->isTokenExpired($response->status())) {
            Cache::forget($this->cacheKey);
            $response = $callback($this->authorizedRequest());
        }

        if (!$response->successful()) {
            throw new RuntimeException($response->body());
        }

        return $response;
    }

    private function authorizedRequest(): PendingRequest
    {
        return Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->retry(1, 500, throw: false);
    }

    private function getAccessToken(): string
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new RuntimeException('PayPal credentials are missing.');
        }

        return Cache::remember($this->cacheKey, now()->addMinutes(45), function () {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->baseUrl . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                throw new RuntimeException($response->body());
            }

            return $response->json('access_token');
        });
    }

    private function isTokenExpired(int $status): bool
    {
        return in_array($status, [401, 403], true);
    }

    private function formatAmount(float $amount, string $currency): string
    {
        $zeroDecimalCurrencies = [
            'HUF', 'JPY', 'TWD',
        ];

        $currency = strtoupper($currency);

        $decimals = in_array($currency, $zeroDecimalCurrencies, true) ? 0 : 2;

        return number_format($amount, $decimals, '.', '');
    }
}
