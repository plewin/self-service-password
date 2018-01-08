<?php
/*
 * LTB Self Service Password
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

use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Exception\LdapUpdateFailedException;
use App\Service\LdapClient;
use App\Service\PasswordStrengthChecker;
use App\Service\UsernameValidityChecker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This page is called to reset a password trusting question/anwser
 */
class ResetPasswordByQuestionController extends Controller
{
    use CaptchaTrait;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        if (!$this->getParameter('enable_questions')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isFormSubmitted($request)) {
            return $this->processFormData($request);
        }

        // Render empty form
        return $this->render('self-service/reset_password_by_question_form.html.twig', [
            'result' => 'emptyresetbyquestionsform',
            'problems' => [],
            'login' => $request->get('login'),
            'questions' => $this->getParameter('questions'),
        ] + $this->getCaptchaTemplateExtraVars($request));
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isFormSubmitted(Request $request)
    {
        return ($request->request->has('login') || $request->query->has('login'))
            && $request->request->has('question')
            && $request->request->has('answer')
            && $request->request->has('newpassword')
            && $request->request->has('confirmpassword');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    private function processFormData(Request $request)
    {
        $login = $request->get('login', '');
        $question = $request->request->get('question', '');
        $answer = $request->request->get('answer', '');
        $newpassword = $request->request->get('newpassword', '');
        $confirmpassword = $request->request->get('confirmpassword', '');

        $missings = [];
        if (!$login) {
            $missings[] = 'loginrequired';
        }
        if (!$question) {
            // should never happen, $question is a select input, request is tampered
            $missings[] = 'questionrequired';
        }
        if (!$answer) {
            $missings[] = 'answerrequired';
        }
        if (!$newpassword) {
            $missings[] = 'newpasswordrequired';
        }
        if (!$confirmpassword) {
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
        if ($newpassword !== $confirmpassword) {
            $errors[] = 'nomatch';
        }

        // Check password strength
        /** @var PasswordStrengthChecker $passwordChecker */
        $passwordChecker = $this->get('password_strength_checker');

        $errors += $passwordChecker->evaluate($newpassword, '', $login);

        if (count($errors) > 0) {
            return $this->renderFormWithError('', $errors, $request);
        }

        // Check CAPTCHA
        if ($this->isCaptchaEnabled() and !$this->verifyCaptcha($request, $login)) {
            return $this->renderFormWithError('', ['badcaptcha'], $request);
        }

        /** @var LdapClient $ldapClient */
        $ldapClient = $this->get('ldap_client');

        try {
            $ldapClient->connect();

            $wanted = ['dn', 'samba', 'shadow', 'questions'];
            if ($this->getParameter('notify_user_on_password_change')) {
                $wanted[] = 'mail';
            }
            $context = $ldapClient->fetchUserEntryContext($login, $wanted);

            // Check question/answer
            $match = $ldapClient->checkQuestionAnswer($login, $question, $answer, $context);
            if (!$match) {
                // incorrect answer
                return $this->renderFormWithError('', ['answernomatch'], $request);
            }

            $ldapClient->changePassword($context['user_dn'], $newpassword, '', $context);
        } catch (LdapErrorException $e) {
            // action probably not needed, problem with configuration or ldap is down
            return $this->renderFormWithError('ldaperror', [], $request);
        } catch (LdapUpdateFailedException $e) {
            // password was rejected by server
            return $this->renderFormWithError('', ['passworderror'], $request);
        } catch (LdapInvalidUserCredentialsException $e) {
            // wrong login
            return $this->renderFormWithError('', ['badcredentials'], $request);
        }

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get('event_dispatcher');

        $event = new GenericEvent();
        $event['login'] = $login;
        $event['new_password'] = $newpassword;
        $event['old_password'] = null;
        $event['context'] = $context;

        $eventDispatcher->dispatch('password.changed', $event);

        // Render success page
        return $this->render('self-service/change_password_success.html.twig');
    }

    /**
     * @param string  $result
     * @param array   $problems
     * @param Request $request
     *
     * @return Response
     */
    private function renderFormWithError($result, $problems, Request $request)
    {
        return $this->render('self-service/reset_password_by_question_form.html.twig', [
            'result' => $result,
            'problems' => $problems,
            'login' => $request->get('login'),
            'questions' => $this->getParameter('questions'),
        ] + $this->getCaptchaTemplateExtraVars($request));
    }
}
