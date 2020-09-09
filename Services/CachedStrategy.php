<?php

namespace ShopmacherImageServer5\Services;

use ShopmacherImageServer5\Utils\Utils;

class CachedStrategy
{
    /**
     * @var array
     */
    private $local = [];

    /**
     * @var array
     */
    private $remote = [];

    /**
     * Returns local path by remote path.
     * On first call the information will be loaded from database.
     * On second call the information will be loaded from cached array.
     * 
     * @param string $remote
     * @return string|bool
     */
    public function getLocalPathByRemotePath(string $remote)
    {
        if (isset($this->local[$remote])) {
            return $this->local[$remote];
        }

        $local = Utils::getLocalPathByRemotePath($remote);
        $this->local[$remote] = $local;

        return $local;
    }

    /**
     * Returns remote path by local path.
     * On first call the information will be loaded from database.
     * On second call the information will be loaded from cached array.
     * 
     * @param string $local
     * @return string|bool
     */
    public function getRemotePathByLocalPath(string $local)
    {
        if (isset($this->remote[$local])) {
            return $this->remote[$local];
        }

        $remote = Utils::getRemotePathByLocalPath($local);
        $this->remote[$local] = $remote;

        return $remote;
    }
}
