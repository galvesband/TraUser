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

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Galvesband\TraUserBundle\Entity\ResetToken;
use Galvesband\TraUserBundle\Entity\User;
use RandomLib\Factory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager
{
    protected $encoderFactory;
    /** @var  Factory */
    protected $generatorFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory, Factory $generatorFactory)
    {
        $this->encoderFactory = $encoderFactory;
        $this->generatorFactory = $generatorFactory;
    }

    /**
     * @param User $user
     * @return \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface
     */
    public function getEncoder(User $user)
    {
        return $this->encoderFactory->getEncoder($user);
    }

    /**
     * Hashes $user->getPlainPassword() and stores it with $user->setPassword().
     *
     * After this call the plain password will be empty. If
     * getPlainPassword() is empty it does nothing.
     *
     * @param User $user
     */
    public function updateUser(User $user)
    {
        $plainPassword = $user->getPlainPassword();

        $encoder = $this->getEncoder($user);
        $user->setSalt($this->generatorFactory->getMediumStrengthGenerator()->generateString(12));
        $user->setPassword($encoder->encodePassword($plainPassword, $user->getSalt()));
        $user->eraseCredentials();
    }

    public function updateResetToken(ResetToken $token)
    {
        $generator = $this->generatorFactory->getLowStrengthGenerator();
        $newTokenString = $generator
            ->generateString(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $token->setToken($newTokenString);
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $object = $event->getEntity();

        if ($object instanceof User) {
            if (!empty($object->getPlainPassword())) {
                $this->updateUser($object);
                $event->setNewValue('password', $object->getPassword());
                $event->setNewValue('salt', $object->getSalt());
            }
        }
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getEntity();

        if ($object instanceof User) {
            $this->updateUser($object);
        }

        if ($object instanceof ResetToken) {
            $this->updateResetToken($object);
        }
    }
}