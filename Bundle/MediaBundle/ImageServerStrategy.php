<?php

namespace SmImageServer\Bundle\MediaBundle;

use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;
use SmImageServer\Utils\Utils;

class ImageServerStrategy implements StrategyInterface
{
    public function normalize($path)
    {
        // remove filesystem directories
        $path = str_replace('//', '/', $path);

        // remove everything before /media/...
        preg_match('/.*((media\/(?:archive|image|music|pdf|temp|unknown|video|vector)(?:\/thumbnail)?).*\/((.+)\.(.+)))/', $path, $matches);

        if (!empty($matches)) {
            return $matches[2] . '/' . $matches[3];
        }

        return $path;
    }

    public function encode($path)
    {
        $path = $this->normalize($path);

        if ($this->isEncoded($path)) {
            return $path;
        }

        $path       = str_replace("/thumbnail", "", $path);
        $remotePath = $this->buildMediaServerPath($path);

        return $remotePath ?: $path;
    }

    private function buildMediaServerPath($path)
    {
        $width = $height = 0;
        // retina
        if (preg_match("#media/image/(.*)_([\d]+)x([\d]+)(@2x)\.(.*)$#", $path, $matches)) {
            $filename  = $matches[1];
            $width     = $matches[2] * 2;
            $height    = $matches[3] * 2;
            $extension = $matches[4];
        }
        elseif (preg_match("/media\/image\/(.*)_([\d]+)x([\d]+)\.(.*)$/", $path, $matches)) {
            $filename  = $matches[1];
            $width     = $matches[2];
            $height    = $matches[3];
            $extension = $matches[4];
        }
        else {
            $pathinfo  = pathinfo($path);
            $filename  = $pathinfo['filename'];
            $extension = $pathinfo['extension'];
        }

        $path       = sprintf("media/image/%s.%s", $filename, $extension);
        $remotePath = Utils::getRemotePathByLocalPah($path);

        if ($width && $height) {
            return sprintf("%s?w=%s&h=%s", $remotePath, $width, $height);
        }

        return $remotePath ?: $path;
    }

    public function isEncoded($path)
    {
        return preg_match("#[0-9a-z]{1}\/[0-9a-z]{2}\/.*\.*$#", $path);
    }
}