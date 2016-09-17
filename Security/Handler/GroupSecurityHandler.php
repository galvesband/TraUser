<?php

namespace Galvesband\TraUserBundle\Security\Handler;

use Galvesband\TraUserBundle\Entity\Group;
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
        if ($object instanceof Group) {
            $user = $this->tokenStorage->getToken()->getUser();
            $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

            if (!$isSuperAdmin and $object->hasRole('ROLE_SUPER_ADMIN')) {
                return false;
            }
        }

        return parent::isGranted($admin, $attributes, $object);
    }
}