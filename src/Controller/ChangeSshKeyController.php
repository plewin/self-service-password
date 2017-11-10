<?php
#==============================================================================
# LTB Self Service Password
#
# Copyright (C) 2009 Clement OUDOT
# Copyright (C) 2009 LTB-project.org
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# GPL License: http://www.gnu.org/licenses/gpl.txt
#
#==============================================================================

# This page is called to change sshPublicKey

namespace App\Controller;

use App\Framework\Controller;
use App\Framework\Request;

use App\Service\LdapClient;
use App\Service\MailNotificationService;
use App\Service\RecaptchaService;
use App\Service\UsernameValidityChecker;

class ChangeSshKeyController extends Controller {
    /**
     * @param $request Request
     * @return string
     */
    public function indexAction(Request $request) {
        if($this->isFormSubmitted($request)) {
            return $this->processFormData($request);
        }

        return $this->renderEmptyForm($request);
    }

    private function isFormSubmitted(Request $request) {
        return $request->get("login")
            && $request->request->has("password")
            && $request->request->has("sshkey");
    }

    private function processFormData(Request $request) {
        $login = $request->get("login", "");
        $password = $request->request->get("password", "");
        $sshkey = $request->request->get("sshkey", "");

        $missings = array();
        if (!$login) { $missings[] = "loginrequired"; }
        if (!$password) { $missings[] = "passwordrequired"; }
        if (!$sshkey) { $missings[] = "sshkeyrequired"; }

        if(count($missings) > 0) {
            return $this->renderFormWithError($missings[0], $request);
        }

        /** @var UsernameValidityChecker $usernameChecker */

        $usernameChecker = $this->get('username_validity_checker');
        // Check the entered username for characters that our installation doesn't support
        $result = $usernameChecker->evaluate($login);
        if($result != '') {
            return $this->renderFormWithError($result, $request);
        }


        // Check reCAPTCHA
        if ( $this->config['use_recaptcha'] ) {
            /** @var RecaptchaService $recaptchaService */
            $recaptchaService = $this->get('recaptcha_service');

            $result = $recaptchaService->verify($request->request->get('g-recaptcha-response'), $login);
            if($result != '') {
                return $this->renderFormWithError($result, $request);
            }
        }

        /** @var LdapClient $ldapClient */
        $ldapClient = $this->get('ldap_client');

        $result = $ldapClient->connect();
        if($result != '') {
            return $this->renderFormWithError($result, $request);
        }

        $context = array ();

        // Check password{
        $result = $ldapClient->checkOldPassword2($login, $password, $context);
        if($result != '') {
            return $this->renderFormWithError($result, $request);
        }

        // Change sshPublicKey
        $result = $ldapClient->changeSshKey($context['user_dn'], $sshkey);
        if($result != 'sshkeychanged') {
            return $this->renderFormWithError($result, $request);
        }

        // Notify password change
        if ($this->config['notify_on_sshkey_change'] and $context['user_mail']) {
            /** @var MailNotificationService $mailService */
            $mailService = $this->get('mail_notification_service');

            $data = array( "login" => $login, "mail" => $context['user_mail'], "sshkey" => $sshkey);
            $mailService->send($context['user_mail'], $this->config['messages']["changesshkeysubject"], $this->config['messages']["changesshkeymessage"].$this->config['mail_signature'], $data);
        }

        return $this->renderSuccessPage();
    }

    private function renderEmptyForm(Request $request) {
        return $this->render('changesshkey.twig', array(
            'result' => 'emptysshkeychangeform',
            'login' => $request->get('login'),
        ));
    }

    private function renderFormWithError($error, Request $request) {
        return $this->render('changesshkey.twig', array(
            'result' => $error,
            'login' => $request->get('login'),
        ));
    }

    private function renderSuccessPage() {
        return $this->render('changesshkey.twig', array(
            'result' => 'sshkeychanged',
        ));
    }
}
