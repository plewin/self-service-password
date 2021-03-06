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
use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Exception\LdapUpdateFailedException;
use App\Ldap\ClientInterface;
use App\PasswordStrengthChecker\CheckerInterface;
use App\Service\UsernameValidityChecker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ChangePasswordController
 *
 * This page is called to change password
 */
class ChangePasswordController extends Controller
{
    use CaptchaTrait;
    use AsYouTypeTrait;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        if (!$this->getParameter('enable_password_change')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isFormSubmitted($request)) {
            return $this->processFormData($request);
        }

        // Render empty form
        return $this->render('self-service/change_password_form.html.twig', [
            'result' => 'emptychangeform',
            'problems' => [],
            'login' => $request->get('login'),
        ] + $this->getCaptchaTemplateExtraVars($request) + $this->getPolicyTemplateExtraVars());
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isFormSubmitted(Request $request): bool
    {
        return ($request->request->has('login') || $request->query->has('login'))
            && $request->request->has('newpassword')
            && $request->request->has('oldpassword')
            && $request->request->has('confirmpassword')
            && $request->request->has('_csrf_token');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    private function processFormData(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('change_password', $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $login = $request->get('login', '');
        $oldPassword = $request->request->get('oldpassword', '');
        $newPassword = $request->request->get('newpassword', '');
        $confirmPassword = $request->request->get('confirmpassword', '');

        $missings = [];
        if (!$login) {
            $missings[] = 'loginrequired';
        }
        if (!$oldPassword) {
            $missings[] = 'oldpasswordrequired';
        }
        if (!$newPassword) {
            $missings[] = 'newpasswordrequired';
        }
        if (!$confirmPassword) {
            $missings[] = 'confirmpasswordrequired';
        }

        if ($this->isCaptchaEnabled() && !$this->isCaptchaSubmitted($request)) {
            $missings[] = 'captcharequired';
        }

        if (count($missings) > 0) {
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

        // Match new and confirm password
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'nomatch';
        }

        /** @var CheckerInterface $passwordChecker */
        $passwordChecker = $this->get('password_strength_checker');

        // Check password strength
        $errors = array_merge($errors, $passwordChecker->evaluate($newPassword, $oldPassword, $login));

        if (count($errors) > 0) {
            return $this->renderFormWithError('', $errors, $request);
        }

        // Check CAPTCHA
        if ($this->isCaptchaEnabled() && !$this->verifyCaptcha($request, $login)) {
            return $this->renderFormWithError('', ['badcaptcha'], $request);
        }

        /** @var ClientInterface $ldapClient */
        $ldapClient = $this->get('ldap_client');

        try {
            $ldapClient->connect();
            // we want user's email address if we have to notify
            $wanted = $this->getParameter('notify_user_on_password_change') ? ['dn', 'samba', 'shadow', 'mail'] : ['dn', 'samba', 'shadow'];
            $context = $ldapClient->fetchUserEntryContext($login, $wanted);
            $ldapClient->checkOldPassword($oldPassword, $context);
            $ldapClient->changePassword($context['user_dn'], $newPassword, $oldPassword, $context);
        } catch (LdapErrorException $e) {
            // action probably not needed, problem with configuration or ldap is down
            return $this->renderFormWithError('ldaperror', [], $request);
        } catch (LdapInvalidUserCredentialsException $e) {
            // action needed, password is wrong
            return $this->renderFormWithError('', ['badcredentials'], $request);
        } catch (LdapUpdateFailedException $e) {
            // action needed ? like password does not respect remote password policy
            return $this->renderFormWithError('', ['passworderror'], $request);
        }

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->get('event_dispatcher');

        $event = new PasswordChangedEvent($login, $oldPassword, $newPassword, $context);

        $eventDispatcher->dispatch($event);

        // render page success
        return $this->render('self-service/change_password_success.html.twig');
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
        return $this->render('self-service/change_password_form.html.twig', [
            'result' => $result,
            'problems' => $problems,
            'login' => $request->get('login'),
        ] + $this->getCaptchaTemplateExtraVars($request) + $this->getPolicyTemplateExtraVars());
    }
}
