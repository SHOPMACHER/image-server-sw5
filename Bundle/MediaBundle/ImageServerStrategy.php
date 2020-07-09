<?php

namespace ShopmacherImageServer5\Bundle\MediaBundle;

use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;
use ShopmacherImageServer5\Utils\Utils;

class ImageServerStrategy implements StrategyInterface
{
    public function normalize($path)
    {
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

        if($filename && $extension){
            $remotePath = Utils::getRemotePathByLocalPath(sprintf("media/image/%s.%s", $filename, $extension));

            if(!$remotePath || !$this->isEncoded($remotePath)) {
                return $path;
            }

            if ($width && $height) {
                $remotePath =  sprintf("%s?w=%s&h=%s", $remotePath, $width, $height);
            }

            return $remotePath;
        }

        return $path;
    }

    public function isEncoded($path)
    {
        $projectName = Shopware()->Container()->getParameter('shopware.cdn.adapters.imageserver.auth.project_name');

        // vundb.dev/7/0b/70755_1.jpg?w=200&h=200
        if(strpos($path, $projectName . '/')  === 0){
            return  true;
        }

        return false;
    }
}
