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

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Galvesband\TraUserBundle\Entity\Group;
use Galvesband\TraUserBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Order of fixture loading
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Group $staffGroup */
        $staffGroup = $this->getReference('staff-group');
        /** @var Group $adminGroup */
        $adminGroup = $this->getReference('admin-group');

        $staffUser = new User();
        $staffUser->setName('Staffer');
        $staffUser->setEmail('staffer@not-real.net');
        $staffUser->setIsActive(true);
        $staffUser->setPlainPassword('staffer@not-real.net');
        $staffUser->setIsSuperAdmin(false);
        $staffUser->addGroup($staffGroup);
        $staffGroup->addUser($staffUser);
        $manager->persist($staffUser);

        $adminUser = new User();
        $adminUser->setName('Admin');
        $adminUser->setEmail('admin@not-real.net');
        $adminUser->setIsActive(true);
        $adminUser->setPlainPassword('admin@not-real.net');
        $adminUser->setIsSuperAdmin(false);
        $adminUser->addGroup($adminGroup);
        $adminGroup->addUser($adminUser);
        $manager->persist($adminUser);

        $superAdminUser = new User();
        $superAdminUser->setName('SuperAdmin');
        $superAdminUser->setEmail('superadmin@not-real.net');
        $superAdminUser->setIsActive(true);
        $superAdminUser->setPlainPassword('superadmin@not-real.net');
        $superAdminUser->setIsSuperAdmin(true);
        $manager->persist($superAdminUser);

        $inactiveAdmin = new User();
        $inactiveAdmin->setName('InactiveAdmin');
        $inactiveAdmin->setEmail('inactiveadmin@not-real.net');
        $inactiveAdmin->setIsActive(false);
        $inactiveAdmin->setPlainPassword('inactiveadmin@not-real.net');
        $inactiveAdmin->setIsSuperAdmin(false);
        $adminGroup->addUser($inactiveAdmin);
        $inactiveAdmin->addGroup($adminGroup);
        $manager->persist($inactiveAdmin);

        $manager->flush();

        $this->setReference('staff-user', $staffUser);
        $this->setReference('admin-user', $adminUser);
        $this->setReference('super-admin-user', $superAdminUser);
        $this->setReference('inactive-admin-user', $inactiveAdmin);
    }
}