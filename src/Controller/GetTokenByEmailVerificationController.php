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

use App\Exception\LdapEntryFoundInvalidException;
use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Ldap\ClientInterface;
use App\Service\MailNotificationService;
use App\Service\TokenManagerService;
use App\Service\UsernameValidityChecker;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This page is called to send a reset token by mail
 */
class GetTokenByEmailVerificationController extends Controller
{
    use CaptchaTrait;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        if (!$this->getParameter('enable_reset_by_email')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isFormSubmitted($request)) {
            return $this->processFormData($request, $this->get('logger'));
        }

        // render form empty
        return $this->render('self-service/email_verification_form.html.twig', [
            'result' => 'emptysendtokenform',
            'problems' => [],
            'login' => $request->get('login'),
        ] + $this->getCaptchaTemplateExtraVars($request));
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isFormSubmitted(Request $request): bool
    {
        return ($request->request->has('login') || $request->query->has('login'))
            && ($this->getParameter('mail_address_use_ldap') || $request->request->has('mail'))
            && $request->request->has('_csrf_token');
    }

    /**
     * @param Request $request
     * @param LoggerInterface $logger
     *
     * @return Response
     */
    private function processFormData(Request $request, LoggerInterface $logger): Response
    {
        if (!$this->isCsrfTokenValid('get_token_by_email', $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $login = $request->get('login');
        $mail = $request->request->get('mail', '');

        $missings = [];

        if (empty($login)) {
            $missings[] = 'loginrequired';
        }
        if (empty($mail) && !$this->getParameter('mail_address_use_ldap')) {
            $missings[] = 'mailrequired';
        }

        if ($this->isCaptchaEnabled() && !$this->isCaptchaSubmitted($request)) {
            $missings[] = 'captcharequired';
        }

        if (count($missings)) {
            return $this->renderFormWithError('', $missings, $request);
        }

        $errors = [];

        /** @var UsernameValidityChecker $usernameChecker */
        $usernameChecker = $this->get('username_validity_checker');

        // Check the entered username for characters that our installation doesn't support
        $result = $usernameChecker->evaluate($login);
        if ('' !== $result) {
            $errors[] = $result;
        }

        if (count($errors)) {
            return $this->renderFormWithError('', $errors, $request);
        }

        // Check CAPTCHA
        if ($this->isCaptchaEnabled() && !$this->verifyCaptcha($request, $login)) {
            return $this->renderFormWithError('', ['badcaptcha'], $request);
        }

        /** @var ClientInterface $ldapClient */
        $ldapClient = $this->get('ldap_client');

        $context = [];

        try {
            $ldapClient->connect();

            if ($this->getParameter('mail_address_use_ldap')) {
                $wanted = ['mail'];
                $context = $ldapClient->fetchUserEntryContext($login, $wanted);

                if (null === $context['user_mail']) {
                    $logger->warning("Mail not found for user $login");
                    throw new LdapEntryFoundInvalidException();
                }

                $mail = $context['user_mail'];
            } else {
                // throw exception if mail does not match
                $ldapClient->checkMail($login, $mail);
            }
        } catch (LdapErrorException $e) {
            return $this->renderFormWithError('ldaperror', [], $request);
        } catch (LdapInvalidUserCredentialsException $e) {
            return $this->renderFormWithError('', ['badcredentials'], $request);
        } catch (LdapEntryFoundInvalidException $e) {
            return $this->renderFormWithError('', ['mailnomatch'], $request);
        }


        /** @var TokenManagerService $tokenManager */
        $tokenManager = $this->get('token_manager_service');

        $token = $tokenManager->createToken($login);

        $resetUrl = $this->generateUrl('reset-password-with-token', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $logger->notice("Send reset URL $resetUrl");

        /** @var MailNotificationService $mailService */
        $mailService = $this->get('mail_notification_service');
        $data = [
            'login'   => $login,
            'mail'    => $mail,
            'url'     => $resetUrl,
            'context' => $context,
        ];
        $success = $mailService->send('mail/user-url-token-requested', $data);

        if (!$success) {
            return $this->renderFormWithError('tokennotsent', [], $request);
        }

        // render page success
        return $this->render('self-service/email_verification_success.html.twig');
    }

    /**
     * @param string  $result
     * @param array   $problems
     * @param Request $request
     *
     * @return Response
     */
    private function renderFormWithError(string $result, array $problems, Request $request): Response
    {
        return $this->render('self-service/email_verification_form.html.twig', [
            'result' => $result,
            'problems' => $problems,
            'login' => $request->get('login'),
        ] + $this->getCaptchaTemplateExtraVars($request));
    }
}
