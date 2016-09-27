<?php

use Liip\FunctionalTestBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testSimpleLogin()
    {
        $client = static::makeClient();
        $client->request('GET', '/admin/login');
        $this->assertStatusCode(200, $client);
    }

    public function testAdminRedirectsToLogin()
    {
        $client = static::makeClient();
        $client->request('GET', '/admin/');
        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost/admin/login'),
            'Response to request "/admin/" is a redirect to /admin/login'
        );
    }

    public function testLoginAction()
    {
        // Bootstrap a database
        $this->loadFixtures([
            'Galvesband\TraUserBundle\Tests\Fixtures\LoadRoleData',
            'Galvesband\TraUserBundle\Tests\Fixtures\LoadGroupData',
            'Galvesband\TraUserBundle\Tests\Fixtures\LoadUserData',
        ]);

        // Wrong user name
        // Wrong password
        // Inactive user
        // Success
        throw new Exception("TODO");
    }
}