<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProxyCheckService
{
    private const API_URL = 'https://proxycheck.io/v2/';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.proxycheck.key', '');
    }

    public function checkIpInfo(string $ip): array
    {
        if (empty($this->apiKey)) {
            Log::warning('ProxyCheck API key not configured');
            return $this->getDefaultResponse();
        }

        if ($this->isLocalOrPrivateIp($ip)) {
            return $this->getLocalIpResponse();
        }

        try {
            $response = Http::timeout(10)->get(self::API_URL . $ip, [
                'key' => $this->apiKey,
                'vpn' => 1,
                'asn' => 1,
                'node' => 1,
                'time' => 1,
                'inf' => 0,
                'risk' => 1,
                'port' => 1,
                'seen' => 1,
                'days' => 7,
                'tag' => 'none',
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->parseResponse($data, $ip);
            }

            Log::error('ProxyCheck API error', ['status' => $response->status()]);
            return $this->getDefaultResponse();

        } catch (\Exception $e) {
            Log::error('ProxyCheck API exception', ['error' => $e->getMessage()]);
            return $this->getDefaultResponse();
        }
    }

    public function getCountryByIp(string $ip): ?string
    {
        $info = $this->checkIpInfo($ip);
        return $info['country'] ?? null;
    }

    public function isVpnOrProxy(string $ip): bool
    {
        $info = $this->checkIpInfo($ip);
        return $info['is_vpn'] ?? false;
    }

    private function parseResponse(array $data, string $ip): array
    {
        if (!isset($data[$ip])) {
            return $this->getDefaultResponse();
        }

        $ipData = $data[$ip];

        return [
            'country' => $ipData['country'] ?? null,
            'country_code' => $ipData['isocode'] ?? null,
            'is_vpn' => ($ipData['proxy'] ?? 'no') === 'yes',
            'vpn_type' => $ipData['type'] ?? null,
            'provider' => $ipData['provider'] ?? null,
            'risk_score' => $ipData['risk'] ?? 0,
            'city' => $ipData['city'] ?? null,
            'region' => $ipData['region'] ?? null,
            'timezone' => $ipData['timezone'] ?? null,
        ];
    }

    private function isLocalOrPrivateIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) ||
               filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    private function getLocalIpResponse(): array
    {
        return [
            'country' => 'Local',
            'country_code' => 'XX',
            'is_vpn' => false,
            'vpn_type' => null,
            'provider' => 'Local Network',
            'risk_score' => 0,
            'city' => 'Local',
            'region' => 'Local',
            'timezone' => 'UTC',
        ];
    }

    private function getDefaultResponse(): array
    {
        return [
            'country' => null,
            'country_code' => null,
            'is_vpn' => false,
            'vpn_type' => null,
            'provider' => null,
            'risk_score' => 0,
            'city' => null,
            'region' => null,
            'timezone' => null,
        ];
    }
}