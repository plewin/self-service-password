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
use App\Exception\LdapInvalidUserCredentialsException;
use App\Exception\LdapUpdateFailedException;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MockClient
 */
class MockClient implements ClientInterface
{
    use LoggerAwareTrait;

    private $mockData;

    /**
     * MockClient constructor.
     */
    public function __construct()
    {
        $this->mockData = [
            'uid=user1,ou=People,dc=example,dc=com' => [
                'givenName' => 'User1GivenName',
                'sn' => 'User1SN',
                'mobile' => '0612345678',
                'displayName' => 'User1 DisplayName',
                'userPassword' => 'password1',
                'mail' => 'user1@example.com',
                'questions' => [
                    'birthday' => 'goodbirthday1',
                ],
            ],
            'uid=user2,ou=People,dc=example,dc=com' => [
                'givenName' => 'User2GivenName',
                'sn' => 'User2SN',
                'mobile' => '0712345678',
                'displayName' => 'User2 DisplayName',
                'userPassword' => 'password2',
                'mail' => 'user2@example.com',
                'questions' => [
                    'birthday' => 'goodbirthday2',
                ],
            ],
            'uid=user3,ou=People,dc=example,dc=com' => [
                'givenName' => 'User3GivenName',
                'sn' => 'User3SN',
                // use 3 has no mobile
                'displayName' => 'User3 DisplayName',
                'userPassword' => 'password3',
                'mail' => 'user3@example.com',
                'questions' => [
                    'birthday' => 'goodbirthday3',
                ],
            ],
            'uid=user10,ou=People,dc=example,dc=com' => [
                'givenName' => 'User10GivenName',
                'sn' => 'User10SN',
                'displayName' => 'User10 DisplayName',
                'userPassword' => 'password10',
                'mail' => 'user10@example.com',
                'questions' => [
                    'birthday' => 'goodbirthday10',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function connect(): bool
    {
        // fake connect
        return true;
    }

    /**
     * @inheritdoc
     */
    public function fetchUserEntryContext(string $login, array $wanted): array
    {
        $dn = 'uid='.$login.',ou=People,dc=example,dc=com';

        if (!isset($this->mockData[$dn])) {
            throw new LdapInvalidUserCredentialsException();
        }

        $context['user_dn'] = $dn;
        $context['user_sms'] = $this->mockData[$dn]['mobile'] ?? null;
        $context['user_displayname'] = $this->mockData[$dn]['displayName'];
        $context['user_mail'] = $this->mockData[$dn]['mail'] ?? null;

        return $context;
    }

    /**
     * @inheritdoc
     */
    public function checkOldPassword(string $oldpassword, array &$context): void
    {
        $dn = $context['user_dn'];

        if ($this->mockData[$dn]['userPassword'] !== $oldpassword) {
            throw new LdapInvalidUserCredentialsException();
        }
    }

    /**
     * @inheritdoc
     */
    public function checkQuestionAnswer(string $login, string $question, string $answer, array &$context): bool
    {
        $dn = 'uid='.$login.',ou=People,dc=example,dc=com';

        return $this->mockData[$dn]['questions'][$question] === $answer;
    }

    /**
     * @inheritdoc
     */
    public function checkMail(string $login, string $mail): bool
    {
        $dn = 'uid='.$login.',ou=People,dc=example,dc=com';
        if (!isset($this->mockData[$dn])) {
            throw new LdapInvalidUserCredentialsException();
        }

        $validMail = $this->mockData[$dn]['mail'];

        if ($mail !== $validMail) {
            throw new LdapEntryFoundInvalidException();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function changeQuestion(string $userdn, string $question, string $answer): void
    {

    }

    /**
     * @inheritdoc
     */
    public function changePassword(string $entryDn, string $newpassword, string $oldpassword, array $context = []): void
    {
        if ('uid=user10,ou=People,dc=example,dc=com' === $entryDn) {
            // poor guy has password change forbidden in password policy
            throw new LdapUpdateFailedException();
        }
    }

    /**
     * @inheritdoc
     */
    public function changeSshKey(string $entryDn, string $sshkey): void
    {

    }


    /**
     * @inheritdoc
     */
    public function getConnection()
    {
        return null;
    }
}
