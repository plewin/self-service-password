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

use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Exception\LdapUpdateFailedException;
use App\Ldap\ClientInterface;
use App\Service\UsernameValidityChecker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This page is called to set answers for a user
 */
class ChangeSecurityQuestionsController extends Controller
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

        // render form empty
        return $this->render('self-service/change_security_question_form.html.twig', [
            'result' => 'emptysetquestionsform',
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
        return $request->request->has('password')
            && $request->request->has('question')
            && $request->request->has('answer')
            && $request->request->has('_csrf_token');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    private function processFormData(Request $request)
    {
        if (!$this->isCsrfTokenValid('change_security_question', $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $login = $request->get('login');
        $password = $request->request->get('password');
        $question = $request->request->get('question');
        $answer = $request->request->get('answer');

        $missings = [];
        if (empty($login)) {
            $missings[] = 'loginrequired';
        }
        if (empty($password)) {
            $missings[] = 'passwordrequired';
        }
        if (empty($question)) {
            // Question cannot be empty, it is a select. Request has been tampered.
            $missings[] = 'questionrequired';
        }
        if (empty($answer)) {
            $missings[] = 'answerrequired';
        }
        if ($this->isCaptchaEnabled() and !$this->isCaptchaSubmitted($request)) {
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
        if ($this->isCaptchaEnabled() and !$this->verifyCaptcha($request, $login)) {
            return $this->renderFormWithError('', ['badcaptcha'], $request);
        }

        /** @var ClientInterface $ldapClient */
        $ldapClient = $this->get('ldap_client');

        try {
            $ldapClient->connect();
            $context = $ldapClient->fetchUserEntryContext($login, ['dn']);
            $ldapClient->checkOldPassword($password, $context);
            // Register answer
            $ldapClient->changeQuestion($context['user_dn'], $question, $answer);
        } catch (LdapErrorException $e) {
            // action probably not needed, problem with configuration or ldap is down
            return $this->renderFormWithError('ldaperror', [], $request);
        } catch (LdapInvalidUserCredentialsException $e) {
            // action needed, password is wrong
            return $this->renderFormWithError('', ['badcredentials'], $request);
        } catch (LdapUpdateFailedException $e) {
            // action needed ?
            return $this->renderFormWithError('', ['answermoderror'], $request);
        }

        // render page success
        return $this->render('self-service/change_security_question_success.html.twig');
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
        return $this->render('self-service/change_security_question_form.html.twig', [
            'result' => $result,
            'problems' => $problems,
            'login' => $request->get('login'),
            'questions' => $this->getParameter('questions'),
        ] + $this->getCaptchaTemplateExtraVars($request));
    }
}
