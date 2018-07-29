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

use App\Exception\LdapEntryFoundInvalidException;
use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Exception\LdapUpdateFailedException;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface ClientInterface
 */
interface ClientInterface extends LoggerAwareInterface
{
    /**
     * @throws LdapErrorException
     *
     * @return true on success
     */
    public function connect(): bool;

    /**
     * @param string $login
     * @param array  $wanted
     *
     * @throws LdapErrorException
     * @throws LdapInvalidUserCredentialsException
     *
     * @return array Modified context
     */
    public function fetchUserEntryContext(string $login, array $wanted): array;

    /**
     * @param string $oldPassword
     * @param array  $context
     *
     * @throws LdapInvalidUserCredentialsException
     */
    public function checkOldPassword(string $oldPassword, array &$context): void;


    // TODO move out ?
    /**
     * @param string $login
     * @param string $question
     * @param string $answer
     * @param array  $context
     *
     * @return bool
     */
    public function checkQuestionAnswer(string $login, string $question, string $answer, array &$context): bool;

    /**
     * @param string $login
     * @param string $mail
     *
     * @throws LdapEntryFoundInvalidException
     * @throws LdapErrorException
     * @throws LdapInvalidUserCredentialsException
     *
     * @return true Always true, on error, exceptions
     */
    public function checkMail(string $login, string $mail): bool;

    /**
     * @param string $userDn
     * @param string $question
     * @param string $answer
     *
     * @throws LdapErrorException
     * @throws LdapUpdateFailedException
     */
    public function changeQuestion(string $userDn, string $question, string $answer): void;

    /**
     * @param string $entryDn
     * @param string $newPassword
     * @param string $oldPassword
     * @param array  $context
     *
     * @throws LdapUpdateFailedException
     */
    public function changePassword(string $entryDn, string $newPassword, string $oldPassword, array $context = []): void;

    /**
     * Change sshPublicKey attribute
     *
     * @param string $entryDn
     * @param string $sshPublicKey
     *
     * @throws LdapUpdateFailedException
     */
    public function changeSshKey(string $entryDn, string $sshPublicKey): void;

    /**
     * For testing purpose only
     *
     * @internal
     *
     * @return resource
     */
    public function getConnection();
}
