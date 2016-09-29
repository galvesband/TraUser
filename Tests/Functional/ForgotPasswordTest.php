<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Galvesband\TraUserBundle\Tests\Functional;

use DateTime;
use Galvesband\TraUserBundle\Entity\ResetToken;
use Galvesband\TraUserBundle\Entity\User;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Swift_Message;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;

class ForgotPasswordTest extends WebTestCase
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
            'Galvesband\TraUserBundle\Tests\Fixtures\LoadResetTokenData',
        ])->getReferenceRepository();
    }

    public function testForgotPasswordView()
    {
        $client = static::makeClient();
        $crawler = $client->request('GET', '/admin/forgot_password');
        $this->assertStatusCode(200, $client);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html div.container form#forgot-password-form h2')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html div.container form#forgot-password-form[action="/admin/forgot_password"]')->count()
        );
    }

    public function testForgotPasswordWrongUserName()
    {
        // Check sending a wrong user name results in a success message alongside no new token or email sent

        $client = static::makeClient();
        $crawler = $client->request('GET', '/admin/forgot_password');
        $this->assertEquals(self::$kernel->getContainer()->has('profiler'), true);

        $buttonNode = $crawler->selectButton('Recuperar Contraseña');
        $form = $buttonNode->form([
            'username' => 'WrongUserName',
            'email' => 'someemail@not-real.net',
        ]);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('/admin/login')
        );

        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(0, $mailCollector->getMessageCount());

        $tokenRepo = self::$kernel->getContainer()->get('doctrine')->getRepository('GalvesbandTraUserBundle:ResetToken');
        $this->assertCount(
            3, // Fixtures contains 3 tokens, so this means there is no new token
            $tokenRepo->findAll()
        );

        $responseCrawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $responseCrawler->filter('div#password-reset')->count()
        );
    }

    public function testForgotPasswordWrongEmail()
    {
        // Check sending a wrong email results in a success message alongside no new token or email sent

        /** @var User $adminUser */
        $adminUser = $this->fixtures->getReference('inactive-admin-user');
        $client = static::makeClient();
        $crawler = $client->request('GET', '/admin/forgot_password');
        $this->assertEquals(self::$kernel->getContainer()->has('profiler'), true);

        $buttonNode = $crawler->selectButton('Recuperar Contraseña');
        $form = $buttonNode->form([
            'username' => $adminUser->getName(),
            'email' => 'someemail@not-real.net',
        ]);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('/admin/login')
        );

        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(0, $mailCollector->getMessageCount());

        $tokenRepo = self::$kernel->getContainer()
            ->get('doctrine')
            ->getRepository('GalvesbandTraUserBundle:ResetToken');
        $this->assertCount(
            3, // Fixtures contains 3 tokens, so this means there is no new token
            $tokenRepo->findAll()
        );

        $responseCrawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $responseCrawler->filter('div#password-reset')->count()
        );
    }

    public function testForgotPasswordInactiveUser()
    {
        // Check sending a correct name and email of an inactive user results in a
        // success message alongside no new token or email sent

        /** @var User $inactiveUser */
        $inactiveUser = $this->fixtures->getReference('inactive-admin-user');
        $this->assertFalse($inactiveUser->getIsActive());
        $client = static::makeClient();
        $crawler = $client->request('GET', '/admin/forgot_password');
        $this->assertEquals(self::$kernel->getContainer()->has('profiler'), true);

        $buttonNode = $crawler->selectButton('Recuperar Contraseña');
        $form = $buttonNode->form([
            'username' => $inactiveUser->getName(),
            'email' => $inactiveUser->getEmail(),
        ]);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('/admin/login')
        );

        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(0, $mailCollector->getMessageCount());

        $tokenRepo = self::$kernel->getContainer()
            ->get('doctrine')
            ->getRepository('GalvesbandTraUserBundle:ResetToken');
        $this->assertCount(
            3, // Fixtures contains two tokens, so this means there is no new token
            $tokenRepo->findAll()
        );

        $responseCrawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $responseCrawler->filter('div#password-reset')->count()
        );
    }

    public function testForgotPasswordSuccess()
    {
        // Check that with correct name and email from an active user results
        // in a success message, an email sent to the right direction and a new token
        // associated to the user used.

        /** @var User $adminUser */
        $adminUser = $this->fixtures->getReference('admin-user');
        $this->assertTrue($adminUser->getIsActive());

        $client = static::makeClient();
        $crawler = $client->request('GET', '/admin/forgot_password');
        $this->assertEquals(self::$kernel->getContainer()->has('profiler'), true);

        $buttonNode = $crawler->selectButton('Recuperar Contraseña');
        $form = $buttonNode->form([
            'username' => $adminUser->getName(),
            'email' => $adminUser->getEmail(),
        ]);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('/admin/login')
        );

        $tokenRepo = self::$kernel->getContainer()
            ->get('doctrine')
            ->getRepository('GalvesbandTraUserBundle:ResetToken');
        $this->assertCount(
            4, // Fixtures contains 3 tokens, so this means there is a new token
            $tokenRepo->findAll()
        );
        /** @noinspection SqlDialectInspection */
        $adminUser = self::$kernel->getContainer()->get('doctrine')->getManager()
            ->createQuery(
                'SELECT u, rt FROM GalvesbandTraUserBundle:User u
                 JOIN u.token rt 
                 WHERE u.id = :id'
            )
            ->setParameter('id', $adminUser->getId())
            ->getSingleResult();
        $this->assertNotNull($adminUser->getToken());
        $this->assertTrue(
            $adminUser->getToken()->getCreatedAt() <= new DateTime('now')
        );
        $this->assertTrue(
            $adminUser->getToken()->getCreatedAt() > new DateTime('-1 minute')
        );

        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());

        /** @var Swift_Message $message */
        $message = $mailCollector->getMessages()[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertCount(1, $message->getTo());
        $destinationArray = $message->getTo();
        $this->assertTrue(array_key_exists($adminUser->getEmail(), $destinationArray));
        $this->assertTrue(
            strpos($message->getBody(), $adminUser->getToken()->getToken()) !== false
        );

        $responseCrawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $responseCrawler->filter('div#password-reset')->count()
        );
    }

    public function testRedeemUnknownToken()
    {
        // Link with non-existing token -> 404
        $client = static::makeClient();
        $client->request('GET', '/admin/recover_password/Peter/a1b2c3d4e5');
        $this->assertStatusCode(404, $client);
    }

    public function testRedeemWrongUserToken()
    {
        // Link with existing token related to wrong user -> 404

        /** @var ResetToken $inactiveUserToken */
        $inactiveUserToken = $this->fixtures->getReference('inactive-user-token');
        /** @var User $adminUser */
        $adminUser = $this->fixtures->getReference('admin-user');

        $client = static::makeClient();
        $client->request(
            'GET',
            '/admin/recover_password/'.$adminUser->getName().'/'.$inactiveUserToken->getToken()
        );
        $this->assertStatusCode(404, $client);
    }

    public function testRedeemOutdatedToken()
    {
        // Link with existing but outdated token -> 404

        /** @var ResetToken $outdatedToken */
        $outdatedToken = $this->fixtures->getReference('outdated-token');
        /** @var User $superAdminUser */
        $superAdminUser = $this->fixtures->getReference('super-admin-user');

        $client = static::makeClient();
        $client->request(
            'GET',
            '/admin/recover_password/'.$superAdminUser->getName().'/'.$outdatedToken->getToken()
        );
        $this->assertStatusCode(404, $client);
    }

    public function testRedeemInactiveUserToken()
    {
        // Check an inactive user can't redeem a token -> 404
        /** @var ResetToken $inactiveUserToken */
        $inactiveUserToken = $this->fixtures->getReference('inactive-user-token');
        /** @var User $inactiveUser */
        $inactiveUser = $this->fixtures->getReference('inactive-admin-user');

        $client = static::makeClient();
        $client->request(
            'GET',
            '/admin/recover_password/'.$inactiveUser->getName().'/'.$inactiveUserToken->getToken()
        );
        $this->assertStatusCode(404, $client);
    }

    public function testRedeemTokenSuccess()
    {
        // Check existing token results in view with new password
        // Check token is deleted afterwards
        // Check new password works

        /** @var ResetToken $staffUserToken */
        $staffUserToken = $this->fixtures->getReference('staff-user-token');
        /** @var User $staffUser */
        $staffUser = $this->fixtures->getReference('staff-user');

        $client = static::makeClient();
        $client->request(
            'GET',
            '/admin/recover_password/'.$staffUser->getName().'/'.$staffUserToken->getToken()
        );
        $this->assertStatusCode(200, $client);
        // We need to extract the password from the html response
        $responseContent = $client->getResponse()->getContent();
        $tmp = preg_match('/&#039;([0-9a-zA-Z\/+])+&#039;/', $responseContent, $match);
        $this->assertTrue($tmp !== false && $tmp !== 0);
        $password = str_replace('&#039;', '', $match[0]);

        $tokenRepo = $this->getContainer()->get('doctrine')->getRepository('GalvesbandTraUserBundle:ResetToken');
        $tokenSearchResult = $tokenRepo->findOneBy(['id' => $staffUserToken->getId()]);
        $this->assertEmpty($tokenSearchResult);

        $this->getContainer()->get('doctrine')->getManager()->refresh($staffUser);

        $encoder_service = $this->getContainer()->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($staffUser);
        $this->assertTrue(
            $encoder->isPasswordValid($staffUser->getPassword(), $password, $staffUser->getSalt())
        );
    }
}