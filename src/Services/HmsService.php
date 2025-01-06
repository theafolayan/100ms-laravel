<?php

namespace TheAfolayan\HmsLaravel\Services;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;

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
        $issuedAt = time();
        $expiry = $issuedAt + (60 * 60); // 1 hour validity
        $payload = [
            'access_key' => $this->apiKey,
            'type' => 'management',
            'iat' => $issuedAt,
            'exp' => $expiry,
        ];

        return JWT::encode($payload, $this->apiSecret, 'HS256');
    }

    private function request($method, $endpoint, $data = [])
    {
        $managementToken = $this->generateManagementToken();

        $headers = [
            'Authorization' => "Bearer {$managementToken}",
            'Content-Type' => 'application/json',
        ];

        $response = $this->client->request($method, $endpoint, [
            'headers' => $headers,
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function createRoom(array $data)
    {
        return $this->request('POST', '/rooms', $data);
    }

    public function generateRoomCode(string $roomId, array $data = [])
    {
        return $this->request('POST', "/rooms/{$roomId}/codes", $data);
    }
}
