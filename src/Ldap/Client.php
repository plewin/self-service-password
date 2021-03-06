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

namespace App\Ldap;

use App\Exception\CryptographyBrokenException;
use App\Exception\LdapEntryFoundInvalidException;
use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Exception\LdapUpdateFailedException;
use App\Utils\PasswordEncoder;
use Psr\Log\LoggerAwareTrait;
use function in_array;

/**
 * Class Client
 */
class Client implements ClientInterface
{
    use LoggerAwareTrait;

    /** @var array */
    private $config;

    /** @var resource */
    private $ldap;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    private $ldapUrl;
    private $ldapUseTls;
    private $ldapBindDn;
    private $ldapBindPw;
    private $whoChangePassword;
    private $adMode;
    private $ldapBase;
    private $ldapFilter;
    private $hash;
    private $smsAttribute;
    private $answerObjectClass;
    private $answerAttribute;
    private $whoChangeSshKey;
    private $sshKeyAttribute;
    private $mailAttribute;
    private $fullnameAttribute;
    private $adOptions;
    private $sambaMode;
    private $sambaOptions;
    private $shadowOptions;
    private $mailAddressUseLdap;

    /**
     * Client constructor.
     *
     * @param PasswordEncoder $passwordEncoder
     * @param string          $ldapUrl
     * @param bool            $ldapUseTls
     * @param string|null     $ldapBindDn
     * @param string|null     $ldapBindPw
     * @param string          $whoChangePassword
     * @param bool            $adMode
     * @param string          $ldapFilter
     * @param string          $ldapBase
     * @param string          $hash
     * @param string          $smsAttribute
     * @param string          $answerObjectClass
     * @param string          $answerAttribute
     * @param string          $whoChangeSshKey
     * @param string          $sshKeyAttribute
     * @param string          $mailAttribute
     * @param string          $fullnameAttribute
     * @param array           $adOptions
     * @param bool            $sambaMode
     * @param array           $sambaOptions
     * @param array           $shadowOptions
     * @param bool            $mailAddressUseLdap
     */
    public function __construct(
        $passwordEncoder,
        string $ldapUrl,
        bool $ldapUseTls,
        ?string $ldapBindDn,
        ?string $ldapBindPw,
        string $whoChangePassword,
        bool $adMode,
        string $ldapFilter,
        string $ldapBase,
        string $hash,
        string $smsAttribute,
        string $answerObjectClass,
        string $answerAttribute,
        string $whoChangeSshKey,
        string $sshKeyAttribute,
        string $mailAttribute,
        string $fullnameAttribute,
        array $adOptions,
        bool $sambaMode,
        array $sambaOptions,
        array $shadowOptions,
        bool $mailAddressUseLdap
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->ldapUrl = $ldapUrl;
        $this->ldapUseTls = $ldapUseTls;
        $this->ldapBindDn = $ldapBindDn;
        $this->ldapBindPw = $ldapBindPw;
        $this->whoChangePassword = $whoChangePassword;
        $this->adMode = $adMode;
        $this->ldapBase = $ldapBase;
        $this->ldapFilter = $ldapFilter;
        $this->hash = $hash;
        $this->smsAttribute = $smsAttribute;
        $this->answerObjectClass = $answerObjectClass;
        $this->answerAttribute = $answerAttribute;
        $this->whoChangeSshKey = $whoChangeSshKey;
        $this->sshKeyAttribute = $sshKeyAttribute;
        $this->mailAttribute = $mailAttribute;
        $this->fullnameAttribute = $fullnameAttribute;
        $this->adOptions = $adOptions;
        $this->sambaMode = $sambaMode;
        $this->sambaOptions = $sambaOptions;
        $this->shadowOptions = $shadowOptions;
        $this->mailAddressUseLdap = $mailAddressUseLdap;
    }

