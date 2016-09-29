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

use Galvesband\TraUserBundle\Entity\User;
use Galvesband\TraUserBundle\Security\Handler\UserSecurityHandler;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Galvesband\TraUserBundle\Admin\UserAdmin;

class UserSecurityHandlerTest extends \PHPUnit_Framework_TestCase {

    public function testReliesOnFallbackIfNotAnUser()
    {
        $userAdmin = new UserAdmin([], 'Galvesband\TraUserBundle\Entity\User', []);

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
            ->with($userAdmin, 'DELETE', $userAdmin)
            ->willReturn(true);

        $userSecurityHandler = new UserSecurityHandler($tokenStorageMock, $fallbackSecurityHandlerMock);

        $this->assertTrue(
            $userSecurityHandler->isGranted($userAdmin, 'DELETE', $userAdmin)
        );
    }

    public function testGrantedUserEditHisOwnUser()
    {
        $userAdmin = new UserAdmin([], 'Galvesband\TraUserBundle\Entity\User', []);

        $someUser = $this->createMock(User::class);
        $someUser->expects($this->exactly(2))
            ->method('hasRole')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(false);
        // Will be asked its id twice, one as the object being edited and one as the user in the auth token
        $someUser->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(1);

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->once())
            ->method('getUser')
            ->willReturn($someUser);
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenMock);

        $fallbackSecurityHandlerMock = $this->createMock(SecurityHandlerInterface::class);

        $userSecurityHandler = new UserSecurityHandler($tokenStorageMock, $fallbackSecurityHandlerMock);

        $this->assertTrue(
            $userSecurityHandler->isGranted($userAdmin, 'EDIT', $someUser)
        );
    }

    public function testWhenUserIsNotSuperAdminAndObjectIs()
    {
        $userAdmin = new UserAdmin([], 'Galvesband\TraUserBundle\Entity\User', []);

        $attributesTrue = ['VIEW', 'SHOW'];
        $attributesFalse = ['EDIT', 'DELETE'];
        $numberOfCalls = count($attributesFalse) + count($attributesTrue);

        $loggedInUser = $this->createMock(User::class);
        $loggedInUser->expects($this->exactly(1*$numberOfCalls))
            ->method('hasRole')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(false);
        $loggedInUser->expects($this->exactly(1*$numberOfCalls))
            ->method('getId')
            ->willReturn(1);

        $subjectUser = $this->createMock(User::class);
        $subjectUser->expects($this->exactly(1*$numberOfCalls))
            ->method('hasRole')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(true);
        $subjectUser->expects($this->exactly(1*$numberOfCalls))
            ->method('getId')
            ->willReturn(2);

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->exactly(1*$numberOfCalls))
            ->method('getUser')
            ->willReturn($loggedInUser);
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->expects($this->exactly(1*$numberOfCalls))
            ->method('getToken')
            ->willReturn($tokenMock);

        $fallbackSecurityHandlerMock = $this->createMock(SecurityHandlerInterface::class);

        $userSecurityHandler = new UserSecurityHandler($tokenStorageMock, $fallbackSecurityHandlerMock);

        foreach ($attributesFalse as $att) {
            $this->assertFalse(
                $userSecurityHandler->isGranted($userAdmin, $att, $subjectUser)
            );
        }
        foreach ($attributesTrue as $att) {
            $this->assertTrue(
                $userSecurityHandler->isGranted($userAdmin, $att, $subjectUser)
            );
        }
    }
}