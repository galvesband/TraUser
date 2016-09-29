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
use RandomLib\Factory;
use RandomLib\Generator;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserManagerTest extends \PHPUnit_Framework_TestCase {

    public function testGetEncoder()
    {
        $generatorFactoryMock = $this->createMock(Factory::class);

        $userMock = $this->createMock(User::class);
        $encoderMock = $this->createMock(PasswordEncoderInterface::class);
        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);
        $encoderFactoryMock->expects($this->once())
            ->method('getEncoder')
            ->with($userMock)
            ->willReturn($encoderMock);

        $userManager = new UserManager($encoderFactoryMock, $generatorFactoryMock);
        $this->assertEquals(
            $encoderMock,
            $userManager->getEncoder($userMock)
        );
    }

    public function testUpdateUser()
    {
        $mediumStrengthGeneratorMock = $this->createMock(Generator::class);
        $mediumStrengthGeneratorMock->expects($this->at(0))
            ->method('generateString')
            ->with(12)
            ->willReturn('123456789012');
        $generatorFactoryMock = $this->createMock(Factory::class);
        $generatorFactoryMock->expects($this->once())
            ->method('getMediumStrengthGenerator')
            ->willReturn($mediumStrengthGeneratorMock);

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('getPlainPassword')
            ->willReturn('test');
        $userMock->expects($this->once())
            ->method('setSalt')
            ->with('123456789012');
        $userMock->expects($this->once())
            ->method('getSalt')
            ->willReturn('123456789012');
        $userMock->expects($this->once())
            ->method('setPassword')
            ->with('test-pass');
        $userMock->expects($this->once())
            ->method('eraseCredentials');

        $encoderMock = $this->createMock(PasswordEncoderInterface::class);
        $encoderMock->expects($this->once())
            ->method('encodePassword')
            ->with('test', '123456789012')
            ->willReturn('test-pass');
        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);
        $encoderFactoryMock->expects($this->once())
            ->method('getEncoder')
            ->with($userMock)
            ->willReturn($encoderMock);

        $userManager = new UserManager($encoderFactoryMock, $generatorFactoryMock);
        $userManager->updateUser($userMock);
    }

    public function testPreUpdate()
    {
        $mediumStrengthGeneratorMock = $this->createMock(Generator::class);
        $mediumStrengthGeneratorMock->expects($this->at(0))
            ->method('generateString')
            ->with(12)
            ->willReturn('123456789012');
        $generatorFactoryMock = $this->createMock(Factory::class);
        $generatorFactoryMock->expects($this->once())
            ->method('getMediumStrengthGenerator')
            ->willReturn($mediumStrengthGeneratorMock);

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->exactly(2))
            ->method('getPlainPassword')
            ->willReturn('test');
        $userMock->expects($this->once())
            ->method('setSalt')
            ->with('123456789012');
        $userMock->expects($this->exactly(2))
            ->method('getSalt')
            ->willReturn('123456789012');
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
            ->with('test', '123456789012')
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
            ->method('setNewValue')
            ->with('salt', '123456789012');

        $userManager = new UserManager($encoderFactoryMock, $generatorFactoryMock);
        $userManager->preUpdate($eventMock);
    }

    public function testUserPrePersist()
    {
        $mediumStrengthGeneratorMock = $this->createMock(Generator::class);
        $mediumStrengthGeneratorMock->expects($this->at(0))
            ->method('generateString')
            ->with(12)
            ->willReturn('123456789012');
        $generatorFactoryMock = $this->createMock(Factory::class);
        $generatorFactoryMock->expects($this->once())
            ->method('getMediumStrengthGenerator')
            ->willReturn($mediumStrengthGeneratorMock);

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->exactly(1))
            ->method('getPlainPassword')
            ->willReturn('test');
        $userMock->expects($this->once())
            ->method('setSalt')
            ->with('123456789012');
        $userMock->expects($this->exactly(1))
            ->method('getSalt')
            ->willReturn('123456789012');
        $userMock->expects($this->once())
            ->method('setPassword')
            ->with('hashed-pass');
        $userMock->expects($this->once())
            ->method('eraseCredentials');

        $encoderMock = $this->createMock(PasswordEncoderInterface::class);
        $encoderMock->expects($this->once())
            ->method('encodePassword')
            ->with('test', '123456789012')
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

        $userManager = new UserManager($encoderFactoryMock, $generatorFactoryMock);
        $userManager->prePersist($eventMock);
    }

    public function testUpdateResetToken()
    {
        $generatorMock = $this->createMock(Generator::class);
        $generatorMock->expects($this->once())
            ->method('generateString')
            ->with(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
            ->willReturn('testtoken');

        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);
        $generatorFactoryMock = $this->createMock(Factory::class);
        $generatorFactoryMock->expects($this->once())
            ->method('getLowStrengthGenerator')
            ->willReturn($generatorMock);

        $token = $this->createMock(ResetToken::class);
        $token->expects($this->once())
            ->method('setToken')
            ->with('testtoken');

        $userManager = new UserManager($encoderFactoryMock, $generatorFactoryMock);
        $userManager->updateResetToken($token);
    }

    public function testResetTokenPrePersist()
    {
        $generatorMock = $this->createMock(Generator::class);
        $generatorMock->expects($this->once())
            ->method('generateString')
            ->with(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
            ->willReturn('testtoken');

        $encoderFactoryMock = $this->createMock(EncoderFactoryInterface::class);
        $generatorFactoryMock = $this->createMock(Factory::class);
        $generatorFactoryMock->expects($this->once())
            ->method('getLowStrengthGenerator')
            ->willReturn($generatorMock);

        $token = $this->createMock(ResetToken::class);
        $token->expects($this->once())
            ->method('setToken')
            ->with('testtoken');

        $eventMock = $this->createMock(LifecycleEventArgs::class);
        $eventMock->expects($this->once())
            ->method('getEntity')
            ->willReturn($token);

        $userManager = new UserManager($encoderFactoryMock, $generatorFactoryMock);
        $userManager->prePersist($eventMock);
    }
}