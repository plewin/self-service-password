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

use Symfony\Component\DependencyInjection\Reference;
use App\EventSubscriber;
use App\Controller;

$container->register('locale.subscriber', EventSubscriber\LocaleSubscriber::class)
    ->addArgument('%locale%')
    ->addArgument(new Reference('translator'))
    ->addArgument('%app_locales%')
    ->addTag('kernel.event_subscriber')
;

$container->register('posthook.subscriber', EventSubscriber\PosthookSubscriber::class)
    ->addArgument('%enable_posthook%')
    ->addArgument(new Reference('posthook_executor'))
    ->addTag('kernel.event_subscriber')
;

$container->register('notifier.subscriber', EventSubscriber\NotificationSubscriber::class)
    ->addArgument(new Reference('mail_notification_service'))
    ->addArgument(new Reference('translator'))
    ->addArgument('%notify_user_on_password_change%')
    ->addArgument('%notify_user_on_sshkey_change%')
    ->addTag('kernel.event_subscriber')
;

$container->register('encryption_service', App\Service\EncryptionService::class)
    ->addArgument('%secret%')
    ->addMethodCall('setLogger', [new Reference('logger')])
;

$container->register('sms_token_generator', App\Utils\SmsTokenGenerator::class)
    ->addArgument('%sms_token_length%')
;

$container->register('username_validity_checker', App\Service\UsernameValidityChecker::class)
    ->addArgument('%login_forbidden_chars%')
    ->addMethodCall('setLogger', [new Reference('logger')])
;

$container->register('recaptcha_service', App\Service\RecaptchaService::class)
    ->addArgument('%recaptcha_privatekey%')
    ->addArgument('%recaptcha_request_method%')
    ->addMethodCall('setLogger', [new Reference('logger')])
;

$container
    ->setAlias('password_strength_checker', 'password_strength_checker.multi')
    ->setPublic(true)
;

$container->register('password_strength_checker.multi', App\PasswordStrengthChecker\MultiChecker::class)
    ->addMethodCall('setContainer', [new Reference('service_container')])
    ->addMethodCall('addChecker', [new Reference('password_strength_checker.zxcvbn')])
    ->addMethodCall('addChecker', [new Reference('password_strength_checker.legacy')])
    ->addMethodCall('addChecker', [new Reference('password_strength_checker.dictionary')])
;

$container->register('password_strength_checker.zxcvbn', App\PasswordStrengthChecker\ZxcvbnChecker::class)
    ->addArgument('%psc_zxcvbn%')
;

$container->register('password_strength_checker.legacy', App\PasswordStrengthChecker\LegacyChecker::class)
    ->addArgument('%pwd_policy_config%')
;

$container->register('password_strength_checker.dictionary', App\PasswordStrengthChecker\DictionaryChecker::class)
    ->addArgument('%psc_dictionary%')
    ->addArgument(new Reference('request_stack'))
    ->addArgument(new Reference('router'))
    ->setPublic(true)
;

$container->register('mail_sender', App\Utils\MailSender::class)
    ->addArgument(new Reference('mailer'))
    ->addMethodCall('setLogger', [new Reference('logger')])
;

$container->register('mail_notification_service', App\Service\MailNotificationService::class)
    ->addArgument(new Reference('twig'))
    ->addArgument(new Reference('mail_sender'))
    ->addArgument('%mail_from%')
    ->addArgument('%mail_from_name%')
    ->addMethodCall('setLogger', [new Reference('logger')])
;

$container->register('sms_notification_service', App\Service\SmsNotificationService::class)
    ->addArgument('%sms_method%')
    ->addArgument(new Reference('mail_sender'))
    ->addArgument('%smsmailto%')
    ->addArgument('%mail_from%')
    ->addArgument('%mail_from_name%')
    ->addArgument('%sms_api_lib%')
    ->addMethodCall('setLogger', [new Reference('logger')])
;

$container->register('ldap_client', '%ldap_client.class%')
    ->addArgument(new Reference('password_encoder'))
    ->addArgument('%ldap_url%')
    ->addArgument('%ldap_use_tls%')
    ->addArgument('%ldap_binddn%')
    ->addArgument('%ldap_bindpw%')
    ->addArgument('%who_change_password%')
    ->addArgument('%enable_ad_mode%')
    ->addArgument('%ldap_filter%')
    ->addArgument('%ldap_base%')
    ->addArgument('%hash%')
    ->addArgument('%ldap_attribute_sms%')
    ->addArgument('%answer_objectClass%')
    ->addArgument('%answer_attribute%')
    ->addArgument('%who_change_ssh_key%')
    ->addArgument('%ldap_attribute_sshkey%')
    ->addArgument('%ldap_attribute_mail%')
    ->addArgument('%ldap_attribute_fullname%')
    ->addArgument('%ad_options%')
    ->addArgument('%enable_samba_mode%')
    ->addArgument('%samba_options%')
    ->addArgument('%shadow_options%')
    ->addArgument('%mail_address_use_ldap%')
    ->addMethodCall('setLogger', [new Reference('logger')])
;

$container->register('password_encoder', App\Utils\PasswordEncoder::class)
    ->addArgument('%hash_options%')
;

$container->register('posthook_executor', App\Service\PosthookExecutor::class)
    ->addArgument('%posthook%')
;

$container->register('token_manager_service', App\Service\TokenManagerService::class)
    ->addArgument(new Reference('session'))
    ->addArgument(new Reference('encryption_service'))
    ->addArgument('%token_lifetime%')
    ->addMethodCall('setLogger', [new Reference('logger')])
;

$container->register('twig.controller.exception', Controller\ExceptionController::class)
    ->addArgument(new Reference('twig'))
    ->addArgument('%kernel.debug%')
;

$container
    ->register('app.twig_extension', App\Twig\AppExtension::class)
    ->addArgument('%pwd_show_policy%')
    ->addArgument(new Reference('security.csrf.token_manager'))
    ->setPublic(false)
    ->addTag('twig.extension')
;

