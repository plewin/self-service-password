<?php
/*
 * LTB Self-Service Password
 *
 * Copyright (C) 2009 Clement OUDOT
 * Copyright (C) 2009 LTB-project.org
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * GPL License: http://www.gnu.org/licenses/gpl.txt
 */

namespace App\Controller;

use App\Events\PasswordChangedEvent;
use App\Exception\CryptographyBrokenException;
use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Exception\LdapUpdateFailedException;
use App\Exception\TokenException;
use App\Ldap\ClientInterface;
use App\PasswordStrengthChecker\CheckerInterface;
use App\Service\TokenManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This page is called to reset a password when a valid token is found in URL
 */
class ResetPasswordByTokenController extends Controller
{
    use CaptchaTrait;
    use AsYouTypeTrait;

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws CryptographyBrokenException
     */
    public function indexAction(Request $request): Response
    {
        if (!($this->getParameter('enable_reset_by_email') || $this->getParameter('enable_reset_by_sms'))) {
            throw $this->createAccessDeniedException();
        }

        $problems = [];
        $login = '';

        /** @var TokenManagerService $tokenManagerService */
        $tokenManagerService = $this->get('token_manager_service');

        // First, de we have a valid token ?
        $token = $request->get('token');
        if (!$token) {
            $problems[] = 'tokenrequired';
        } else {
            // Get token
            try {
                $login = $tokenManagerService->openToken($token);
            } catch (TokenException $e) {
                $problems[] = 'tokennotvalid';
            }
        }
        if (count($problems)) {
            return $this->render('self-service/reset_password_by_token_failure.html.twig', ['result' => $problems[0]]);
        }

        // Next is the form submitted ?
        if (!$request->request->has('_csrf_token')) {
            return $this->renderEmptyPage($request, $login);
        }

        if (!$this->isCsrfTokenValid('reset_by_token', $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }


        $newPassword = $request->request->get('newpassword');
        $confirmPassword = $request->request->get('confirmpassword');
        if (!$newPassword) {
            $problems[] = 'newpasswordrequired';
        }
        if (!$confirmPassword) {
            $problems[] = 'confirmpasswordrequired';
        }

        // Match new and confirm password
        if ($newPassword !== $confirmPassword) {
            $problems[] = 'nomatch';
        }

        if ($this->isCaptchaEnabled() && !$this->isCaptchaSubmitted($request)) {
            $problems[] = 'captcharequired';
        }

        /** @var CheckerInterface $passwordChecker */
        $passwordChecker = $this->get('password_strength_checker');

        // Check password strength
        $problems = array_merge($problems, $passwordChecker->evaluate($newPassword, '', $login));

        if (count($problems)) {
            return $this->renderErrorPage('', $problems, $request, $login);
        }

        // Okay the form is submitted but is the CAPTCHA valid ?
        if ($this->isCaptchaEnabled() && !$this->verifyCaptcha($request, $login)) {
            return $this->renderErrorPage('', ['badcaptcha'], $request, $login);
        }

        // All good, we try

        $notify = $this->getParameter('notify_user_on_password_change');

        /** @var ClientInterface $ldapClient */
        $ldapClient = $this->get('ldap_client');

        try {
            $ldapClient->connect();
            $wantedContext = ['dn', 'samba', 'shadow'];
            // Get user email for notification
            if ($notify) {
                $wantedContext[] = 'mail';
            }
            $context = $ldapClient->fetchUserEntryContext($login, $wantedContext);
            // Change password
            $ldapClient->changePassword($context['user_dn'], $newPassword, '', $context);
        } catch (LdapErrorException $e) {
            // action probably not needed, problem with configuration or ldap is down
            return $this->renderErrorPage('ldaperror', [], $request, $login);
        } catch (LdapInvalidUserCredentialsException $e) {
            // wrong login... should not be possible
            // unless the token got corrupted on this server or the user was deleted/moved on the ldap
            // between the token creation and usage
            return $this->renderErrorPage('badcredentials', [], $request, $login);
        } catch (LdapUpdateFailedException $e) {
            // password was refused by server
            return $this->renderErrorPage('', ['passworderror'], $request, $login);
        }

        // Delete token if all is ok
        $tokenManagerService->destroyToken();

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->get('event_dispatcher');

        $event = new PasswordChangedEvent($login, null, $newPassword, $context);

        $eventDispatcher->dispatch($event);

        // render success page
        return $this->render('self-service/change_password_success.html.twig');
    }

    /**
     * @param Request $request
     * @param string  $login
     *
     * @return Response
     */
    private function renderEmptyPage(Request $request, string $login): Response
    {
        return $this->render('self-service/reset_password_by_token_form.html.twig', [
            //TODO refactor translation
            'result'   => 'emptyresetbyquestionsform',
            'problems' => [],
            'source'   => $request->get('source'),
            'token'    => $request->get('token'),
            'login'    => $login,
        ] + $this->getCaptchaTemplateExtraVars($request) + $this->getPolicyTemplateExtraVars());
    }

    /**
     * @param string  $result
     * @param array   $problems
     * @param Request $request
     * @param string  $login
     *
     * @return Response
     */
    private function renderErrorPage(string $result, array $problems, Request $request, $login): Response
    {
        return $this->render('self-service/reset_password_by_token_form.html.twig', [
            'result'   => $result,
            'problems' => $problems,
            'source'   => $request->get('source'),
            'token'    => $request->get('token'),
            'login'    => $login,
        ] + $this->getCaptchaTemplateExtraVars($request) + $this->getPolicyTemplateExtraVars());
    }
}
