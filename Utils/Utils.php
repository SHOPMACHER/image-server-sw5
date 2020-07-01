<?php

namespace SmImageServer\Utils;

class Utils
{
    public static function buildRemotePath(string $localPath)
    {
        $parsedPath   = parse_url($localPath);
        $path         = $parsedPath['path'] ?? '';

        if(!$path) {
            return  $localPath;
        }

        $queryString  = $parsedPath['query'] ?? '';
        $pathInfo     = pathinfo($path);
        $baseFilename = $pathInfo['basename'] ;
        $md5          = md5($baseFilename);

        if($queryString) {
            $baseFilename .= '?' . $queryString;
        }

        $remotePath   = implode("/", [$md5[0], substr($md5, 1, 2), $baseFilename]);

        return $remotePath;
    }

    public static function deleteImageTransferByRemotePath(string $remotePath): int
    {
        return Shopware()->Db()->delete(
            'sm_imageserver_transfer', 'remote_path = ' . Shopware()->Db()->quote($remotePath)
        );
    }

    public static function getRemotePathByLocalPath(string $localPath)
    {
        $sql = <<<SQL
SELECT remote_path 
FROM sm_imageserver_transfer 
WHERE local_path = :local_path
SQL;

        return Shopware()->Db()->fetchOne($sql, ['local_path' => $localPath]);
    }

    public static function getUuidByRemotePath(string $remotePath)
    {
        $sql = <<<SQL
SELECT remote_uuid 
FROM sm_imageserver_transfer 
WHERE remote_path = :remote_path
SQL;

        return Shopware()->Db()->fetchOne($sql, ['remote_path' => $remotePath]);
    }

    public static function insertImageTransfer(string $localPath, string $remotePath, $remoteUuid)
    {
        $sql = <<<SQL
INSERT 
INTO sm_imageserver_transfer 
SET local_path = ?, 
    remote_path = ?, 
    remote_uuid = ?
SQL;

        return Shopware()->Db()->executeQuery($sql, [$localPath, $remotePath, $remoteUuid]);
    }
}