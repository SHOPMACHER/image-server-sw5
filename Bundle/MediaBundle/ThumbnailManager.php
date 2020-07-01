<?php

namespace SmImageServer\Bundle\MediaBundle;

use Enlight_Event_EventManager;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Thumbnail\Generator\GeneratorInterface;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Media;

class ThumbnailManager extends Manager
{
    private $mediaService;

    public function __construct(GeneratorInterface $generator,
                                string $rootDir,
                                Enlight_Event_EventManager $eventManager,
                                MediaServiceInterface $mediaService)
    {
        $this->mediaService = $mediaService;

        parent::__construct($generator, $rootDir, $eventManager, $mediaService);
    }


    public function createMediaThumbnail(Media $media, $thumbnailSizes = array(), $keepProportions = false)
    {
        if ($this->mediaService->getAdapterType() === 'ImageServer'){
            return;
        }

        return parent::createMediaThumbnail($media, $thumbnailSizes, $keepProportions);
    }
}