    /**
     * @inheritdoc
     */
    public function connect(): bool
    {
        //Connect to LDAP
        $this->ldap = ldap_connect($this->ldapUrl);

        ErrorHandler::start(E_WARNING);
        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        // TODO rigor
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
        if ($this->ldapUseTls && !ldap_start_tls($this->ldap)) {
            ErrorHandler::stop();

            $this->logger->alert('LDAP - Unable to use StartTLS');
            throw new LdapErrorException('LDAP - Unable to use StartTLS');
        }
        ErrorHandler::stop();

        // Bind
        ErrorHandler::start(E_WARNING);
        $success = ldap_bind($this->ldap, $this->ldapBindDn, $this->ldapBindPw);
        ErrorHandler::stop();
        if (false === $success) {
            $errno = ldap_errno($this->ldap);
            $this->logger->alert("LDAP - Bind error $errno (".ldap_error($this->ldap).')');
            throw new LdapErrorException("LDAP - Bind error $errno (".ldap_error($this->ldap).')');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fetchUserEntryContext(string $login, array $wanted): array
    {
        $context = [];

        $entry = $this->getUserEntry($login);

        if (in_array('dn', $wanted, true)) {
            $this->updateContextDn($entry, $context);
        }
        if (in_array('samba', $wanted, true) || in_array('shadow', $wanted, true)) {
            $this->updateContextSambaAndShadow($entry, $context);
        }
        if (in_array('mail', $wanted, true)) {
            $this->updateContextMail($entry, $context);
        }
        if (in_array('sms', $wanted, true)) {
            $this->updateContextSms($entry, $context);
        }
        if (in_array('displayname', $wanted, true)) {
            $this->updateContextDisplayName($entry, $context);
        }
        if (in_array('questions', $wanted, true)) {
            $this->updateContextQuestions($entry, $context);
        }

        return $context;
    }

    /**
     * @inheritdoc
     */
    public function checkOldPassword(string $oldPassword, array &$context): void
    {
        $success = $this->verifyPasswordWithBind($context['user_dn'], $oldPassword);
        if (false === $success) {
            $errno = ldap_errno($this->ldap);
            $this->logger->notice("LDAP - Bind user error $errno  (".ldap_error($this->ldap).')');
            throw new LdapInvalidUserCredentialsException("LDAP - Bind user error $errno  (".ldap_error($this->ldap).')');
        }
    }

    /**
     * @inheritdoc
     */
    public function checkQuestionAnswer(string $login, string $question, string $answer, array &$context): bool
    {
        $match = false;

        // Match with user submitted values
        foreach ($context['user_answers'] as $questionValue) {
            $answer = preg_quote($answer, '/');
            if (preg_match("/^\{$question\}$answer$/i", $questionValue)) {
                $match = true;
            }
        }

        if (!$match) {
            $this->logger->notice("Answer does not match question for user $login");

            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function checkMail(string $login, string $mail): bool
    {
        $fetchMailFromLdap = $this->mailAddressUseLdap;

        $wanted = ['mail'];
        $context = $this->fetchUserEntryContext($login, $wanted);

        if (null === $context['user_mail']) {
            $this->logger->warning("Mail not found for user $login");
            throw new LdapEntryFoundInvalidException("Mail not found for user $login");
        }

        $match = false;

        if ($fetchMailFromLdap) {
            // Match with user submitted values
            foreach ($context['user_mails'] as $mailValue) {
                if (strcasecmp($mail, $mailValue) === 0) {
                    $match = true;
                    break;
                }
            }
        } else {
            $match = hash_equals($context['user_mail'], $mail);
        }

        if (!$match) {
            $this->logger->notice("Mail $mail does not match for user $login");
            throw new LdapEntryFoundInvalidException("Mail $mail does not match for user $login");
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function changeQuestion(string $userDn, string $question, string $answer): void
    {
        // Rebind as Manager if needed
        if ('manager' === $this->whoChangePassword) {
            $this->rebindAsManager();
        }

        // Check objectClass presence
        $search = ldap_search($this->ldap, $userDn, '(objectClass=*)', ['objectClass']);
        if (false === $search) {
            $this->throwLdapError('Search error');
        }

        // Get objectClass values from user entry
        $entry = ldap_first_entry($this->ldap, $search);
        $ocValues = ldap_get_values($this->ldap, $entry, 'objectClass');

        // Remove 'count' key
        unset($ocValues['count']);

        if (!in_array($this->answerObjectClass, $ocValues, true)) {
            // Answer objectClass is not present, add it
            array_push($ocValues, $this->answerObjectClass);
            $ocValues = array_values($ocValues);
            $userdata['objectClass'] = $ocValues;
        }

        // Question/Answer
        $userdata[$this->answerAttribute] = '{'.$question.'}'.$answer;

        // Commit modification on directory
        $success = ldap_mod_replace($this->ldap, $userDn, $userdata);
        if (false === $success) {
            $errno = ldap_errno($this->ldap);
            $this->logger->critical("LDAP - Modify answer (error $errno (".ldap_error($this->ldap).')');
            throw new LdapUpdateFailedException("LDAP - Modify answer (error $errno (".ldap_error($this->ldap).')');
        }
    }

    /**
     * @inheritdoc
     *
     * @throws CryptographyBrokenException
     */
    public function changePassword(string $entryDn, string $newPassword, string $oldPassword, array $context = []): void
    {
        // Rebind as Manager if needed
        // TODO detect if needed ?
        if ('manager' === $this->whoChangePassword) {
            $this->rebindAsManager();
        }

        $hash = $this->hash;

        // Get hash type if hash is set to auto
        if ('auto' === $hash) {
            $hash = $this->findHash($entryDn);
        }
        // Transform password value
        if ('clear' !== $hash) {
            $newPassword = $this->passwordEncoder->hash($hash, $newPassword);
        }

        // Special case: AD mode with password changed as user
        if ($this->adMode && 'user' === $this->whoChangePassword) {
            // The AD password change procedure is modifying the attribute unicodePwd by
            // first deleting unicodePwd with the current password and them adding it with the
            // the new password
            $oldPassword = $this->passwordEncoder->format('ad', $oldPassword);
            $newPassword = $this->passwordEncoder->format('ad', $newPassword);

            $modifications = [
                ['attrib' => 'unicodePwd', 'modtype' => LDAP_MODIFY_BATCH_REMOVE, 'values' => [$oldPassword]],
                ['attrib' => 'unicodePwd', 'modtype' => LDAP_MODIFY_BATCH_ADD, 'values' => [$newPassword]],
            ];

            $success = ldap_modify_batch($this->ldap, $entryDn, $modifications);
            if (!$success) {
                $errno = ldap_errno($this->ldap);
                $this->logger->critical("LDAP - Modify password error $errno (".ldap_error($this->ldap).')');
                throw new LdapUpdateFailedException("LDAP - Modify password error $errno (".ldap_error($this->ldap).')');
            }

            return;
        }

        // Generic case

        $sambaMode = $this->sambaMode;
        $sambaOptions = $this->sambaOptions;
        if (isset($context['user_is_samba_account']) && false === $context['user_is_samba_account']) {
            $sambaMode = false;
        }

        $shadowOptions = $this->shadowOptions;
        if (isset($context['user_is_shadow_account']) && false === $context['user_is_shadow_account']) {
            $shadowOptions['update_shadowLastChange'] = false;
            $shadowOptions['update_shadowExpire'] = false;
        }

        $time = time();

        $userData = [];

        // Set samba attributes
        if ($sambaMode) {
            $userData['sambaNTPassword'] = $this->passwordEncoder->format('nt', $newPassword);
            $userData['sambaPwdLastSet'] = $time;
            if (isset($sambaOptions['min_age']) && $sambaOptions['min_age'] > 0) {
                $userData['sambaPwdCanChange'] = $time + ( $sambaOptions['min_age'] * 86400 );
            }
            if (isset($sambaOptions['max_age']) && $sambaOptions['max_age'] > 0) {
                $userData['sambaPwdMustChange'] = $time + ( $sambaOptions['max_age'] * 86400 );
            }
        }

        // Set shadow attributes
        if ($shadowOptions['update_shadowLastChange']) {
            $userData['shadowLastChange'] = floor($time / 86400);
        }
        if ($shadowOptions['update_shadowExpire']) {
            $daysBeforeExpiration = $shadowOptions['shadow_expire_days'];
            if ($daysBeforeExpiration > 0) {
                $userData['shadowExpire'] = floor(($time / 86400) + $daysBeforeExpiration);
            } else {
                $userData['shadowExpire'] = $daysBeforeExpiration;
            }
        }

        // Set password value
        if ($this->adMode) {
            $userData['unicodePwd'] = $this->passwordEncoder->format('ad', $newPassword);

            if ($this->adOptions['enable_force_unlock']) {
                $userData['lockoutTime'] = 0;
            }
            if ($this->adOptions['enable_force_password_change']) {
                $userData['pwdLastSet'] = 0;
            }
        } else {
            $userData['userPassword'] = $newPassword;
        }

        ErrorHandler::start(E_WARNING);
        $success = ldap_mod_replace($this->ldap, $entryDn, $userData);
        ErrorHandler::stop();
        if (!$success) {
            $errno = ldap_errno($this->ldap);
            $this->logger->critical("LDAP - Modify password error $errno (".ldap_error($this->ldap).')');
            throw new LdapUpdateFailedException("LDAP - Modify password error $errno (".ldap_error($this->ldap).')');
        }
    }

    /**
     * @param string $entryDn
     *
     * @return string
     */
    private function findHash(string $entryDn): string
    {
        $searchUserPassword = ldap_read($this->ldap, $entryDn, '(objectClass=*)', ['userPassword']);
        if ($searchUserPassword) {
            $userPassword = ldap_get_values($this->ldap, ldap_first_entry($this->ldap, $searchUserPassword), 'userPassword');
            if (false !== $userPassword && preg_match('/^\{(\w+)\}/', $userPassword[0], $matches)) {
                return strtoupper($matches[1]);
            }
        }

        return 'clear';
    }

    /**
     * @inheritdoc
     */
    public function changeSshKey(string $entryDn, string $sshPublicKey): void
    {
        // Rebind as Manager if needed
        if ('manager' === $this->whoChangeSshKey) {
            $this->rebindAsManager();
        }

        $userData = [];
        $userData[$this->sshKeyAttribute] = $sshPublicKey;

        // Commit modification on directory
        $success = ldap_mod_replace($this->ldap, $entryDn, $userData);

        if (false === $success) {
            $errno = ldap_errno($this->ldap);
            $this->logger->critical("LDAP - Modify $this->sshKeyAttribute error $errno (".ldap_error($this->ldap).')');
            throw new LdapUpdateFailedException("LDAP - Modify $this->sshKeyAttribute error $errno (".ldap_error($this->ldap).')');
        }
    }

    /**
     * @param string $error
     *
     * @throws LdapErrorException
     */
    private function throwLdapError($error): void
    {
        $errno = ldap_errno($this->ldap);
        $this->logger->notice("LDAP - $error $errno (".ldap_error($this->ldap).')');
        throw new LdapErrorException("LDAP - $error $errno (".ldap_error($this->ldap).')');
    }

    /**
     * @param string $login
     *
     * @return resource
     *
     * @throws LdapErrorException
     * @throws LdapInvalidUserCredentialsException
     */
    private function getUserEntry(string $login)
    {
        $escapedLogin = ldap_escape($login, null, LDAP_ESCAPE_FILTER);
        // Search for user
        $refinedLdapFilter = str_replace('{login}', $escapedLogin, $this->ldapFilter);
        ErrorHandler::start(E_WARNING);
        $search = ldap_search($this->ldap, $this->ldapBase, $refinedLdapFilter);
        ErrorHandler::stop();
        if (false === $search) {
            $this->throwLdapError('Search error');
        }

        $entry = ldap_first_entry($this->ldap, $search);
        if (false === $entry) {
            $this->logger->notice("LDAP - User $login not found");
            throw new LdapInvalidUserCredentialsException("LDAP - User $login not found");
        }

        return $entry;
    }

    /**
     * @param resource $entry
     * @param array    $context
     */
    private function updateContextDn($entry, array &$context): void
    {
        $userDn = ldap_get_dn($this->ldap, $entry);
        $context['user_dn'] = $userDn;
    }

    /**
     * @param resource $entry
     * @param array    $context
     */
    private function updateContextDisplayName($entry, array &$context): void
    {
        $displayName = ldap_get_values($this->ldap, $entry, $this->fullnameAttribute);
        //TODO rigor
        $context['user_displayname'] = $displayName[0];
    }

    /**
     * @param resource $entry
     * @param array    $context
     */
    private function updateContextMail($entry, &$context): void
    {
        ErrorHandler::start(E_WARNING);
        $mailValues = ldap_get_values($this->ldap, $entry, $this->mailAttribute);
        ErrorHandler::stop();
        //TODO rigor

        $mails = [];
        $mail = null;

        if ($mailValues['count'] > 0) {
            unset($mailValues['count']);

            if (strcasecmp($this->mailAttribute, 'proxyAddresses') === 0) {
                $removePrefixFn = function ($mailValue) {
                    return str_ireplace('smtp:', '', $mailValue);
                };
                $mailValues = array_map($removePrefixFn, $mailValues);
            }

            $mail = $mailValues[0];
            $mails = $mailValues;
        }

        $context['user_mail'] = $mail;
        $context['user_mails'] = $mails;
    }

    /**
     * @param resource $entry
     * @param array    $context
     */
    private function updateContextSms($entry, array &$context): void
    {
        $smsSanitizeNumber = $this->config['sms_sanitize_number'];
        $smsTruncateNumber = $this->config['sms_truncate_number'];
        $smsTruncateLength = $this->config['sms_truncate_number_length'];

        // Get sms values
        $smsValues = ldap_get_values($this->ldap, $entry, $this->smsAttribute);

        $context['user_sms_raw'] = null;
        $context['user_sms'] = null;

        // Check sms number
        if ($smsValues['count'] > 0) {
            $sms = $smsValues[0];
            $context['user_sms_raw'] = $sms;
            if ($smsSanitizeNumber) {
                $sms = preg_replace('/\D/', '', $sms);
            }
            if ($smsTruncateNumber) {
                $sms = substr($sms, -$smsTruncateLength);
            }
            $context['user_sms'] = $sms;
        }
    }

    /**
     * @param resource $entry
     * @param array    $context
     */
    private function updateContextSambaAndShadow($entry, array &$context): void
    {
        // Check objectClass to allow samba and shadow updates
        $ocValues = ldap_get_values($this->ldap, $entry, 'objectClass');

        $context['user_is_samba_account'] = in_array('sambaSamAccount', $ocValues, true) || in_array('sambaSAMAccount', $ocValues, true);
        $context['user_is_shadow_account'] = in_array('shadowAccount', $ocValues, true);
    }

    /**
     * @param resource $entry
     * @param array    $context
     */
    private function updateContextQuestions($entry, array &$context): void
    {
        // Get question/answer values
        $questionValues = ldap_get_values($this->ldap, $entry, $this->answerAttribute);
        unset($questionValues['count']);

        $context['user_answers'] = [];

        foreach ($questionValues as $questionValue) {
            $context['user_answers'][] = $questionValue;
        }
    }

    /**
     * @param string $dn
     * @param string $password
     *
     * @return bool
     */
    private function verifyPasswordWithBind(string $dn, string $password): bool
    {
        // Bind with current password
        $success = @ldap_bind($this->ldap, $dn, $password);
        if (false === $success) {
            $errno = ldap_errno($this->ldap);
            if ((49 === $errno) && $this->adMode && ldap_get_option($this->ldap, 0x0032, $extendedError)) {
                $this->logger->notice("LDAP - Bind user extended_error $extendedError  (".ldap_error($this->ldap).')');
                $extendedError = explode(', ', $extendedError);
                if (strpos($extendedError[2], '773') || strpos($extendedError[0], 'NT_STATUS_PASSWORD_MUST_CHANGE')) {
                    $this->logger->notice('LDAP - Bind user password needs to be changed');

                    return true;
                }
                if ($this->adOptions['enable_change_expired_password'] && ( strpos($extendedError[2], '532') || strpos($extendedError[0], 'NT_STATUS_ACCOUNT_EXPIRED'))) {
                    $this->logger->notice('LDAP - Bind user password is expired');

                    return true;
                }
            }

            return false;
        }

        return true;
    }

    private function rebindAsManager(): void
    {
        ldap_bind($this->ldap, $this->ldapBindDn, $this->ldapBindPw);
    }

    /**
     * @inheritdoc
     */
    public function getConnection()
    {
        return $this->ldap;
    }
}
