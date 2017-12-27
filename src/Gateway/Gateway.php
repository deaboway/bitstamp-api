<?php

namespace Shapecode\Bitstamp\Gateway;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class Gateway
 *
 * @package Shapecode\Bitstamp\Gateway
 * @author  Nikita Loges
 */
class Gateway
{

    /**  */
    const API_URI = 'https://www.bitstamp.net/api/v2';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => trim(self::API_URI, '/'),
        ]);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getApiUrn(array $params = []): string
    {
        $path = '';
        if ($params) {
            $params = array_map(function ($el) {
                return trim($el, '/');
            }, $params);
            $path = implode('/', $params);
        }

        return sprintf(
            '%s/%s',
            rtrim($this->getApiUri(), '/'),
            $path . '/'
        );
    }

    /**
     * @return string
     */
    public function getApiUri(): string
    {
        return self::API_URI;
    }

    /**
     * @param string $method
     * @param string $request
     * @param array  $options
     *
     * @return ResponseInterface
     */
    public function send(string $method, string $request, array $options = []): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->client->request($method, $request, $options);

        return $response;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array
     * @throws \RuntimeException
     */
    public function processResponse(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array  $items
     * @param string $className
     *
     * @return array|object[]
     */
    public function deserializeItems(array $items, string $className)
    {
        foreach ($items as $key => $item) {
            $items[$key] = $this->deserializeItem($item, $className);
        }

        return $items;
    }

    /**
     * @param array  $item
     * @param string $className
     *
     * @return object
     */
    public function deserializeItem(array $item, string $className)
    {
        static $serializer = null;

        if ($serializer === null) {
            $normalizers = [
                new GetSetMethodNormalizer(null, new CamelCaseToSnakeCaseNameConverter()),
                new DateTimeNormalizer(),
                new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter()),
            ];
            $serializer = $serializer = new Serializer($normalizers, [new JsonEncoder()]);
        }

        return $serializer->deserialize(json_encode($item), $className, 'json');
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $headers
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, string $uri, array $headers = []): RequestInterface
    {
        return new Request($method, $uri, $headers);
    }
}
