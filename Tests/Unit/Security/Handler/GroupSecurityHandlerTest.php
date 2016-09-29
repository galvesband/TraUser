<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Galvesband\Tests\Unit\Security\Handler;

use Galvesband\TraUserBundle\Entity\Group;
use Galvesband\TraUserBundle\Entity\User;
use Galvesband\TraUserBundle\Security\Handler\GroupSecurityHandler;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Galvesband\TraUserBundle\Admin\GroupAdmin;

class GroupSecurityHandlerTest extends \PHPUnit_Framework_TestCase {

    public function testReliesOnFallbackIfNotAGroup()
    {
        $groupAdmin = new GroupAdmin([], 'Galvesband\TraUserBundle\Entity\Group', []);

        $someUser = $this->createMock(User::class);
        $someUser->expects($this->exactly(1))
            ->method('hasRole')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(false);

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->once())
            ->method('getUser')
            ->willReturn($someUser);
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenMock);

        $fallbackSecurityHandlerMock = $this->createMock(SecurityHandlerInterface::class);
        $fallbackSecurityHandlerMock->expects($this->once())
            ->method('isGranted')
            ->with($groupAdmin, 'DELETE', $groupAdmin)
            ->willReturn(true);

        $groupSecurityHandler = new GroupSecurityHandler($tokenStorageMock, $fallbackSecurityHandlerMock);

        $this->assertTrue(
            $groupSecurityHandler->isGranted($groupAdmin, 'DELETE', $groupAdmin)
        );
    }

    public function testWhenUserIsNotSuperAdminAndObjectIs()
    {
        $groupAdmin = new GroupAdmin([], 'Galvesband\TraUserBundle\Entity\Group', []);

        $attributesTrue = ['VIEW', 'SHOW'];
        $attributesFalse = ['EDIT', 'DELETE'];
        $numberOfCalls = count($attributesFalse) + count($attributesTrue);

        $loggedInUser = $this->createMock(User::class);
        $loggedInUser->expects($this->exactly(1*$numberOfCalls))
            ->method('hasRole')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(false);

        $subjectGroup = $this->createMock(Group::class);
        $subjectGroup->expects($this->exactly(1*$numberOfCalls))
            ->method('hasRole')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(true);

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->exactly(1*$numberOfCalls))
            ->method('getUser')
            ->willReturn($loggedInUser);
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->expects($this->exactly(1*$numberOfCalls))
            ->method('getToken')
            ->willReturn($tokenMock);

        $fallbackSecurityHandlerMock = $this->createMock(SecurityHandlerInterface::class);

        $groupSecurityHandler = new GroupSecurityHandler($tokenStorageMock, $fallbackSecurityHandlerMock);

        foreach ($attributesFalse as $att) {
            $this->assertFalse(
                $groupSecurityHandler->isGranted($groupAdmin, $att, $subjectGroup)
            );
        }
        foreach ($attributesTrue as $att) {
            $this->assertTrue(
                $groupSecurityHandler->isGranted($groupAdmin, $att, $subjectGroup)
            );
        }
    }
}