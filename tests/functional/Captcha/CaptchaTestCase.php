<?php

namespace App\Tests\Functional\Captcha;

use App\Ldap\MockClient;
use App\Tests\Functional\FunctionalTestCase;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class CaptchaTestCase
 */
abstract class CaptchaTestCase extends FunctionalTestCase
{
    protected function createOverridedTwig(ContainerInterface $container, array $overrides, $request)
    {
        /** @var \Twig_Environment $twig */
        $twig = $container->get('twig');

        $globals = $twig->getGlobals();
        /** @var \Symfony\Bridge\Twig\AppVariable $app */
        $app = $globals['app'];
        // this is only to have a valid app.request.baseUrl global in template
        $stack = new RequestStack();
        $stack->push($request);
        $app->setRequestStack($stack);

        foreach ($overrides as $key => $value) {
            $twig->addGlobal($key, $value);
        }

        return $twig;
    }

    protected function createMockRecaptchaService($willVerify)
    {
        $recaptchaService = $this
            ->getMockBuilder('App\\Service\\RecaptchaService')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $recaptchaService
            ->expects($this->any())
            ->method('verify')
            ->willReturn($willVerify ? '' : 'badcaptcha')
        ;
        return $recaptchaService;
    }

    protected function createMockUsernameValidityChecker()
    {
        $usernameValidityChecker = $this->getMock("App\\Service\\UsernameValidityChecker");
        $usernameValidityChecker
            ->expects($this->once())
            ->method('evaluate')
            ->with($this->equalTo('user1'))
            ->willReturn('')
        ;

        return $usernameValidityChecker;
    }

    protected function createMockPasswordStrengthChecker()
    {
        $passwordChecker = $this
            ->getMockBuilder("App\\PasswordStrengthChecker\\CheckerInterface")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $passwordChecker
            ->expects($this->once())
            ->method('evaluate')
            ->with($this->equalTo('newpassword'), $this->equalTo('password1'), $this->equalTo('user1'))
            ->willReturn([])
        ;

        return $passwordChecker;
    }

    protected function createMockContainer($parameters, $services)
    {
        $parameters_ = [];

        foreach ($parameters as $parameterName => $parameterValue) {
            $parameters_[] = [$parameterName, $parameterValue];
        }

        $services_get = [];
        $services_has = [];

        foreach ($services as $serviceName => $service) {
            $services_get[] = [$serviceName, 1, $service];
            $services_has[] = [$serviceName, true];
        }

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\Container');

        $container
            ->method('getParameter')
            ->will($this->returnValueMap($parameters_))
        ;

        $container
            ->expects($this->any())
            ->method('has')
            ->will($this->returnValueMap($services_has))
        ;

        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($services_get))
        ;

        return $container;
    }

    protected function createMockLdapClient()
    {
        $ldapClient = new MockClient();
        $ldapClient->setLogger(new NullLogger());

        return $ldapClient;
    }

    protected function createMockCsrfTokenManager()
    {
        $container = $this->getMock('Symfony\Component\Security\Csrf\CsrfTokenManager');
        $container
            ->expects($this->any())
            ->method('isTokenValid')
            ->willReturn(true)
        ;
        return $container;
    }
}