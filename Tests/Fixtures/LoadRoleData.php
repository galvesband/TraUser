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
use Galvesband\TraUserBundle\Entity\Role;

class LoadRoleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Order of fixture loading
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $staffRole = new Role();
        $staffRole->setName("Staff");
        $staffRole->setDescription("Allows to create, edit or delete content but can not touch users.");
        $staffRole->setRole('ROLE_STAFF');
        $manager->persist($staffRole);

        $adminRole = new Role();
        $adminRole->setName("Admin");
        $adminRole->setDescription("Allows to create, edit or delete users and includes everything Staff can do.");
        $adminRole->setRole('ROLE_ADMIN');
        $manager->persist($adminRole);

        $manager->flush();

        $this->addReference('staff-role', $staffRole);
        $this->addReference('admin-role', $adminRole);
    }
}