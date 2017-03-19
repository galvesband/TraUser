<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Galvesband\TraUserBundle\DependencyInjection;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Galvesband\TraUserBundle\Entity\ResetToken;
use Galvesband\TraUserBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserManagerTest extends \PHPUnit_Framework_TestCase {

    public function testGetEncoder()
    {
        $userMock = $this->createMock(User::class);
        $encoderMock = $this->createMock(PasswordEncoderInterface::class);
        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);
        $encoderFactoryMock->expects($this->once())
            ->method('getEncoder')
            ->with($userMock)
            ->willReturn($encoderMock);

        $userManager = new UserManager($encoderFactoryMock);
        $this->assertEquals(
            $encoderMock,
            $userManager->getEncoder($userMock)
        );
    }

    public function testUpdateUser()
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('getPlainPassword')
            ->willReturn('test');
        $userMock->expects($this->once())
            ->method('setSalt');
        $userMock->expects($this->once())
            ->method('getSalt');
        $userMock->expects($this->once())
            ->method('setPassword')
            ->with('test-pass');
        $userMock->expects($this->once())
            ->method('eraseCredentials');

        $encoderMock = $this->createMock(PasswordEncoderInterface::class);
        $encoderMock->expects($this->once())
            ->method('encodePassword')
            ->willReturn('test-pass');
        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);
        $encoderFactoryMock->expects($this->once())
            ->method('getEncoder')
            ->with($userMock)
            ->willReturn($encoderMock);

        $userManager = new UserManager($encoderFactoryMock);
        $userManager->updateUser($userMock);
    }

    public function testPreUpdate()
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->exactly(2))
            ->method('getPlainPassword')
            ->willReturn('test');
        $userMock->expects($this->once())
            ->method('setSalt');
        $userMock->expects($this->exactly(2))
            ->method('getSalt');
        $userMock->expects($this->once())
            ->method('setPassword')
            ->with('hashed-pass');
        $userMock->expects($this->exactly(1))
            ->method('getPassword')
            ->willReturn('hashed-pass');
        $userMock->expects($this->once())
            ->method('eraseCredentials');

        $encoderMock = $this->createMock(PasswordEncoderInterface::class);
        $encoderMock->expects($this->once())
            ->method('encodePassword')
            ->willReturn('hashed-pass');
        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);
        $encoderFactoryMock->expects($this->once())
            ->method('getEncoder')
            ->with($userMock)
            ->willReturn($encoderMock);

        $eventMock = $this->createMock(PreUpdateEventArgs::class);
        $eventMock->expects($this->exactly(1))
            ->method('getEntity')
            ->willReturn($userMock);
        $eventMock->expects($this->at(1))
            ->method('setNewValue')
            ->with('password', 'hashed-pass');
        $eventMock->expects($this->at(2))
            ->method('setNewValue');

        $userManager = new UserManager($encoderFactoryMock);
        $userManager->preUpdate($eventMock);
    }

    public function testUserPrePersist()
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->exactly(1))
            ->method('getPlainPassword')
            ->willReturn('test');
        $userMock->expects($this->once())
            ->method('setSalt');
        $userMock->expects($this->exactly(1))
            ->method('getSalt');
        $userMock->expects($this->once())
            ->method('setPassword')
            ->with('hashed-pass');
        $userMock->expects($this->once())
            ->method('eraseCredentials');

        $encoderMock = $this->createMock(PasswordEncoderInterface::class);
        $encoderMock->expects($this->once())
            ->method('encodePassword')
            ->willReturn('hashed-pass');
        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);
        $encoderFactoryMock->expects($this->once())
            ->method('getEncoder')
            ->with($userMock)
            ->willReturn($encoderMock);

        $eventMock = $this->createMock(LifecycleEventArgs::class);
        $eventMock->expects($this->exactly(1))
            ->method('getEntity')
            ->willReturn($userMock);

        $userManager = new UserManager($encoderFactoryMock);
        $userManager->prePersist($eventMock);
    }

    public function testUpdateResetToken()
    {
        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);

        $token = $this->createMock(ResetToken::class);
        $token->expects($this->once())
            ->method('setToken');

        $userManager = new UserManager($encoderFactoryMock);
        $userManager->updateResetToken($token);
    }

    public function testResetTokenPrePersist()
    {
        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);

        $token = $this->createMock(ResetToken::class);
        $token->expects($this->once())
            ->method('setToken');

        $eventMock = $this->createMock(LifecycleEventArgs::class);
        $eventMock->expects($this->once())
            ->method('getEntity')
            ->willReturn($token);

        $userManager = new UserManager($encoderFactoryMock);
        $userManager->prePersist($eventMock);
    }
}