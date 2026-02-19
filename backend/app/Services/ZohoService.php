<?php

namespace App\Services;

use App\Models\ZohoToken;
use Illuminate\Support\Facades\Http;

class ZohoService
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private string $authUrl;
    
    public function __construct()
    {
        $this->clientId = config('services.zoho.client_id');
        $this->clientSecret = config('services.zoho.client_secret');
        $this->baseUrl = config('services.zoho.base_url');
        $this->authUrl = config('services.zoho.auth_url');
    }

    private function getAccessToken(): string
    {
        $token = ZohoToken::first();
        if (! $token || $token->isExpired()) {
            $this->refreshToken($token);
            $token = ZohoToken::first();
        }
        return $token->access_token;
    }

    private function refreshToken(?ZohoToken $token): void
    {
        $response = Http::asForm()->post($this->authUrl, [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $token->refresh_token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Zoho token refresh failed: '.$response->body());
        }

        $data = $response->json();

        ZohoToken::updateOrCreate(
            ['id' => 1],
            [
                'access_token' => $data['access_token'],
                'expires_at' => now()->addSeconds($data['expires_in']), // 3600 сек = 1 час
            ]
        );
    }

    public function request(string $method, string $endpoint, array $data = []): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->$method("{$this->baseUrl}/{$endpoint}", $data);

        if ($response->status() === 401) {
            $token = ZohoToken::first();
            $this->refreshToken($token);

            $response = Http::withToken($this->getAccessToken())
                ->$method("{$this->baseUrl}/{$endpoint}", $data);
        }

        if ($response->failed()) {
            throw new \Exception('Zoho API error: '.$response->body());
        }

        return $response->json();
    }

    public function createAccount(array $data): string
    {
        $response = $this->request('post', 'Accounts', [
            'data' => [[
                'Account_Name' => $data['account_name'],
                'Website' => $data['account_website'],
                'Phone' => $data['account_phone'],
            ]],
        ]);

        if ($response['data'][0]['status'] !== 'success') {
            throw new \Exception('Zoho account creation error'.$response['data'][0]['message']);
        }

        return $response['data'][0]['details']['id'];

    }

    public function createDeal(array $data, int $accountId): string
    {
        $response = $this->request('post', 'Deals', [
            'data' => [[
                'Deal_Name' => $data['deal_name'],
                'Stage' => $data['deal_stage'],
                'Account_Name' => $accountId,
            ]],
        ]);

        if ($response['data'][0]['status'] !== 'success') {
            throw new \Exception($response['data'][0]['message']);
        }

        return $response['data'][0]['details']['id'];

    }
}
