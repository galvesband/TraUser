<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Galvesband\TraUserBundle\Tests\Fixtures;

use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Galvesband\TraUserBundle\Entity\ResetToken;
use Galvesband\TraUserBundle\Entity\User;

class LoadResetTokenData extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 4;
    }

    public function load(ObjectManager $manager)
    {
        /** @var User $inactiveAdmin */
        $inactiveAdmin = $this->getReference('inactive-admin-user');
        $inactiveUserToken = new ResetToken();
        $inactiveAdmin->setToken($inactiveUserToken);
        $inactiveUserToken->setUser($inactiveAdmin);
        $manager->persist($inactiveUserToken);

        /** @var User $staffUser */
        $staffUser = $this->getReference('staff-user');
        $staffUserToken = new ResetToken();
        $staffUser->setToken($staffUserToken);
        $staffUserToken->setUser($staffUser);
        $manager->persist($staffUserToken);

        /** @var User $superAdmin */
        $superAdmin = $this->getReference('super-admin-user');
        $outdatedToken = new ResetToken();
        $outdatedToken->setCreatedAt(new DateTime('-1 week'));
        $superAdmin->setToken($outdatedToken);
        $outdatedToken->setUser($superAdmin);
        $manager->persist($outdatedToken);

        $manager->flush();

        $this->setReference('inactive-user-token', $inactiveUserToken);
        $this->setReference('outdated-token', $outdatedToken);
        $this->setReference('staff-user-token', $staffUserToken);
    }
}