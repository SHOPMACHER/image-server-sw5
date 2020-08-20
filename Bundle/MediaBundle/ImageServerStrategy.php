<?php

namespace ShopmacherImageServer5\Bundle\MediaBundle;

use ShopmacherImageServer5\Utils\Config;
use ShopmacherImageServer5\Utils\Utils;
use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;

class ImageServerStrategy implements StrategyInterface
{
    private $config;

    /**
     * ImageServerStrategy constructor.
     *
     * @param $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function normalize($path)
    {
        //        if (!$this->isEncoded($path)) {
        //            return $path;
        //        }

        // remove everything before /media/...
        preg_match('/.*((media\/(?:archive|image|music|pdf|temp|unknown|video|vector)(?:\/thumbnail)?).*\/((.+)\.(.+)))/', $path, $matches);

        if (!empty($matches)) {
            return $matches[2] . '/' . $matches[3];
        }

        $projectName = $this->config->getProjectName();

        // https://bobshop-imageserver.scalecommerce.cloud/images/stage.bobshop.com/a/d5/bobcash_logo_wortmarke.png
        if (strpos($path, $projectName)) {
            $pathInfo = pathinfo($path);

            return 'media/image/' . $pathInfo['basename'];
        }

        return $path;
    }

    public function encode($path)
    {
        $path = $this->normalize($path);
        $path = str_replace("/thumbnail", "", $path);

        $remotePath = Utils::getRemotePathByLocalPath($path);

        if ($remotePath) {
            return $remotePath;
        }

        if ($this->isEncoded($path)) {
            return $path;
        }

        $remotePath = $this->buildMediaServerPath($path);

        return $remotePath ?: $path;
    }

    private function buildMediaServerPath($path)
    {
        $width = $height = 0;

        // retina thumbnail
        if (preg_match("#media/image/(.*)_([\d]+)x([\d]+)(@2x)\.(.*)$#", $path, $matches)) {
            $filename  = $matches[1];
            $width     = $matches[2] * 2;
            $height    = $matches[3] * 2;
            $extension = $matches[5];
        }
        // normal thumbnail
        elseif (preg_match("/media\/image\/(.*)_([\d]+)x([\d]+)\.(.*)$/", $path, $matches)) {
            $filename  = $matches[1];
            $width     = $matches[2];
            $height    = $matches[3];
            $extension = $matches[4];
        }
        // original image
        else {
            $pathinfo  = pathinfo($path);
            $filename  = $pathinfo['filename'] ?: '';
            $extension = $pathinfo['extension'] ?: '';
        }

        if ($filename && $extension) {
            $remotePath = Utils::getRemotePathByLocalPath(sprintf("media/image/%s.%s", $filename, $extension));

            if (!$remotePath || !$this->isEncoded($remotePath)) {
                return $path;
            }

            if ($width && $height) {
                $remotePath = sprintf("%s?w=%s&h=%s", $remotePath, $width, $height);
            }

            return $remotePath;
        }

        return $path;
    }

    public function isEncoded($path)
    {
        $projectName = $this->config->getProjectName();

        // vundb.dev/7/0b/70755_1.jpg?w=200&h=200
        if (strpos($path, $projectName . '/') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Generates a relative path from given path without asking Database.
     * It will be generated based on the ImageServer rules.
     */
    public function generateMediaServerPath(string $path)
    {
        $lookupPath = strtolower(str_replace('/thumbnail', '', $path));
        $width = $height = 0;

        // retina thumbnail
        if (preg_match("#media/image/(.*)_([\d]+)x([\d]+)(@2x)\.(.*)$#", $lookupPath, $matches)) {
            $filename  = $matches[1];
            $width     = $matches[2] * 2;
            $height    = $matches[3] * 2;
            $extension = $matches[5];
        }
        // normal thumbnail
        elseif (preg_match("/media\/image\/(.*)_([\d]+)x([\d]+)\.(.*)$/", $lookupPath, $matches)) {
            $filename  = $matches[1];
            $width     = $matches[2];
            $height    = $matches[3];
            $extension = $matches[4];
        }
        // original image
        else {
            $pathinfo  = pathinfo($lookupPath);
            $filename  = $pathinfo['filename'] ?: '';
            $extension = $pathinfo['extension'] ?: '';
        }

        if ($filename && $extension) {
            $basename = sprintf('%s.%s', $filename, $extension);
            $hash = md5($basename);
            $static = sprintf(
                '%s/%s/%s/%s',
                $this->config->getProjectName(),
                substr($hash, 0, 1),
                substr($hash, 1, 2),
                $basename
            );

            if ($width && $height) {
                $static = sprintf("%s?w=%s&h=%s", $static, $width, $height);
            }

            return $static;
        }

        return $path;
    }
}
