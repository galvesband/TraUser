<?php

namespace Galvesband\TraUserBundle\Security\Handler;

use Galvesband\TraUserBundle\Entity\User;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSecurityHandler extends RoleSecurityHandler
{
    protected $tokenStorage;

    public function __construct($authorizationChecker, array $superAdminRoles, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($authorizationChecker, $superAdminRoles);
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

        return parent::isGranted($admin, $attributes, $object);
    }
}