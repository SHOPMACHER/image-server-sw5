<?php

namespace ShopmacherImageServer5\Services;

use ShopmacherImageServer5\Bundle\MediaBundle\ImageServerStrategy;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;

class MediaService implements MediaServiceInterface
{
    /**
     * @var MediaServiceInterface
     */
    private $service;

    /**
     * @var ImageServerStrategy
     */
    private $strategy;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $mediaUrl = '';

    public function __construct(
        MediaServiceInterface $service,
        ImageServerStrategy $strategy,
        array $config
    ) {
        $this->service = $service;
        $this->strategy = $strategy;
        $this->config = $config;

        if (
            isset($config['adapters'])
            && isset($config['adapters']['imageserver'])
            && isset($config['adapters']['imageserver']['mediaUrl'])
        ) {
            $this->mediaUrl = rtrim($config['adapters']['imageserver']['mediaUrl'], '/');
        }
    }

    public function getUrl($path)
    {
        if ('imageserver' !== $this->config['backend']) {
            return $this->service->getUrl($path);
        }

        if (empty($path)) {
            return null;
        }

        if ($this->strategy->isEncoded($path)) {
            return $this->mediaUrl . '/' . ltrim($path, '/');
        }

        $static = $this->strategy->generateMediaServerPath($path);
        return $this->mediaUrl . '/' . ltrim($static, '/');
    }

    public function read($path)
    {
        return $this->service->read($path);
    }

    public function readStream($path)
    {
        return $this->service->readStream($path);
    }

    public function write($path, $contents, $append = false)
    {
        return $this->service->write($path, $contents, $append);
    }

    public function listFiles($directory = '')
    {
        return $this->service->listFiles($directory);
    }

    public function writeStream($path, $resource, $append = false)
    {
        return $this->service->writeStream($path, $resource, $append);
    }

    public function has($path)
    {
        return $this->service->has($path);
    }

    public function delete($path)
    {
        return $this->service->delete($path);
    }

    public function getSize($path)
    {
        return $this->service->getSize($path);
    }

    public function rename($path, $newpath)
    {
        return $this->service->rename($path, $newpath);
    }

    public function normalize($path)
    {
        return $this->service->normalize($path);
    }

    public function encode($path)
    {
        return $this->serivce->encode($path);
    }

    public function isEncoded($path)
    {
        return $this->service->isEncoded($path);
    }

    public function getAdapterType()
    {
        return $this->service->getAdapterType();
    }

    public function createDir($dirname)
    {
        return $this->serialize->createDir($dirname);
    }

    public function migrateFile($path)
    {
        return $this->service->migrateFile($path);
    }

    public function getFilesystem()
    {
        return $this->service->getFilesystem();
    }
}
