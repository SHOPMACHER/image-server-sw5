<?php

namespace ShopmacherImageServer5\Bundle\MediaBundle;

use ShopmacherImageServer5\Services\CachedStrategy;
use ShopmacherImageServer5\Utils\Config;
use ShopmacherImageServer5\Utils\Utils;
use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;

class ImageServerStrategy implements StrategyInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CachedStrategy
     */
    private $cache;

    /**
     * ImageServerStrategy constructor.
     *
     * @param Config $config
     * @param CachedStrategy $cache
     */
    public function __construct(Config $config, CachedStrategy $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * Normalize given ImageServer HTTP path back to a Shopware virtual path.
     * SYSTEM: "/home/vagrant/www/shopware/media/image/thumbnail/"
     * REMOTE: "project/a/bc/abc.jpg?q=75&w=800&h=800"
     * 
     * @param string $path
     * @return string
     */
    public function normalize($path)
    {
        // check if path is encoded

        if ($this->isEncoded($path)) {
            $urlParams = parse_url($path);

            // query database first for local path by remote path
            $localPath = $this->cache->getLocalPathByRemotePath(str_replace('/images/', '', $urlParams['path']));
            if (0 < strlen($localPath)) {
                return $localPath;
            }

            // otherwise return generated local path
            $pathInfo = pathinfo($urlParams['path']);
            $localPath = 'media/image/' . $pathInfo['basename'];
            return $localPath;
        }

        // otherwise just return given path

        return $path;
    }

    /**
     * Encodes given path for an image and for a thumbnail.
     * IMAGE: "media/image/abc.jpg"
     * THUMB: "media/image/thumbnail/abc_800x800.jpg"
     * 
     * @param string $path
     * @return string
     */
    public function encode($path)
    {
        // return if already encoded
        if ($this->isEncoded($path)) {
            return $path;
        }

        // encode thumbnail
        if (strpos($path, '/thumbnail/') !== false) {
            return $this->encodeThumbnailPath($path);
        }

        // encode image
        return $this->encodeImagePath($path);
    }

    /**
     * Check if given path is in the ImageServer representation
     * IMAGE:  "media/image/abc.jpg"
     * THUMB:  "media/image/thumbnail/abc_800x800.jpg"
     * REMOTE: "project/a/bc/abc.jpg?q=75&w=800&h=800"
     * 
     * @param string $path
     * @return bool
     */
    public function isEncoded($path)
    {
        $projectName = $this->config->getProjectName();

        if (strpos($path, $projectName . '/') !== false) {
            return true;
        }

        return false;
    }

    ### PRIVATE ###

    /**
     * Encode given path to a full ImageServer http path by run a SQL query.
     * If path does not exists in the mapping table, return given path.
     * 
     * @param string $path
     * @return string
     */
    private function encodeImagePath($path)
    {
        $pathinfo  = pathinfo($path);
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];

        // check if path exists in mapping table
        $remotePath = $this->cache->getRemotePathByLocalPath('media/image/' . $filename . '.' . $extension);
        if (0 === strlen($remotePath)) {
            // use default ImageServer encoding when not
            $remotePath = $this->encodeBaseName($filename . '.' . $extension);
        }

        return $remotePath;
    }

    /**
     * Encode thumbnail path to a full ImageServer HTTP path by run a SQL query.
     * Also add query parameters to run operations on the returning image.
     * QUALITY: q=75
     * WIDTH:   w=800
     * HEIGHT:  h=800
     * If path does not exists in the mapping table, return empty string.
     * 
     * @param string $path
     * @return string
     */
    private function encodeThumbnailPath($path)
    {
        $params = [];
        if ($this->config->getQuality() > 0) {
            $params[] = sprintf('q=%s', min($this->config->getQuality(), 100));
        }

        // retina thumbnail
        if (preg_match("#media/image/thumbnail/(.*)_([\d]+)x([\d]+)(@2x)\.(.*)$#", $path, $matches)) {
            $filename  = $matches[1];
            $params[] = sprintf('w=%s', $matches[2] * 2);
            $params[] = sprintf('h=%s', $matches[3] * 2);
            $extension = $matches[5];
        }
        // normal thumbnail
        elseif (preg_match("/media\/image\/thumbnail\/(.*)_([\d]+)x([\d]+)\.(.*)$/", $path, $matches)) {
            $filename  = $matches[1];
            $params[] = sprintf('w=%s', $matches[2]);
            $params[] = sprintf('h=%s', $matches[3]);
            $extension = $matches[4];
        }
        // original image
        else {
            $pathinfo  = pathinfo($path);
            $filename  = $pathinfo['filename'] ?: '';
            $extension = $pathinfo['extension'] ?: '';
        }

        // check if path exists in mapping table
        $remotePath = $this->cache->getRemotePathByLocalPath('media/image/' . $filename . '.' . $extension);
        if (0 === strlen($remotePath)) {
            // use default ImageServer encoding when not
            $remotePath = $this->encodeBaseName($filename . '.' . $extension);
        }

        if (0 < count($params)) {
            $remotePath = sprintf("%s?%s", $remotePath, implode('&', $params));
        }

        return $remotePath;
    }

    /**
     * Encode basename to ImageServer path based on ImageServer logic.
     * 
     * @param string $path
     * @return string
     */
    private function encodeBaseName($basename)
    {
        $hash = md5($basename);
        return sprintf(
            '%s/%s/%s/%s',
            $this->config->getProjectName(),
            substr($hash, 0, 1),
            substr($hash, 1, 2),
            $basename
        );
    }
}
