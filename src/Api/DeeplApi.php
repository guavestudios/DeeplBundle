<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Api;

use Guave\DeeplBundle\Config\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class DeeplApi
{
    protected string $apiKey;

    public function __construct(string $deeplApiKey, Config $config)
    {
        $url = 'https://api.deepl.com';
        if ($config->isFreeApi()) {
            $url = 'https://api-free.deepl.com';
        }

        $this->client = new Client(
            [
                'base_uri' => $url,
                'timeout' => 8.0,
            ]
        );

        $this->apiKey = $deeplApiKey;
    }

    public function translate(string $text, string $sourceLang, string $targetLang)
    {
        $response = $this->client->post(
            '/v2/translate',
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'auth_key' => $this->apiKey,
                    'text' => $text,
                    'target_lang' => $targetLang,
                    'source_lang' => $sourceLang,
                ],
                'exceptions' => false,
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return $this->handleResponse($response);
        }

        throw new RuntimeException($response->getStatusCode() . ':' . $response->getBody()->getContents());
    }

    protected function handleResponse(ResponseInterface $response): array
    {
        $jsonResponse = $response->getBody()->__toString();

        return self::jsonDecode($jsonResponse, true);
    }

    private static function jsonDecode(string $json, bool $assoc = false, int $depth = 512, int $options = 0)
    {
        $data = json_decode($json, $assoc, $depth, $options);
        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('json_decode error: ' . \json_last_error_msg());
        }

        return $data;
    }
}
