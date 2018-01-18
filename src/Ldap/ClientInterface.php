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
    public function connect();

    /**
     * @param string $login
     * @param array  $wanted
     *
     * @throws LdapErrorException
     * @throws LdapInvalidUserCredentialsException
     *
     * @return array Modified context
     */
    public function fetchUserEntryContext($login, $wanted);

    /**
     * @param string $oldpassword
     * @param array  $context
     *
     * @throws LdapInvalidUserCredentialsException
     */
    public function checkOldPassword($oldpassword, &$context);


    // TODO move out ?
    /**
     * @param string $login
     * @param string $question
     * @param string $answer
     * @param array  $context
     *
     * @return bool
     */
    public function checkQuestionAnswer($login, $question, $answer, &$context);

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
    public function checkMail($login, $mail);

    /**
     * @param string $userdn
     * @param string $question
     * @param string $answer
     *
     * @throws LdapErrorException
     * @throws LdapUpdateFailedException
     */
    public function changeQuestion($userdn, $question, $answer);

    /**
     * @param string $entryDn
     * @param string $newpassword
     * @param string $oldpassword
     * @param array  $context
     *
     * @throws LdapUpdateFailedException
     */
    public function changePassword($entryDn, $newpassword, $oldpassword, $context = []);

    /**
     * Change sshPublicKey attribute
     *
     * @param string $entryDn
     * @param string $sshkey
     *
     * @throws LdapUpdateFailedException
     */
    public function changeSshKey($entryDn, $sshkey);

    /**
     * For testing purpose only
     *
     * @internal
     *
     * @return resource
     */
    public function getConnection();
}