<?php

namespace App\Tests\Functional\Captcha;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Class GregwarCaptchaTest
 */
class GregwarCaptchaTest extends CaptchaTestCase
{
    public function testChangePasswordGregwarCaptchaVisibleController()
    {
        $client = $this->createClient();
        $changePasswordController = $client->getContainer()->get('change_password.controller');

        $request = new Request();

        $mockSessionStorage = new MockArraySessionStorage();

        $request->setSession(new Session($mockSessionStorage));

        $overrides = [
            'enable_password_change' => true,
            'enable_captcha' => true,
            'captcha_type' => 'gregwar',
        ];

        $services = [
            'twig' => $this->createOverridedTwig($client->getContainer(), $overrides, $request),
        ];

        $container = $this->createMockContainer($overrides, $services);

        $changePasswordController->setContainer($container);

        /** @var Response $response */
        $response = $changePasswordController->indexAction($request);
        $content = $response->getContent();

        // assert image with inlined jpeg
        $this->assertRegExp('<img src="data:image/jpeg;base64,.*" />', $content);
        $this->assertContains('name="captcha"', $content);
        $this->assertContains('id="captcha"', $content);
        $this->assertContains('placeholder="Captcha"', $content);
    }

    public function testChangePasswordRecaptchaNotSubmittedFormController()
    {
        $client = $this->createClient();
        $changePasswordController = $client->getContainer()->get('change_password.controller');

        $request = new Request(
            [],
            [
                'login' => 'user1',
                'oldpassword' => 'password1',
                'newpassword' => 'newpassword',
                'confirmpassword' => 'confirmpassword',
            ]
        );
        $mockSessionStorage = new MockArraySessionStorage();

        $request->setSession(new Session($mockSessionStorage));

        $parameters = [
            'enable_password_change' => true,
            'enable_captcha' => true,
            'captcha_type' => 'gregwar',
        ];

        $services = [
            'twig' => $this->createOverridedTwig($client->getContainer(), $parameters, $request),
        ];

        $container = $this->createMockContainer($parameters, $services);

        $changePasswordController->setContainer($container);

        /** @var Response $response */
        $response = $changePasswordController->indexAction($request);
        $content = $response->getContent();
        //TODO missing translation
        $this->assertContains('captcharequired', $content);
    }

    public function testChangePasswordGregwarCaptchaSubmittedFormInvalidController()
    {
        $client = $this->createClient();
        $changePasswordController = $client->getContainer()->get('change_password.controller');

        $request1 = new Request();
        $mockSessionStorage = new MockArraySessionStorage();
        $mockSession = new Session($mockSessionStorage);
        $request1->setSession($mockSession);

        $parameters = [
            'enable_password_change' => true,
            'enable_captcha' => true,
            'captcha_type' => 'gregwar',
        ];

        $services = [
            'twig' => $this->createOverridedTwig($client->getContainer(), $parameters, $request1),
            'username_validity_checker' => $this->createMockUsernameValidityChecker(),
            'password_strength_checker' => $this->createMockPasswordStrengthChecker(),
            'recaptcha_service' => $this->createMockRecaptchaService(false),
        ];

        $container = $this->createMockContainer($parameters, $services);

        $changePasswordController->setContainer($container);

        // discard the response
        $changePasswordController->indexAction($request1);

        $request2 = new Request(
            [],
            [
                'login' => 'user1',
                'oldpassword' => 'password1',
                'newpassword' => 'newpassword',
                'confirmpassword' => 'newpassword',
                'captcha' => 'plop42',
            ]
        );
        // reuse mock session storage
        $request2->setSession($mockSession);

        /** @var Response $response */
        $response = $changePasswordController->indexAction($request2);
        $content = $response->getContent();
        $this->assertContains('CAPTCHA was not entered correctly', $content);
    }

    public function testChangePasswordGregwarCaptchaSubmittedFormValidController()
    {
        $client = $this->createClient();
        $changePasswordController = $client->getContainer()->get('change_password.controller');

        $request1 = new Request();
        $mockSessionStorage = new MockArraySessionStorage();
        $mockSession = new Session($mockSessionStorage);
        $request1->setSession($mockSession);

        $parameters = [
            'enable_password_change' => true,
            'enable_captcha' => true,
            'captcha_type' => 'gregwar',
        ];

        $services = [
            'twig' => $this->createOverridedTwig($client->getContainer(), $parameters, $request1),
            'username_validity_checker' => $this->createMockUsernameValidityChecker(),
            'password_strength_checker' => $this->createMockPasswordStrengthChecker(),
            'recaptcha_service' => $this->createMockRecaptchaService(false),
            'ldap_client' => $this->createMockLdapClient(),
            'event_dispatcher' => $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcher'),
        ];

        $container = $this->createMockContainer($parameters, $services);

        $changePasswordController->setContainer($container);

        // discard the response
        $changePasswordController->indexAction($request1);

        $request2 = new Request(
            [],
            [
                'login' => 'user1',
                'oldpassword' => 'password1',
                'newpassword' => 'newpassword',
                'confirmpassword' => 'newpassword',
                // cheat, get the captcha phrase from the session
                'captcha' => $mockSession->get('captcha'),
            ]
        );
        // reuse mock session storage
        $request2->setSession($mockSession);

        /** @var Response $response */
        $response = $changePasswordController->indexAction($request2);
        $content = $response->getContent();
        $this->assertContains('Your password was changed', $content);
    }
}

