<?php

namespace TheAfolayan\HmsLaravel\Services;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class HmsService
{
    protected $client;
    protected $apiKey;
    protected $apiSecret;
    protected $baseUrl;

    public function __construct($apiKey, $apiSecret, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseUrl = $baseUrl;
        $this->client = new Client(['base_uri' => $baseUrl]);
    }

    private function generateManagementToken()
    {
        // Cache key for the management token
        $cacheKey = '100ms_management_token';

        // Check if the token is already cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Generate a new token
        $issuedAt = new DateTimeImmutable();
        $expiry = $issuedAt->modify('+24 hours')->getTimestamp(); // 24 hours validity

        $payload = [
            'access_key' => $this->apiKey,
            'type' => 'management',
            'version' => 2,
            'jti' => Uuid::uuid4()->toString(), // Generates a unique identifier for the token
            'iat' => $issuedAt->getTimestamp(), // Issued At
            'nbf' => $issuedAt->getTimestamp(), // Not Before
            'exp' => $expiry, // Expiry
        ];

        $token = JWT::encode($payload, $this->apiSecret, 'HS256');

        // Cache the token with a short expiration buffer (e.g., 23 hours instead of 24)
        Cache::put($cacheKey, $token, \Illuminate\Support\Carbon::now()->addHours(23));

        return $token;
    }


    private function request($method, $endpoint, $data = [])
    {
        $managementToken = $this->generateManagementToken();

        $headers = [
            'Authorization' => "Bearer {$managementToken}",
            'Content-Type' => 'application/json',
        ];
        try {
            $response = $this->client->request($method, $endpoint, [
                'headers' => $headers,
                'json' => $data,
            ]);
        } catch (\Exception $e) {
            // dd($response);
            // dd($this->baseUrl);
            // Handle the exception as needed
            throw new \Exception("Request to {$endpoint} failed: " . $e->getMessage());
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function createRoom(array $data)
    {
        return $this->request('POST', '/v2/rooms', $data);
    }

    public function generateRoomCode(string $roomId, array $data = [])
    {
        return $this->request('POST', "/v2/rooms/{$roomId}/codes", $data);
    }

    public function disableRoom(string $roomId)
    {
        return $this->request('POST', "/v2/rooms/{$roomId}", ['enabled' => false]);
    }

    public function enableRoom(string $roomId)
    {
        return $this->request('POST', "/v2/rooms/{$roomId}", ['enabled' => true]);
    }

    public function getRoom(string $roomId)
    {
        return $this->request('GET', "/v2/rooms/{$roomId}");
    }

    public function getRooms()
    {
        return $this->request('GET', "/v2/rooms");
    }
}
