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

namespace App\Utils;

use App\Exception\CryptographyBrokenException;
use InvalidArgumentException;

/**
 * Class PasswordVerifier
 */
class PasswordVerifier
{
    private $passwordEncoder;

    /**
     * PasswordEncoder constructor.
     *
     * @param PasswordEncoder $passwordEncoder
     */
    public function __construct($passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param string $password
     * @param string $ldapHash
     *
     * @return bool
     *
     * @throws CryptographyBrokenException
     */
    public function verify(string $password, string $ldapHash): bool
    {
        $hashDetails = $this->userPasswordAnalyzer($ldapHash);

        // return false if the ldap password could not be analyzed (unknown scheme)
        if (false === $hashDetails) {
            return false;
        }

        $password = $this->passwordEncoder->hash($hashDetails['scheme'], $password, $hashDetails['salt']);

        return hash_equals($password, $ldapHash);
    }

    /**
     * Returns an array with "scheme" and "salt"
     *
     * @param string $userPasswordValue
     *
     * @return array|false
     */
    private function userPasswordAnalyzer(string $userPasswordValue)
    {
        $matches = [];
        if (!preg_match('/{(\S+)}(\S+)/', $userPasswordValue, $matches)) {
            throw new InvalidArgumentException('Hashed password does not validate LDAP format');
        }

        $scheme = strtoupper($matches[1]);
        $base64HashAndSalt = $matches[2];

        if ('CRYPT' === $scheme) {
            // crypt is actually easier, we do not extract the salt, we use the hash as the salt for crypt()
            return [
                'user_password_value' => $userPasswordValue,
                'scheme'              => $scheme,
                'password_hash'       => $base64HashAndSalt,
                'salt'                => $base64HashAndSalt, // hash can be used as salt
            ];
        }

        $schemes = [
            'SHA'     => ['size' =>  40, 'salted' => false],
            'SHA256'  => ['size' =>  64, 'salted' => false],
            'SHA384'  => ['size' =>  96, 'salted' => false],
            'SHA512'  => ['size' => 128, 'salted' => false],
            'SSHA'    => ['size' =>  40, 'salted' =>  true],
            'SSHA256' => ['size' =>  64, 'salted' =>  true],
            'SSHA384' => ['size' =>  96, 'salted' =>  true],
            'SSHA512' => ['size' => 128, 'salted' =>  true],
            'MD5'     => ['size' =>  32, 'salted' => false],
            'SMD5'    => ['size' =>  32, 'salted' =>  true],
        ];

        if (!array_key_exists($scheme, $schemes)) {
            error_log("user_password_analyzer: password hashing scheme '$scheme' is not supported");
            //TODO rigor
            return false;
        }

        $hashAndSalt = base64_decode($base64HashAndSalt);

        // salt may contain null bytes
        $unpacked = unpack("H{$schemes[$scheme]['size']}hash/C*salt", $hashAndSalt);

        $passwordHash = $unpacked['hash'];

        // remove hash to keep only the salt bytes
        unset($unpacked['hash']);
        $salt = implode('', array_map('\chr', $unpacked));
        $salt = $schemes[$scheme]['salted'] ? $salt : false;

        return [
            'user_password_value' => $userPasswordValue,
            'scheme'              => $scheme,
            'password_hash'       => $passwordHash,
            'salt'                => $salt,
        ];
    }
}
