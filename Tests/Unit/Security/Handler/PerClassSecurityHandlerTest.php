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

use Galvesband\TraUserBundle\Admin\UserAdmin;
use Galvesband\TraUserBundle\Entity\Group;
use Galvesband\TraUserBundle\Entity\User;
use Galvesband\TraUserBundle\Security\Handler\PerClassSecurityHandler;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;

class PerClassSecurityHandlerTest extends \PHPUnit_Framework_TestCase {

    public function testIsGranted()
    {
        $userAdmin = new UserAdmin([], 'Galvesband\TraUserBundle\Entity\User', []);

        $roleSecHandlerMoc = $this->createMock(RoleSecurityHandler::class);
        $roleSecHandlerMoc->expects($this->at(0))
            ->method('isGranted')
            ->will($this->returnValue(true));
        $roleSecHandlerMoc->expects($this->at(1))
            ->method('isGranted')
            ->will($this->returnValue(false));

        $noopSecHandlerMoc = $this->createMock('Sonata\AdminBundle\Security\Handler\NoopSecurityHandler');
        $noopSecHandlerMoc->expects($this->at(0))
            ->method('isGranted')
            ->will($this->returnValue(true));
        $noopSecHandlerMoc->expects($this->at(1))
            ->method('isGranted')
            ->will($this->returnValue(true));

        $perClassSecHandler = new PerClassSecurityHandler([
                'role' => $roleSecHandlerMoc,
                'noop' => $noopSecHandlerMoc,
            ],[
                'Galvesband\TraUserBundle\Entity\User' => 'role',
                'Galvesband\\TraUserBundle\\Entity\\Group' => 'role',
                'default' => 'noop'
            ]
        );

        $this->assertTrue(
            $perClassSecHandler->isGranted($userAdmin, [], new User())
        );
        $this->assertFalse(
            $perClassSecHandler->isGranted($userAdmin, [], new Group())
        );
        $this->assertTrue(
            $perClassSecHandler->isGranted($userAdmin, [], $userAdmin)
        );
        $this->assertTrue(
            $perClassSecHandler->isGranted($userAdmin, [], null)
        );
    }
}