<?php

namespace ShopmacherImageServer5\Subscriber;

use Enlight_Template_Manager;

class RegisterTemplate implements \Enlight\Event\SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * RegisterTemplate constructor.
     *
     * @param string $pluginDirectory
     */
    public function __construct(string $pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_MediaManager' => 'onMediaPostDispatch'
        ];
    }

    public function onMediaPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Customer $controller */
        $controller = $args->getSubject();

        $view = $controller->View();
        $request = $controller->Request();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        if ($request->getActionName() == 'load') {
            $view->extendsTemplate('backend/extends/media_manager/view/media/view.js');
            $view->extendsTemplate('backend/extends/media_manager/view/media/grid.js');
        }
    }
}
