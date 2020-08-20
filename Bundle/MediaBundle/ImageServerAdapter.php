<?php

namespace ShopmacherImageServer5\Bundle\MediaBundle;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;
use RuntimeException;
use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;
use ShopmacherImageServer5\Services\ImageServer\ImageServerClient;
use ShopmacherImageServer5\Services\ImageServer\ImageServerClientException;
use ShopmacherImageServer5\Utils\Utils;
use ShopmacherImageServer5\Utils\Config as PluginConfig;

/**
 * Class ImageServerAdapter
 */
class ImageServerAdapter extends AbstractAdapter
{

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @var ImageServerClient
     */
    private $imageServerClient;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var string
     */
    private $projectName;

    /**
     * ImageServerAdapter constructor.
     *
     * @param ImageServerClient $client
     * @param ModelManager      $modelManager
     * @param StrategyInterface $strategy
     * @param Config            $config
     */
    public function __construct(ImageServerClient $client, ModelManager $modelManager, StrategyInterface $strategy, PluginConfig $config)
    {
        $this->strategy          = $strategy;
        $this->imageServerClient = $client;
        $this->modelManager      = $modelManager;
        $this->config = $config;
        $this->projectName = $config->getProjectName();
    }

    public function write($path, $contents, Config $config)
    {
        $path     = $this->strategy->encode($path);
        $filename = sys_get_temp_dir() . '/' . basename($path);

        $stream = fopen($filename, 'w+b');
        fwrite($stream, $contents);
        rewind($stream);
        $result = $this->writeStream($path, $stream, $config, $isUpload = true);
        fclose($stream);
        unlink($filename);

        if ($result === false) {
            return false;
        }

        $result['contents'] = $contents;
        $result['mimetype'] = Util::guessMimeType($path, $contents);

        return $result;
    }

    /**
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     * @param bool     $isUpload
     *
     * @return array|false|void
     * @throws ImageServerClientException
     */
    public function writeStream($path, $resource, Config $config, $isUpload = false)
    {
        if(!$isUpload) {
            $media = $this->modelManager->getRepository(Media::class)->findOneBy(['path' => $path]);

            // Only migrate media which path is existed.
            if(!$media) {
                return true;
            }
        }

        $remoteImage = $this->imageServerClient->upload($path, $resource);
        $remotePath = $remoteImage['path'];
        $remoteUUID = $remoteImage['uuid'];
        $remotePath = $this->projectName . '/' . $remotePath;

        Utils::insertImageTransfer($path, $remotePath, $remoteUUID);

        return [
            'path'       => $remotePath,
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
        ];
    }

    public function update($path, $contents, Config $config)
    {
        throw new RuntimeException(sprintf('"%s" function is not implemented for "%s"', __FUNCTION__, $path));
    }

    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }

    public function rename($path, $newpath)
    {
        return true;
    }

    public function copy($path, $newpath)
    {
        return true;
    }

    public function delete($path)
    {
        $uuid   = Utils::getUuidByRemotePath($path);
        $result = $this->imageServerClient->delete($uuid);

        if (!$result) {
            return false;
        }

        return Utils::deleteImageTransferByRemotePath($path);
    }

    public function deleteDir($dirname)
    {
        return true;
    }

    public function createDir($dirname, Config $config)
    {
       return true;
    }

    public function setVisibility($path, $visibility)
    {
        return compact('visibility');
    }

    public function has($path)
    {
        if($this->strategy->isEncoded($path)){
            return true;
        }

        $remotePath = Utils::getRemotePathByLocalPath($path);

        if ($this->strategy->isEncoded($remotePath)) {
            return true;
        }

        return false;
    }

    public function read($path)
    {
        $mediaUrl = Shopware()->Container()->getParameter('shopware.cdn.adapters.ImageServer.mediaUrl');
        $mediaUrl = rtrim($mediaUrl, '/');

        if (strpos($path, $mediaUrl) === false) {
            $path = implode('/', [$mediaUrl, $path]);
        }

        return [
            'contents' => file_get_contents($path)
        ];
    }

    public function readStream($path)
    {
        throw new RuntimeException(sprintf('"%s" function is not implemented for "%s"', __FUNCTION__, $path));
    }

    public function listContents($directory = '', $recursive = false)
    {
        throw new RuntimeException(sprintf('"%s" function is not implemented', __FUNCTION__));
    }

    public function getMetadata($path)
    {
        throw new RuntimeException(sprintf('"%s" function is not implemented for "%s"', __FUNCTION__, $path));
    }

    public function getSize($path)
    {
    }

    public function getMimetype($path)
    {
        throw new RuntimeException(sprintf('"%s" function is not implemented for "%s"', __FUNCTION__, $path));

    }

    public function getTimestamp($path)
    {
        throw new RuntimeException(sprintf('"%s" function is not implemented for "%s"', __FUNCTION__, $path));
    }

    public function getVisibility($path)
    {
        return AdapterInterface::VISIBILITY_PUBLIC;
    }
}
