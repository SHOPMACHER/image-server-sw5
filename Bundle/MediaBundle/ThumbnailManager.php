<?php

namespace SmImageServer\Bundle\MediaBundle;

use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Media;

class ThumbnailManager extends Manager
{
    public function __construct()
    {

    }

    public function createMediaThumbnail(Media $media, $thumbnailSizes = array(), $keepProportions = false)
    {

    }
}