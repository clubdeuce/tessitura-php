<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Exceptions\ApiException;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Tests\testCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;

#[CoversClass(Api::class)]
#[UsesClass(ApiException::class)]
class ApiErrorHandlingTest extends testCase
{
    /**
     * A non-200 response body is captured once and preserved in both the log and the thrown exception.
     *
     * @throws Exception
     */
    public function testNon200ResponseBodyPreservedInException(): void
    {
        $errorBody = '{"error":"Not Found","message":"The requested resource was not found"}';

        $response = new Response(404, [], $errorBody);
        $client   = $this->createMock(Client::class);
        $client->method('get')->willReturn($response);

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/'], $client);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($errorBody);
        $this->expectExceptionCode(404);

        $api->get('test-endpoint');
    }

    /**
     * A non-200 response with an empty body results in an ApiException with an empty message.
     *
     * @throws Exception
     */
    public function testNon200ResponseWithEmptyBody(): void
    {
        $response = new Response(500, [], '');
        $client   = $this->createMock(Client::class);
        $client->method('get')->willReturn($response);

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/'], $client);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('');

        $api->get('test-endpoint');
    }

    /**
     * A Guzzle ClientException (4xx) is normalized to an ApiException with the response body.
     *
     * @throws Exception
     */
    public function testGuzzleClientExceptionNormalizedToApiException(): void
    {
        $errorBody = '{"error":"Unauthorized"}';
        $request   = new Request('GET', 'https://api.tessitura.com/test-endpoint');
        $response  = new Response(401, [], $errorBody);

        $client = $this->createMock(Client::class);
        $client->method('get')->willThrowException(new ClientException('Client error', $request, $response));

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/'], $client);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($errorBody);
        $this->expectExceptionCode(401);

        $api->get('test-endpoint');
    }

    /**
     * A Guzzle ServerException (5xx) is normalized to an ApiException with the response body.
     *
     * @throws Exception
     */
    public function testGuzzleServerExceptionNormalizedToApiException(): void
    {
        $errorBody = '{"error":"Internal Server Error"}';
        $request   = new Request('GET', 'https://api.tessitura.com/test-endpoint');
        $response  = new Response(503, [], $errorBody);

        $client = $this->createMock(Client::class);
        $client->method('get')->willThrowException(new ServerException('Server error', $request, $response));

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/'], $client);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($errorBody);
        $this->expectExceptionCode(503);

        $api->get('test-endpoint');
    }

    /**
     * A Guzzle RequestException without a response falls back to the exception message.
     *
     * @throws Exception
     */
    public function testGuzzleRequestExceptionWithoutResponseUsesExceptionMessage(): void
    {
        $request = new Request('GET', 'https://api.tessitura.com/test-endpoint');

        $client = $this->createMock(Client::class);
        $client->method('get')->willThrowException(new RequestException('Connection refused', $request));

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/'], $client);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Connection refused');
        $this->expectExceptionCode(0);

        $api->get('test-endpoint');
    }

    /**
     * A Guzzle exception with an empty response body falls back to the exception message.
     *
     * @throws Exception
     */
    public function testGuzzleExceptionWithEmptyBodyFallsBackToExceptionMessage(): void
    {
        $request  = new Request('GET', 'https://api.tessitura.com/test-endpoint');
        $response = new Response(400, [], '');

        $client = $this->createMock(Client::class);
        $client->method('get')->willThrowException(new ClientException('Bad request', $request, $response));

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/'], $client);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad request');
        $this->expectExceptionCode(400);

        $api->get('test-endpoint');
    }

    /**
     * The ApiException wraps the original Guzzle exception as the previous exception.
     *
     * @throws Exception
     */
    public function testApiExceptionWrapsPreviousGuzzleException(): void
    {
        $request         = new Request('GET', 'https://api.tessitura.com/test-endpoint');
        $response        = new Response(404, [], 'Not found');
        $guzzleException = new ClientException('Not found', $request, $response);

        $client = $this->createMock(Client::class);
        $client->method('get')->willThrowException($guzzleException);

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/'], $client);

        try {
            $api->get('test-endpoint');
            $this->fail('Expected ApiException was not thrown');
        } catch (ApiException $e) {
            $this->assertSame($guzzleException, $e->getPrevious());
        }
    }

    /**
     * Error responses are logged when a logger is set.
     *
     * @throws Exception
     */
    public function testErrorResponseIsLogged(): void
    {
        $errorBody = '{"error":"Not Found"}';
        $response  = new Response(404, [], $errorBody);

        $client = $this->createMock(Client::class);
        $client->method('get')->willReturn($response);

        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->once())
               ->method('info')
               ->with($this->stringContains($errorBody));

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/'], $client, $logger);

        try {
            $api->get('test-endpoint');
        } catch (ApiException $e) {
            // expected
        }
    }
}
