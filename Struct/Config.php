<?php

namespace SmImageServer\Struct;

use Shopware\Components\Plugin\ConfigReader;

class Config
{
    /**
     * @var string
     */
    public $apiUrl;

    /**
     * @var string
     */
    public $apiToken;

    /**
     * @var string
     */
    public $uuid;

    /**
     * Config constructor.
     *
     * @param ConfigReader
     */
    public function __construct(ConfigReader $configReader, string $pluginName)
    {
        $configurations = $configReader->getByPluginName($pluginName);

        $this->apiUrl   = $configurations['image_server_url'];
        $this->apiToken = $configurations['image_server_token'];
        $this->uuid     = $configurations['image_server_uuid'];
    }
}