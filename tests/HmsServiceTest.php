<?php

namespace TheAfolayan\HmsLaravel\Tests;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use TheAfolayan\HmsLaravel\Services\HmsService;

class HmsServiceTest extends TestCase
{
    /** @var array<int, array<string, mixed>> */
    protected $history = [];

    public function testRequestsCarryASignedManagementToken()
    {
        $service = $this->serviceReturning([new Response(200, [], '{"id":"room-1"}')]);

        $response = $service->createRoom(['name' => 'My Room']);

        $this->assertSame(['id' => 'room-1'], $response);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.100ms.live/v2/rooms', (string) $request->getUri());
        $this->assertSame(['name' => 'My Room'], json_decode((string) $request->getBody(), true));

        $claims = (array) JWT::decode($this->bearerToken(0), new Key(self::API_SECRET, 'HS256'));

        $this->assertSame(self::API_KEY, $claims['access_key']);
        $this->assertSame('management', $claims['type']);
        $this->assertSame(2, $claims['version']);
        $this->assertNotEmpty($claims['jti']);
        $this->assertSame(24 * 60 * 60, $claims['exp'] - $claims['iat']);
    }

    public function testTheManagementTokenIsCachedBetweenRequests()
    {
        $service = $this->serviceReturning([
            new Response(200, [], '{"data":[]}'),
            new Response(200, [], '{"id":"room-1"}'),
        ]);

        $service->getRooms();
        $service->getRoom('room-1');

        $this->assertSame($this->bearerToken(0), $this->bearerToken(1));
        $this->assertSame($this->bearerToken(0), Cache::get('100ms_management_token'));
    }

    public function testRoomCodesAreRequestedForARole()
    {
        $service = $this->serviceReturning([new Response(200, [], '{"code":"abc-defg-hij","role":"host"}')]);

        $response = $service->generateRoomCodeForRole('room-1', 'host');

        $this->assertSame('abc-defg-hij', $response['code']);
        $this->assertSame('/v2/room-codes/room/room-1/role/host', $this->history[0]['request']->getUri()->getPath());
    }

    public function testRoomsCanBeDisabledAndEnabled()
    {
        $service = $this->serviceReturning([
            new Response(200, [], '{"enabled":false}'),
            new Response(200, [], '{"enabled":true}'),
        ]);

        $service->disableRoom('room-1');
        $service->enableRoom('room-1');

        $this->assertSame(['enabled' => false], json_decode((string) $this->history[0]['request']->getBody(), true));
        $this->assertSame(['enabled' => true], json_decode((string) $this->history[1]['request']->getBody(), true));
        $this->assertSame('/v2/rooms/room-1', $this->history[1]['request']->getUri()->getPath());
    }

    public function testFailedRequestsAreReportedWithTheEndpoint()
    {
        $service = $this->serviceReturning([new Response(500, [], '{"message":"boom"}')]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Request to /v2/rooms failed');

        $service->createRoom(['name' => 'My Room']);
    }

    /**
     * A service whose HTTP client answers from a queue instead of the network.
     *
     * @param  array<int, Response>  $responses
     */
    protected function serviceReturning(array $responses): HmsService
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        $client = new Client([
            'base_uri' => config('100ms.base_url'),
            'handler' => $stack,
        ]);

        $service = new class(self::API_KEY, self::API_SECRET, config('100ms.base_url')) extends HmsService {
            public function useClient(Client $client): void
            {
                $this->client = $client;
            }
        };

        $service->useClient($client);

        return $service;
    }

    protected function bearerToken(int $index): string
    {
        return substr($this->history[$index]['request']->getHeaderLine('Authorization'), strlen('Bearer '));
    }
}
