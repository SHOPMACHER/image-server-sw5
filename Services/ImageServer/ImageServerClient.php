<?php

namespace ShopmacherImageServer5\Services\ImageServer;

use Exception;
use GuzzleHttp\Client;
use ShopmacherImageServer5\Struct\Config;

class ImageServerClient
{
    public const BASE_URL = 'https://imageserver.scalecommerce.cloud/api/v1/';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client(
            [
                'base_url' => self::BASE_URL,
                'defaults' => [
                    'headers' => [
                        'x-auth-token' => $config['access_token']
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
                        'uuid'     => $this->config['project_uuid'],
                        'override' => 0
                    ]
                ]
            );

        } catch (Exception $exception) {
            throw new ImageServerClientException(sprintf("Upload %s failed!", $path), $exception->getCode());
        }

        $content = json_decode($response->getBody(), true);

        if (!isset($content['files']) || empty($content['files'])) {
            throw new ImageServerClientException(sprintf("Upload %s failed!", $path));
        }

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

        if (isset($content['error']) && $content['error'] === 1) {
            throw new ImageServerClientException(sprintf("Delete %s failed!", $uuid));
        }

        return !$content['error'];
    }
}
