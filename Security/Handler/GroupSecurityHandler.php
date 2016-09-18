<?php

namespace Galvesband\TraUserBundle\Security\Handler;

use Galvesband\TraUserBundle\Entity\Group;
use Galvesband\TraUserBundle\GalvesbandTraUserBundle;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GroupSecurityHandler extends RoleSecurityHandler
{
    protected $tokenStorage;

    public function __construct($authorizationChecker, array $superAdminRoles, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($authorizationChecker, $superAdminRoles);
        $this->tokenStorage = $tokenStorage;
    }

    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $userIsSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        $objectIsSuperAdmin = ($object instanceof Group && $object->hasRole('ROLE_SUPER_ADMIN'));

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