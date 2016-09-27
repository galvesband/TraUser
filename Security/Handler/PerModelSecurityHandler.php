<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Galvesband\TraUserBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;

class PerModelSecurityHandler extends RoleSecurityHandler
{
    protected $handlerMap;
    protected $entityHandlerMap;
    protected $containerInterface;

    public function __construct($authorizationChecker, array $superAdminRoles, array $handlerMap, array $entityHandlerMap)
    {
        parent::__construct($authorizationChecker, $superAdminRoles);
        $this->handlerMap = $handlerMap;
        $this->entityHandlerMap = $entityHandlerMap;
    }

    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!is_null($object)) {
            foreach ($this->entityHandlerMap as $modelName => $handlerName) {
                if ($object instanceof $modelName) {
                    $handler = $this->handlerMap[$handlerName];
                    return $handler->isGranted($admin, $attributes, $object);
                }
            }
        }

        return parent::isGranted($admin, $attributes, $object);
    }
}