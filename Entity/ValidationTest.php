<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Galvesband\TraUserBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ValidationTest extends KernelTestCase
{
    /** @var  \Symfony\Component\Validator\Validator\ValidatorInterface */
    private $validator;

    protected function setUp() {
        self::bootKernel();

        $this->validator = static::$kernel->getContainer()
            ->get('validator');
    }

    public function testUserFieldsValidation() {
        // name not null
        $user = new User();
        $user->setPlainPassword('testPwd');
        $user->setEmail('lerele@lolailo.com');
        $errors = $this->validator->validate($user);
        $this->assertCount(1, $errors);

        // name is not empty
        $user = new User();
        $user->setName('');
        $user->setPlainPassword('testPwd');
        $user->setEmail('lerele@lolailo.com');
        $errors = $this->validator->validate($user);
        $this->assertCount(1, $errors);

        // name at least 4 length
        $user = new User();
        $user->setName('123');
        $user->setPlainPassword('testPwd');
        $user->setEmail('lerele@lolailo.com');
        $errors = $this->validator->validate($user);
        $this->assertCount(1, $errors);

        // email not null
        $user = new User();
        $user->setName('normalName');
        $user->setPlainPassword('testPwd');
        $errors = $this->validator->validate($user);
        $this->assertCount(1, $errors);

        // email not empty
        $user = new User();
        $user->setName('normalName');
        $user->setPlainPassword('testPwd');
        $user->setEmail('');
        $errors = $this->validator->validate($user);
        $this->assertCount(1, $errors);

        // email is email
        $user = new User();
        $user->setName('normalName');
        $user->setPlainPassword('testPwd');
        $user->setEmail('notAnEmail###');
        $errors = $this->validator->validate($user);
        $this->assertCount(1, $errors);

        // valid user
        $user = new User();
        $user->setName('normalName');
        $user->setPlainPassword('testPwd');
        $user->setEmail('lerele@lolailo.com');
        $errors = $this->validator->validate($user);
        $this->assertCount(0, $errors);
    }

    public function testGroupFieldsValidation() {
        // name is not null
        $group = new Group();
        $group->setDescription('Some description');
        $errors = $this->validator->validate($group);
        $this->assertCount(1, $errors);

        // name is not empty
        $group = new Group();
        $group->setName('');
        $group->setDescription('Some description');
        $errors = $this->validator->validate($group);
        $this->assertCount(1, $errors);

        // name is at least 4
        $group = new Group();
        $group->setName('123');
        $group->setDescription('Some description');
        $errors = $this->validator->validate($group);
        $this->assertCount(1, $errors);

        // valid group
        $group = new Group();
        $group->setName('MyGroup');
        $group->setDescription('Some description');
        $errors = $this->validator->validate($group);
        $this->assertCount(0, $errors);
    }

    public function testRoleFieldsValidation() {
        // name is not null
        $role = new Role();
        $role->setDescription('Some description');
        $role->setRole('ROLE_SOMETHING');
        $errors = $this->validator->validate($role);
        $this->assertCount(1, $errors);

        // name is not empty
        $role = new Role();
        $role->setName('');
        $role->setDescription('Some description');
        $role->setRole('ROLE_SOMETHING');
        $errors = $this->validator->validate($role);
        $this->assertCount(1, $errors);

        // name is at least 4
        $role = new Role();
        $role->setName('123');
        $role->setDescription('Some description');
        $role->setRole('ROLE_SOMETHING');
        $errors = $this->validator->validate($role);
        $this->assertCount(1, $errors);

        // Role is not null
        $role = new Role();
        $role->setName('SomeName');
        $role->setDescription('Some description');
        $errors = $this->validator->validate($role);
        $this->assertCount(1, $errors);

        // Role is not empty
        $role = new Role();
        $role->setName('SomeName');
        $role->setRole('');
        $role->setDescription('Some description');
        $errors = $this->validator->validate($role);
        $this->assertCount(1, $errors);

        // Role starts with role
        $role = new Role();
        $role->setName('SomeName');
        $role->setRole('lerele');
        $role->setDescription('Some description');
        $errors = $this->validator->validate($role);
        $this->assertCount(1, $errors);

        // Valid role
        $role = new Role();
        $role->setName('SomeName');
        $role->setRole('ROLE_SOMETHING');
        $role->setDescription('Some description');
        $errors = $this->validator->validate($role);
        $this->assertCount(0, $errors);
    }

    protected function tearDown() {
        parent::tearDown();

        $this->validator = null; // avoid memory leaks
    }
}
