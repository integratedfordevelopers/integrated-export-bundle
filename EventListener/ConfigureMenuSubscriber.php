<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ExportBundle\EventListener;

use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Event subscriber for adding menu items to integrated_menu
 *
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
class ConfigureMenuSubscriber implements EventSubscriberInterface
{
    const MENU = 'integrated_menu';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_EXPORT = 'ROLE_EXPORT';
    const MENU_CONTENT = 'Content';

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ConfigureMenuEvent::CONFIGURE => 'onMenuConfigure',
        );
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        if ($menu->getName() !== self::MENU) {
            return;
        }

        if ($this->authorizationChecker->isGranted([self::ROLE_ADMIN, self::ROLE_EXPORT])) {
            if (!$menuContent = $menu->getChild(self::MENU_CONTENT)) {
                $menuContent = $menu->addChild(self::MENU_CONTENT);
            }

            $menuContent->addChild('Export', array('route' => 'integrated_export_homepage'));
        }
    }
}
