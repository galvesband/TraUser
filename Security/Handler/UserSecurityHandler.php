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

use Galvesband\TraUserBundle\Entity\User;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSecurityHandler implements SecurityHandlerInterface
{
    protected $tokenStorage;
    protected $fallBackSecurityHandler;

    public function __construct(TokenStorageInterface $tokenStorage, SecurityHandlerInterface $fallbackSecurityHandler)
    {
        $this->fallBackSecurityHandler = $fallbackSecurityHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $userIsSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        $objectIsSuperAdmin = ($object instanceof User && $object->hasRole('ROLE_SUPER_ADMIN'));

        // If the logged in user is the object...
        if ($object instanceof User && $user->getId() === $object->getId()) {
            if ($attributes === 'EDIT') {
                return true;
            }
        }

        if (!$userIsSuperAdmin and $objectIsSuperAdmin) {
            switch ($attributes) {
                case 'VIEW':
                case 'SHOW':
                    return true;
                    break;
                default:
                    return false;
            }
        }

        return $this->fallBackSecurityHandler->isGranted($admin, $attributes, $object);
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