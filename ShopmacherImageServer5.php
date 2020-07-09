<?php

namespace ShopmacherImageServer5;

use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use ShopmacherImageServer5\Bundle\MediaBundle\ImageServerAdapter;
use ShopmacherImageServer5\Bundle\MediaBundle\ImageServerStrategy;

class ShopmacherImageServer5 extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Collect_MediaAdapter_imageserver'  => 'onCollectAdapter',
            'Shopware_Collect_MediaStrategy_imageserver' => 'onCollectStrategy',
        ];
    }

    public function install(InstallContext $context)
    {
        /** @var CrudService $crudService */
        $crudService = $this->container->get('shopware_attribute.crud_service');
        $crudService->update('s_media_attributes', 'image_server_path', 'string');

        $this->container->get('models')->generateAttributeModels(['s_media_attributes']);

        /** @var Enlight_Components_Db_Adapter_Pdo_Mysql $db */
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS sm_imageserver_transfer (
    id INT PRIMARY KEY AUTO_INCREMENT, 
    local_path VARCHAR(255), 
    remote_path VARCHAR(255),
    remote_uuid VARCHAR(255)
    );
SQL;

        $db = $this->container->get('dbal_connection');
        $db->executeQuery($sql);
    }

    /**
     * @return ImageServerAdapter
     */
    public function onCollectAdapter(): ImageServerAdapter
    {
        /** @var ImageServerAdapter $imageServerAdapter */
        $imageServerAdapter = $this->container->get('sm_imageserver.media_adapter');

        return $imageServerAdapter;
    }

    /**
     * @return ImageServerStrategy
     */
    public function onCollectStrategy(): ImageServerStrategy
    {
        /** @var ImageServerStrategy $imageServerStrategy */
        $imageServerStrategy = $this->container->get('sm_imageserver.media_strategy');

        return $imageServerStrategy;
    }

}
