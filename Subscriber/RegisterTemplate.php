<?php

namespace SmImageServer\Subscriber;

use Enlight_Template_Manager;

class RegisterTemplate implements \Enlight\Event\SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @var Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * RegisterTemplate constructor.
     *
     * @param string                   $pluginDirectory
     * @param Enlight_Template_Manager $templateManager
     */
    public function __construct(string $pluginDirectory, Enlight_Template_Manager $templateManager)
    {
        $this->pluginDirectory = $pluginDirectory;
        $this->templateManager = $templateManager;
    }

    public static function getSubscribedEvents()
    {
        return [
//            'Enlight_Controller_Action_PostDispatch'                            => 'onPreDispatch',
//            'Enlight_Controller_Action_PostDispatchSecure_Backend_MediaManager' => 'onLoadAlbum'
        ];
    }

    public function onPreDispatch(\Enlight_Event_EventArgs $args)
    {
        $subject = $args->get('subject');
        $view    = $subject->View();

        if (!$view) {
            return;
        }

        $this->templateManager->addTemplateDir($this->pluginDirectory . '/Resources/views/');
    }

    public function onLoadAlbum(\Enlight_Event_EventArgs $args): void
    {
        $controller = $args->getSubject();

        $view    = $controller->View();
        $request = $controller->Request();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views/');

        if ($request->getActionName() == 'load') {
            $view->extendsTemplate(
                'backend/extends/media_manager/view/media/view.js'
            );
        }
    }
}