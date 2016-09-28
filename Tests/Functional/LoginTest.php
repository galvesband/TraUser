<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Galvesband\TraUserBundle\Entity\User;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    /** @var  ReferenceRepository */
    private $fixtures;

    public function setUp()
    {
        // Bootstrap a database with some data
        $this->fixtures = $this->loadFixtures([
            'Galvesband\TraUserBundle\Tests\Fixtures\LoadRoleData',
            'Galvesband\TraUserBundle\Tests\Fixtures\LoadGroupData',
            'Galvesband\TraUserBundle\Tests\Fixtures\LoadUserData',
        ])->getReferenceRepository();
    }

    public function testSimpleLogin()
    {
        $client = static::makeClient();
        $loginCrawler = $client->request('GET', '/admin/login');
        $this->assertStatusCode(200, $client);
        $this->assertGreaterThan(
            0,
            $loginCrawler->filter('html div.container form#login-form h2:contains("Identificación")')->count()
        );
        $this->assertGreaterThan(
            0,
            $loginCrawler->filter('html div.container form#login-form[action="/admin/login_check"]')->count()
        );
        $this->assertGreaterThan(
            0,
            $loginCrawler->filter('form#login-form a[href="/admin/forgot_password"]')->count()
        );

        $forgotPasswordLink = $loginCrawler
            ->filter('form#login-form a[href="/admin/forgot_password"]')
            ->eq(0)
            ->link();

        $recoverPasswordCrawler = $client->click($forgotPasswordLink);
        $this->assertStatusCode(200, $client);
        $this->assertGreaterThan(
            0,
            $recoverPasswordCrawler->filter('html div.container form#forgot-password-form')->count()
        );
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

    public function testLoginWrongName()
    {
        $client = static::makeClient();
        $loginCrawler = $client->request('GET', '/admin/login');

        $buttonNode = $loginCrawler->selectButton('Identificar');
        $form = $buttonNode->form([
            '_username' => 'WrongUserName',
            '_password' => 'lalala',
        ]);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost/admin/login')
        );
        $responseCrawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $responseCrawler->filter('div#error-message')->count()
        );
    }

    public function testLoginWrongPassword()
    {
        $client = static::makeClient();
        $loginCrawler = $client->request('GET', '/admin/login');
        $buttonNode = $loginCrawler->selectButton('Identificar');
        $form = $buttonNode->form([
            '_username' => 'Admin',
            '_password' => 'wrong password',
        ]);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost/admin/login')
        );
        $responseCrawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $responseCrawler->filter('div#error-message')->count()
        );
    }

    public function testLoginInactiveUser()
    {
        $client = static::makeClient();
        $loginCrawler = $client->request('GET', '/admin/login');
        $buttonNode = $loginCrawler->selectButton('Identificar');
        $form = $buttonNode->form([
            '_username' => 'InactiveAdmin',
            '_password' => 'inactiveadmin@not-real.net',
        ]);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost/admin/login')
        );
        $responseCrawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $responseCrawler->filter('div#error-message')->count()
        );
    }

    public function testLoginSuccess()
    {
        $client = static::makeClient();
        $loginCrawler = $client->request('GET', '/admin/login');
        $buttonNode = $loginCrawler->selectButton('Identificar');
        $form = $buttonNode->form([
            '_username' => 'Admin',
            '_password' => 'admin@not-real.net',
        ]);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost/admin/dashboard'),
            'successful login redirects'
        );
        $client->followRedirect();

        /** @var User $user */
        $user = self::$kernel->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertFalse(is_null($user));
        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->getName() === 'Admin');
    }
}