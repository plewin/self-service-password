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
    public function testChangePasswordController()
    {
        $changePasswordController = new ChangePasswordController();

        $container = $this->getMock(ContainerInterface::class);

        $container
            ->method('getParameter')
            ->with('enable_password_change')
            ->willReturn(false);

        $this->setExpectedException(AccessDeniedException::class);

        $changePasswordController->setContainer($container);
        $changePasswordController->indexAction(new Request());
    }

    public function testChangeSecurityQuestionController()
    {
        $changeSecurityQuestionsController = new ChangeSecurityQuestionsController();

        $container = $this->getMock(ContainerInterface::class);

        $container
            ->method('getParameter')
            ->with('enable_questions')
            ->willReturn(false);

        $this->setExpectedException(AccessDeniedException::class);

        $changeSecurityQuestionsController->setContainer($container);
        $changeSecurityQuestionsController->indexAction(new Request());
    }

    public function testChangeSshKeyController()
    {
        $changeSshKeyController = new ChangeSshKeyController();

        $container = $this->getMock(ContainerInterface::class);

        $container
            ->method('getParameter')
            ->with('enable_sshkey_change')
            ->willReturn(false);

        $this->setExpectedException(AccessDeniedException::class);

        $changeSshKeyController->setContainer($container);
        $changeSshKeyController->indexAction(new Request());
    }

    public function testGetTokenByEmailVerificationController()
    {
        $getTokenByEmailVerificationController = new GetTokenByEmailVerificationController();
        $container = $this->getMock(ContainerInterface::class);

        $container
            ->method('getParameter')
            ->with('enable_reset_by_email')
            ->willReturn(false);

        $this->setExpectedException(AccessDeniedException::class);

        $getTokenByEmailVerificationController->setContainer($container);
        $getTokenByEmailVerificationController->indexAction(new Request());
    }

    public function testGetTokenBySmsVerificationController()
    {
        $getTokenBySmsVerificationController = new GetTokenBySmsVerificationController();

        $container = $this->getMock(ContainerInterface::class);

        $container
            ->method('getParameter')
            ->with('enable_reset_by_sms')
            ->willReturn(false);

        $this->setExpectedException(AccessDeniedException::class);

        $getTokenBySmsVerificationController->setContainer($container);
        $getTokenBySmsVerificationController->indexAction(new Request());
    }

    public function testResetPasswordByQuestionController()
    {
        $resetPasswordByQuestionController = new ResetPasswordByQuestionController();

        $container = $this->getMock(ContainerInterface::class);

        $container
            ->method('getParameter')
            ->with('enable_questions')
            ->willReturn(false);

        $this->setExpectedException(AccessDeniedException::class);

        $resetPasswordByQuestionController->setContainer($container);
        $resetPasswordByQuestionController->indexAction(new Request());
    }

    public function testResetPasswordByTokenController()
    {
        $resetPasswordByTokenController = new ResetPasswordByTokenController();

        $container = $this->getMock(ContainerInterface::class);

        $values = [
            ['enable_reset_by_email', false],
            ['enable_reset_by_sms', false],
        ];

        $container
            ->method('getParameter')
            ->will($this->returnValueMap($values))
        ;

        $this->setExpectedException(AccessDeniedException::class);

        $resetPasswordByTokenController->setContainer($container);
        $resetPasswordByTokenController->indexAction(new Request());
    }
}

