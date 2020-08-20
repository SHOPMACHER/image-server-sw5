<?php

namespace ShopmacherImageServer5\Utils;

use Shopware\Components\Plugin\ConfigReader;

class Config
{
    private $projectName;
    private $projectUuid;
    private $apiUrl;
    private $accessToken;

    /**
     * @return mixed
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @param mixed $projectName
     */
    public function setProjectName($projectName): void
    {
        $this->projectName = $projectName;
    }

    /**
     * @return mixed
     */
    public function getProjectUuid()
    {
        return $this->projectUuid;
    }

    /**
     * @param mixed $projectUuid
     */
    public function setProjectUuid($projectUuid): void
    {
        $this->projectUuid = $projectUuid;
    }

    /**
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param mixed $apiUrl
     */
    public function setApiUrl($apiUrl): void
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken): void
    {
        $this->accessToken = $accessToken;
    }


    /**
     * Config constructor.
     *
     * @param ConfigReader $configReader
     * @param string       $pluginName
     */
    public function __construct(ConfigReader $configReader, string $pluginName)
    {
        $config = $configReader->getByPluginName($pluginName);

        $this->setAccessToken($config['access_token']);
        $this->setApiUrl($config['api_url']);
        $this->setProjectName($config['project_name']);
        $this->setProjectUuid($config['project_uuid']);
    }
}
