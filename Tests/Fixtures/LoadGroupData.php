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
use Galvesband\TraUserBundle\Entity\Role;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Order of fixture loading
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Role $staffRole */
        $staffRole = $this->getReference('staff-role');
        /** @var Role $adminRole */
        $adminRole = $this->getReference('admin-role');

        $staffGroup = new Group();
        $staffGroup->setName("Staffers");
        $staffGroup->setDescription("Usual site workers.");
        $staffGroup->addRole($staffRole);
        $staffRole->addGroup($staffGroup);
        $manager->persist($staffGroup);

        $adminGroup = new Group();
        $adminGroup->setName("Administrators");
        $adminGroup->setDescription("Site administrators.");
        $adminGroup->addRole($adminRole);
        $adminRole->addGroup($adminGroup);
        $manager->persist($adminGroup);

        $manager->flush();

        $this->setReference('staff-group', $staffGroup);
        $this->setReference('admin-group', $adminGroup);
    }
}