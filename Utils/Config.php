<?php

namespace ShopmacherImageServer5\Utils;

use Shopware\Components\Plugin\ConfigReader;

class Config
{
    /**
     * @var string
     */
    private $projectName;

    /**
     * @var string
     */
    private $projectUuid;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var int
     */
    private $quality;

    /**
     * @var bool
     */
    private $deleteAfterMigration;

    /**
     * Config constructor.
     *
     * @param ConfigReader $configReader
     * @param string       $pluginName
     */
    public function __construct(ConfigReader $configReader, string $pluginName)
    {
        $config = $configReader->getByPluginName($pluginName);

        $this->accessToken = $config['access_token'];
        $this->apiUrl = $config['api_url'];
        $this->projectName = $config['project_name'];
        $this->projectUuid = $config['project_uuid'];
        $this->quality = (int)$config['quality'];
        $this->deleteAfterMigration = (bool)$config['delete_after_migration'];
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @return string
     */
    public function getProjectUuid()
    {
        return $this->projectUuid;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return int
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @return bool
     */
    public function deleteAfterMigration()
    {
        return $this->deleteAfterMigration;
    }
}
