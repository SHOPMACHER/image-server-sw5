<?php

namespace SmImageServer\Utils;

class Utils
{
    public static function getRemotePathByLocalPah(string $localPath)
    {
        $sql = <<<SQL
SELECT remote_path 
FROM sm_imageserver_transfer 
WHERE local_path = :local_path
SQL;

        return Shopware()->Db()->fetchOne($sql, ['local_path' => $localPath]);
    }

    public static function getUuidByLocalPath(string $localPath)
    {
        $sql = <<<SQL
SELECT remote_uuid 
FROM sm_imageserver_transfer 
WHERE local_path = :local_path
SQL;

        return Shopware()->Db()->fetchOne($sql, ['local_path' => $localPath]);
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

    public static function deleteImageTransferByRemotePath(string $remotePath): int
    {
        return Shopware()->Db()->delete(
            'sm_imageserver_transfer', 'remote_path = ' . Shopware()->Db()->quote($remotePath)
        );
    }
}