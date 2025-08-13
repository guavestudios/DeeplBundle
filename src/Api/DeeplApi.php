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
    private Client $client;

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

    /**
     * @return array<array>
     */
    public function translate(string $text, string $sourceLang, string $targetLang): array
    {
        $sourceLang = str_replace('_', '-', strtoupper($sourceLang));
        $targetLang = str_replace('_', '-', strtoupper($targetLang));

        $response = $this->client->post(
            '/v2/translate',
            [
                'headers' => [
                    'Authorization' => 'DeepL-Auth-Key '.$this->apiKey,
                ],
                'json' => [
                    'text' => [$text],
                    'target_lang' => $targetLang,
                    'source_lang' => $sourceLang,
                ],
                'exceptions' => false,
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return $this->handleResponse($response);
        }

        throw new RuntimeException($response->getStatusCode().':'.$response->getBody()->getContents());
    }

    protected function handleResponse(ResponseInterface $response): array
    {
        $jsonResponse = $response->getBody()->__toString();

        return self::jsonDecode($jsonResponse, true);
    }

    private static function jsonDecode(string $json, bool $assoc = false, int $depth = 512, int $options = 0)
    {
        $data = json_decode($json, $assoc, $depth, $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('json_decode error: '.json_last_error_msg());
        }

        return $data;
    }
}
