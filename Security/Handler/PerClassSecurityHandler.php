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
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class PerClassSecurityHandler implements SecurityHandlerInterface
{
    protected $handlerMap;
    protected $classHandlerMap;

    public function __construct(array $handlerMap, array $classHandlerMap)
    {
        $this->handlerMap = $handlerMap;
        $this->classHandlerMap = $classHandlerMap;
        if (!isset($classHandlerMap['default'])) {
            throw new InvalidConfigurationException("There is no default security handler in the class-handler map.");
        }
    }

    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!is_null($object) && isset($this->classHandlerMap[get_class($object)])) {
            $handlerName = $this->classHandlerMap[get_class($object)];
            $handler = $this->handlerMap[$handlerName];
        }
        else {
            $handler = $this->handlerMap[$this->classHandlerMap['default']];
        }

        return $handler->isGranted($admin, $attributes, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRole(AdminInterface $admin)
    {
        return 'ROLE_'.str_replace('.', '_', strtoupper($admin->getCode())).'_%s';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
    }
}