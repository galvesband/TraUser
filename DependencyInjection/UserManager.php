<?php

namespace Galvesband\TraUserBundle\DependencyInjection;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Galvesband\TraUserBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager
{
    protected $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

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

        if (!empty($plainPassword)) {
            $encoder = $this->getEncoder($user);
            $user->setPassword($encoder->encodePassword($plainPassword, $user->getSalt()));
            $user->eraseCredentials();
        }
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $user = $event->getEntity();

        if (!($user instanceof User)) {
            return;
        }

        $this->updateUser($user);
        $event->setNewValue('password', $user->getPassword());
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $user = $event->getEntity();

        if (!($user instanceof User)) {
            return;
        }

        $this->updateUser($user);
    }
}