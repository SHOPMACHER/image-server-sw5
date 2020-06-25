<?php

namespace SmImageServer\Services\ImageServer;

use Exception;
use GuzzleHttp\Client;
use SmImageServer\Struct\Config;

class ImageServerClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    /**
     * ImageServerClient constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->client = new Client(
            [
                'base_url' => $config->apiUrl,
                'defaults' => [
                    'headers' => [
                        'x-auth-token' => $config->apiToken
                    ],
                    'timeout' => 10,
                ]

            ]
        );
    }

    /**
     * @param string $path
     * @param        $resource
     *
     * @return mixed
     * @throws ImageServerClientException
     */
    public function upload(string $path, $resource)
    {
        try {
            $response = $this->client->post(
                'image/upload',
                [
                    'body' => [
                        'images[]' => $resource,
                        'uuid'     => $this->config->uuid,
                        'override' => 0
                    ]
                ]
            );

        } catch (Exception $exception) {
            throw new ImageServerClientException(sprintf("Upload %s failed!", $path), $exception->getCode());
        }

        $content = json_decode($response->getBody(), true);

        $files = $content['files'];
        $file  = reset($files);

        if (!isset($file['path'])) {
            throw new ImageServerClientException(sprintf("Upload %s failed!", $path));
        }

        return $file;
    }

    public function delete(string $uuid): bool
    {
        if (!$uuid) {
            return false;
        }

        try {

            $response = $this->client->delete('image/' . $uuid);
        } catch (Exception $exception) {
            throw new ImageServerClientException(sprintf("Delete image uuid %s failed!", $uuid), $exception->getCode());
        }

        $content = json_decode($response->getBody(), true);

        return !$content['error'];
    }
}