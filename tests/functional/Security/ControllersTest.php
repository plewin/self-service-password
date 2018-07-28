<?php

namespace App\Tests\Functional\Security;

use App\Controller\ChangePasswordController;
use App\Controller\ChangeSecurityQuestionsController;
use App\Controller\ChangeSshKeyController;
use App\Controller\GetTokenByEmailVerificationController;
use App\Controller\GetTokenBySmsVerificationController;
use App\Controller\ResetPasswordByQuestionController;
use App\Controller\ResetPasswordByTokenController;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ControllersTest
 */
class ControllersTest extends FunctionalTestCase
{
    public function testChangePasswordController(): void
    {
        $changePasswordController = new ChangePasswordController();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container
            ->method('getParameter')
            ->with('enable_password_change')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $changePasswordController->setContainer($container);
        $changePasswordController->indexAction(new Request());
    }

    public function testChangeSecurityQuestionController(): void
    {
        $changeSecurityQuestionsController = new ChangeSecurityQuestionsController();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container
            ->method('getParameter')
            ->with('enable_questions')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $changeSecurityQuestionsController->setContainer($container);
        $changeSecurityQuestionsController->indexAction(new Request());
    }

    public function testChangeSshKeyController(): void
    {
        $changeSshKeyController = new ChangeSshKeyController();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container
            ->method('getParameter')
            ->with('enable_sshkey_change')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $changeSshKeyController->setContainer($container);
        $changeSshKeyController->indexAction(new Request());
    }

    public function testGetTokenByEmailVerificationController(): void
    {
        $getTokenByEmailVerificationController = new GetTokenByEmailVerificationController();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container
            ->method('getParameter')
            ->with('enable_reset_by_email')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $getTokenByEmailVerificationController->setContainer($container);
        $getTokenByEmailVerificationController->indexAction(new Request());
    }

    public function testGetTokenBySmsVerificationController(): void
    {
        $getTokenBySmsVerificationController = new GetTokenBySmsVerificationController();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container
            ->method('getParameter')
            ->with('enable_reset_by_sms')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $getTokenBySmsVerificationController->setContainer($container);
        $getTokenBySmsVerificationController->indexAction(new Request());
    }

    public function testResetPasswordByQuestionController(): void
    {
        $resetPasswordByQuestionController = new ResetPasswordByQuestionController();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container
            ->method('getParameter')
            ->with('enable_questions')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $resetPasswordByQuestionController->setContainer($container);
        $resetPasswordByQuestionController->indexAction(new Request());
    }

    public function testResetPasswordByTokenController(): void
    {
        $resetPasswordByTokenController = new ResetPasswordByTokenController();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $values = [
            ['enable_reset_by_email', false],
            ['enable_reset_by_sms', false],
        ];

        $container
            ->method('getParameter')
            ->will($this->returnValueMap($values))
        ;

        $this->expectException(AccessDeniedException::class);

        $resetPasswordByTokenController->setContainer($container);
        $resetPasswordByTokenController->indexAction(new Request());
    }
}

