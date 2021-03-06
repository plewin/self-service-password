<?php

namespace App\Tests\Functional\Captcha;

use App\Controller\ChangePasswordController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class RecaptchaTest
 */
class RecaptchaTest extends CaptchaTestCase
{
    public function testChangePasswordRecaptchaVisibleController(): void
    {
        $client = self::createClient();
        $changePasswordController = new ChangePasswordController();
        $changePasswordController->setContainer($client->getContainer());

        $request = new Request();

        $overrides = [
            'enable_password_change' => true,
            'enable_captcha' => true,
            'captcha_type' => 'recaptcha',
            'recaptcha_publickey' => 'recaptcha_publickey',
            'recaptcha_theme' => 'light',
            'recaptcha_type' => 'image',
            'recaptcha_size' => 'normal',
        ];

        $services = [
            'twig' => $this->createOverridedTwig($client->getContainer(), $overrides, $request),
        ];

        $container = $this->createMockContainer($overrides, $services);

        $changePasswordController->setContainer($container);

        /** @var Response $response */
        $response = $changePasswordController->indexAction($request);
        $content = $response->getContent();
        $this->assertContains('g-recaptcha', $content);
        $this->assertContains('data-sitekey="recaptcha_publickey"', $content);
        $this->assertContains('data-theme="light"', $content);
        $this->assertContains('data-type="image"', $content);
        $this->assertContains('data-size="normal"', $content);
        $this->assertContains('https://www.google.com/recaptcha/api.js', $content);
    }

    public function testChangePasswordRecaptchaNotSubmittedFormController(): void
    {
        $client = self::createClient();
        $changePasswordController = new ChangePasswordController();
        $changePasswordController->setContainer($client->getContainer());

        $request = new Request(
            [],
            [
                'login' => 'user1',
                'oldpassword' => 'password1',
                'newpassword' => 'newpassword',
                'confirmpassword' => 'confirmpassword',
                '_csrf_token' => 'FAKE CSRF TOKEN',
            ]
        );

        $parameters = [
            'enable_password_change' => true,
            'enable_captcha' => true,
            'captcha_type' => 'recaptcha',
            'recaptcha_publickey' => 'recaptcha_publickey',
            'recaptcha_theme' => 'light',
            'recaptcha_type' => 'image',
            'recaptcha_size' => 'normal',
        ];

        $services = [
            'twig' => $this->createOverridedTwig($client->getContainer(), $parameters, $request),
            'security.csrf.token_manager' => $this->createMockCsrfTokenManager(),
        ];

        $container = $this->createMockContainer($parameters, $services);

        $changePasswordController->setContainer($container);

        /** @var Response $response */
        $response = $changePasswordController->indexAction($request);
        $content = $response->getContent();
        //TODO missing translation
        $this->assertContains('captcharequired', $content);
    }

    public function testChangePasswordRecaptchaSubmittedFormInvalidController(): void
    {
        $client = self::createClient();
        $changePasswordController = new ChangePasswordController();
        $changePasswordController->setContainer($client->getContainer());

        $request = new Request(
            [],
            [
                'login' => 'user1',
                'oldpassword' => 'password1',
                'newpassword' => 'newpassword',
                'confirmpassword' => 'newpassword',
                'g-recaptcha-response' => 'plop42',
                '_csrf_token' => 'FAKE CSRF TOKEN',
            ]
        );

        $parameters = [
            'enable_password_change' => true,
            'enable_captcha' => true,
            'captcha_type' => 'recaptcha',
            'recaptcha_publickey' => 'recaptcha_publickey',
            'recaptcha_theme' => 'light',
            'recaptcha_type' => 'image',
            'recaptcha_size' => 'normal',
        ];

        $services = [
            'twig' => $this->createOverridedTwig($client->getContainer(), $parameters, $request),
            'username_validity_checker' => $this->createMockUsernameValidityChecker(),
            'password_strength_checker' => $this->createMockPasswordStrengthChecker(),
            'recaptcha_service' => $this->createMockRecaptchaService(false),
            'security.csrf.token_manager' => $this->createMockCsrfTokenManager(),
        ];

        $container = $this->createMockContainer($parameters, $services);

        $changePasswordController->setContainer($container);

        /** @var Response $response */
        $response = $changePasswordController->indexAction($request);
        $content = $response->getContent();
        $this->assertContains('CAPTCHA was not entered correctly', $content);
    }

    public function testChangePasswordRecaptchaSubmittedFormValidController(): void
    {
        $client = self::createClient();
        $changePasswordController = new ChangePasswordController();
        $changePasswordController->setContainer($client->getContainer());

        $request = new Request(
            [],
            [
                'login' => 'user1',
                'oldpassword' => 'password1',
                'newpassword' => 'newpassword',
                'confirmpassword' => 'newpassword',
                'g-recaptcha-response' => 'plop42',
                '_csrf_token' => 'FAKE CSRF TOKEN',
            ]
        );

        $parameters = [
            'enable_password_change' => true,
            'enable_captcha' => true,
            'captcha_type' => 'recaptcha',
            'recaptcha_publickey' => 'recaptcha_publickey',
            'recaptcha_theme' => 'light',
            'recaptcha_type' => 'image',
            'recaptcha_size' => 'normal',
        ];

        $services = [
            'twig' => $this->createOverridedTwig($client->getContainer(), $parameters, $request),
            'username_validity_checker' => $this->createMockUsernameValidityChecker(),
            'password_strength_checker' => $this->createMockPasswordStrengthChecker(),
            'recaptcha_service' => $this->createMockRecaptchaService(true),
            'ldap_client' => $this->createMockLdapClient(),
            'event_dispatcher' => $this->getMockBuilder(EventDispatcher::class)->getMock(),
            'security.csrf.token_manager' => $this->createMockCsrfTokenManager(),
        ];

        $container = $this->createMockContainer($parameters, $services);

        $changePasswordController->setContainer($container);

        /** @var Response $response */
        $response = $changePasswordController->indexAction($request);
        $content = $response->getContent();
        $this->assertContains('Your password was changed', $content);
    }
}

