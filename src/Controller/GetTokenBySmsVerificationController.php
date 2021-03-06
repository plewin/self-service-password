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

use App\Exception\CryptographyBrokenException;
use App\Exception\LdapEntryFoundInvalidException;
use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Service\EncryptionService;
use App\Ldap\ClientInterface;
use App\Service\SmsNotificationService;
use App\Service\TokenManagerService;
use App\Service\UsernameValidityChecker;
use App\Utils\SmsTokenGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This page is called to send random generated password to user by SMS
 */
class GetTokenBySmsVerificationController extends Controller
{
    use CaptchaTrait;

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws CryptographyBrokenException
     */
    public function indexAction(Request $request): Response
    {
        if (!$this->getParameter('enable_reset_by_sms')) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->get('token');
        $smsToken = $request->get('smstoken');

        if (!empty($token) && !empty($smsToken) && $request->request->has('_csrf_token')) {
            if (!$this->isCsrfTokenValid('sms_token_attempt', $request->request->get('_csrf_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token');
            }

            return $this->processSmsTokenAttempt($request);
        }

        $encryptedSmsLogin = $request->get('encrypted_sms_login');

        if (!empty($encryptedSmsLogin) && $request->request->has('_csrf_token')) {
            if (!$this->isCsrfTokenValid('send_sms_token', $request->request->get('_csrf_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token');
            }

            return $this->generateAndSendSmsToken($request);
        }

        $login = $request->get('login');

        if (!empty($login) && $request->request->has('_csrf_token')) {
            if (!$this->isCsrfTokenValid('user_entry_search', $request->request->get('_csrf_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token');
            }

            return $this->processSearchUserFormData($request);
        }

        // render search user form empty
        return $this->render('self-service/sms_verification_user_search_form.html.twig', [
            'result' => 'emptysendsmsform',
            'problems' => [],
            'login' => $request->get('login'),
        ] + $this->getCaptchaTemplateExtraVars($request));
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws CryptographyBrokenException
     */
    private function processSmsTokenAttempt(Request $request): Response
    {
        /** @var EncryptionService $encryptionService */
        $encryptionService = $this->get('encryption_service');

        // Open session with the token
        $tokenId = $encryptionService->decrypt($request->get('token'));
        $receivedSmsCode = $request->get('smstoken');

        $session = $this->get('session');
        $session->start();

        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');

        if (!$session->has('smstoken')) {
            $logger->notice("Unable to open session $tokenId");

            return $this->render('self-service/sms_verification_sms_code_failure.html.twig', [
                //TODO precise error
                'result' => 'tokennotvalid',
            ]);
        }

        $smsToken = $session->get('smstoken');

        $login        = $smsToken['login'];
        $sessionToken = $smsToken['smstoken'];
        $attempts     = $smsToken['attempts'];

        if (null !== $this->getParameter('token_lifetime')) {
            // Manage lifetime with session content
            $tokenTime = $smsToken['time'];
            $smsTokenAgeInSeconds = time() - $tokenTime;
            if ($smsTokenAgeInSeconds > $this->getParameter('token_lifetime')) {
                $logger->warning('Token lifetime expired');
                $session->remove('smstoken');

                return $this->render('self-service/sms_verification_sms_code_failure.html.twig', [
                    //TODO precise error to user
                    'result' => 'tokennotvalid',
                ]);
            }
        }


        if (!hash_equals($sessionToken, $receivedSmsCode)) {
            if ($attempts < $this->getParameter('max_attempts')) {
                $smsToken['attempts'] += 1;
                $session->set('smstoken', $smsToken);
                $logger->notice("SMS token $receivedSmsCode not valid, attempt $attempts");
                $result = 'tokenattempts';

                return $this->renderTokenForm($result, $request->get('token'));
            }

            // TODO more precise log
            $logger->warning("SMS token $receivedSmsCode not valid");
            $session->remove('smstoken');

            return $this->render('self-service/sms_verification_sms_code_failure.html.twig', [
                //TODO precise error to user
                'result' => 'tokennotvalid',
            ]);
        }

        // we don't need smstoken anymore
        $session->remove('smstoken');

        /** @var TokenManagerService $tokenManagerService */
        $tokenManagerService = $this->get('token_manager_service');

        $token = $tokenManagerService->createToken($login);

        $resetUrl = $this->generateUrl('reset-password-with-token', ['token' => $token, 'source' => 'sms'], UrlGeneratorInterface::ABSOLUTE_URL);

        $logger->notice("Send reset URL $resetUrl");

        return $this->redirect($resetUrl);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws CryptographyBrokenException
     */
    private function generateAndSendSmsToken(Request $request): Response
    {
        /** @var EncryptionService $encryptionService */
        $encryptionService = $this->get('encryption_service');

        $encryptedSmsLogin = $request->get('encrypted_sms_login');

        $decryptedSmsLogin = explode(':', $encryptionService->decrypt($encryptedSmsLogin));
        [$sms, $login] = $decryptedSmsLogin;

        // Generate sms token and send by sms

        /** @var SmsTokenGenerator $smsTokenGenerator */
        $smsTokenGenerator = $this->get('sms_token_generator');

        // Generate sms token
        $smsCode = $smsTokenGenerator->generateSmsCode();

        /** @var SessionInterface $session */
        $session = $this->get('session');
        $session->start();
        $smsToken = [
            'login'    => $login,
            'smstoken' => $smsCode,
            'time'     => time(),
            'attempts' => 0,
        ];
        $session->set('smstoken', $smsToken);

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $data = [
            'sms_attribute'   => $sms,
            'smsresetmessage' => $translator->trans('smsresetmessage'),
            'smstoken'        => $smsCode,
        ];

        /** @var SmsNotificationService $smsService */
        $smsService = $this->get('sms_notification_service');

        // Send message
        $result = $smsService->send(
            $sms,
            $login,
            $this->getParameter('smsmail_subject'),
            $this->getParameter('sms_message'),
            $data,
            $smsCode
        );

        if ('smssent' === $result) {
            /** @var EncryptionService $encryptionService */
            $encryptionService = $this->get('encryption_service');

            $token  = $encryptionService->encrypt($session->getId());

            return $this->renderTokenForm($result, $token);
        }

        // sms failed, we don't need the smstoken anymore
        $session->remove('smstoken');

        return $this->render('self-service/sms_verification_sms_code_failure.html.twig', [
            'result' => $result,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws CryptographyBrokenException
     */
    private function processSearchUserFormData(Request $request): Response
    {
        $login = $request->get('login');

        // Check the entered username for characters that our installation doesn't support
        /** @var UsernameValidityChecker $usernameChecker */
        $usernameChecker = $this->get('username_validity_checker');

        $result = $usernameChecker->evaluate($login);
        if ('' !== $result) {
            return $this->renderSearchUserFormWithError('', [$result], $request);
        }

        // Check CAPTCHA
        if ($this->isCaptchaEnabled() && !$this->verifyCaptcha($request, $login)) {
            return $this->renderSearchUserFormWithError('', ['badcaptcha'], $request);
        }

        // Check sms
        /** @var ClientInterface $ldapClient */
        $ldapClient = $this->get('ldap_client');

        try {
            $ldapClient->connect();
            $wanted = ['dn', 'sms', 'displayname'];

            $context = $ldapClient->fetchUserEntryContext($login, $wanted);

            if (!$context['user_sms']) {
                /** @var LoggerInterface $logger */
                $logger = $this->get('logger');
                $logger->critical("No SMS number found for user $login");
                throw new LdapEntryFoundInvalidException("No SMS number found for user $login");
            }
        } catch (LdapErrorException $e) {
            // action probably not needed, problem with configuration or ldap is down
            return $this->renderSearchUserFormWithError('ldaperror', [], $request);
        } catch (LdapInvalidUserCredentialsException $e) {
            // user action needed, invalid login
            return $this->renderSearchUserFormWithError('', ['badcredentials'], $request);
        } catch (LdapEntryFoundInvalidException $e) {
            // user has no sms
            //TODO hide form ?
            return $this->renderSearchUserFormWithError('smsnonumber', [], $request);
        }

        $sms = $context['user_sms'];

        /** @var EncryptionService $encryptionService */
        $encryptionService = $this->get('encryption_service');

        $encryptedSmsLogin = $encryptionService->encrypt("$sms:$login");

        // Render search user from entry
        return $this->render('self-service/sms_verification_user_entry_confirmation.html.twig', [
            'result'              => 'smsuserfound',
            'displayname'         => $context['user_displayname'],
            'login'               => $login,
            'encrypted_sms_login' => $encryptedSmsLogin,
            'sms'                 => $this->getParameter('sms_partially_hide_number') ? substr_replace($sms, '****', 4, 4) : $sms,
        ] + $this->getCaptchaTemplateExtraVars($request));
    }

    /**
     * @param string  $result
     * @param array   $problems
     * @param Request $request
     *
     * @return Response
     */
    private function renderSearchUserFormWithError(string $result, array $problems, Request $request): Response
    {
        return $this->render('self-service/sms_verification_user_search_form.html.twig', [
            'result'   => $result,
            'problems' => $problems,
            'login'    => $request->get('login'),
        ] + $this->getCaptchaTemplateExtraVars($request));
    }

    /**
     * @param string $result
     * @param string $token
     *
     * @return Response
     */
    private function renderTokenForm(string $result, string $token): Response
    {
        return $this->render('self-service/sms_verification_sms_code_form.html.twig', [
            'result' => $result,
            'token'  => $token,
        ]);
    }
}
