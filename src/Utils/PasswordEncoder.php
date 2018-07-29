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

/**
 * Class PasswordEncoder
 */
class PasswordEncoder
{
    private $hashOptions;

    /**
     * PasswordEncoder constructor.
     *
     * @param array $hashOptions
     */
    public function __construct(array $hashOptions)
    {
        $this->hashOptions = $hashOptions;
    }

    /**
     * @param string      $scheme
     * @param string      $password
     * @param string|null $salt
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    public function hash(string $scheme, string $password, ?string $salt = null): string
    {
        $scheme = strtoupper($scheme);

        if ('SSHA' === $scheme) {
            return $this->makeSshaPassword($password, $salt);
        }
        if ('SSHA256' === $scheme) {
            return $this->makeSsha256Password($password, $salt);
        }
        if ('SSHA384' === $scheme) {
            return $this->makeSsha384Password($password, $salt);
        }
        if ('SSHA512' === $scheme) {
            return $this->makeSsha512Password($password, $salt);
        }
        if ('SHA' === $scheme) {
            return $this->makeShaPassword($password);
        }
        if ('SHA256' === $scheme) {
            return $this->makeSha256Password($password);
        }
        if ('SHA384' === $scheme) {
            return $this->makeSha384Password($password);
        }
        if ('SHA512' === $scheme) {
            return $this->makeSha512Password($password);
        }
        if ('SMD5' === $scheme) {
            return $this->makeSmd5Password($password, $salt);
        }
        if ('MD5' === $scheme) {
            return $this->makeMd5Password($password);
        }
        if ('CRYPT' === $scheme) {
            return $this->makeCryptPassword($password, $this->hashOptions);
        }

        // TODO log algo not found
        return $password;
    }

    /**
     * @param string $format
     * @param string $password
     *
     * @return string
     */
    public function format(string $format, string $password): string
    {
        $format = strtoupper($format);

        if ('AD' === $format) {
            return $this->makeAdPassword($password);
        }

        if ('NT' === $format) {
            return $this->makeMd4Password($password);
        }

        return $password;
    }

    /**
     * Create SSHA password
     *
     * @param string      $password
     * @param string|null $salt
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    private function makeSshaPassword(string $password, ?string $salt = null): string
    {
        try {
            $salt = $salt ?? random_bytes(4);
        } catch (\Exception $e) {
            throw new CryptographyBrokenException('Unable to generate salt');
        }

        return '{SSHA}'.base64_encode(pack('H*', sha1($password.$salt)).$salt);
    }

    /**
     * Create SSHA256 password
     *
     * @param string      $password
     * @param string|null $salt
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    private function makeSsha256Password(string $password, ?string $salt = null): string
    {
        try {
            $salt = $salt ?? random_bytes(4);
        } catch (\Exception $e) {
            throw new CryptographyBrokenException('Unable to generate salt');
        }

        return '{SSHA256}'.base64_encode(pack('H*', hash('sha256', $password.$salt)).$salt);
    }

    /**
     * Create SSHA384 password
     *
     * @param string $password
     * @param string|null $salt
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    private function makeSsha384Password(string $password, ?string $salt = null): string
    {
        try {
            $salt = $salt ?? random_bytes(4);
        } catch (\Exception $e) {
            throw new CryptographyBrokenException('Unable to generate salt');
        }

        return '{SSHA384}'.base64_encode(pack('H*', hash('sha384', $password.$salt)).$salt);
    }

    /**
     * Create SSHA512 password
     *
     * @param string      $password
     * @param string|null $salt
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    private function makeSsha512Password(string $password, ?string $salt = null): string
    {
        try {
            $salt = $salt ?? random_bytes(4);
        } catch (\Exception $e) {
            throw new CryptographyBrokenException('Unable to generate salt');
        }

        return '{SSHA512}'.base64_encode(pack('H*', hash('sha512', $password.$salt)).$salt);
    }

    /**
     * Create SHA password
     *
     * @param string $password
     *
     * @return string
     */
    private function makeShaPassword(string $password): string
    {
        return '{SHA}'.base64_encode(pack('H*', sha1($password)));
    }

    /**
     * Create SHA256 password
     *
     * @param string $password
     *
     * @return string
     */
    private function makeSha256Password(string $password): string
    {
        return '{SHA256}'.base64_encode(pack('H*', hash('sha256', $password)));
    }

    /**
     * Create SHA384 password
     *
     * @param string $password
     *
     * @return string
     */
    private function makeSha384Password(string $password): string
    {
        return '{SHA384}'.base64_encode(pack('H*', hash('sha384', $password)));
    }

    /**
     * Create SHA512 password
     *
     * @param string $password
     *
     * @return string
     */
    private function makeSha512Password(string $password): string
    {
        return '{SHA512}'.base64_encode(pack('H*', hash('sha512', $password)));
    }

    /**
     * Create SMD5 password
     *
     * @param string      $password
     * @param string|null $salt
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    private function makeSmd5Password(string $password, ?string $salt = null): string
    {
        try {
            $salt = $salt ?? random_bytes(4);
        } catch (\Exception $e) {
            throw new CryptographyBrokenException('Unable to generate salt');
        }

        return '{SMD5}'.base64_encode(pack('H*', md5($password.$salt)).$salt);
    }

    /**
     * Create MD5 password
     *
     * @param string $password
     *
     * @return string
     */
    private function makeMd5Password(string $password): string
    {
        return '{MD5}'.base64_encode(pack('H*', md5($password)));
    }

    /**
     * Create CRYPT password
     *
     * @param string $password
     * @param array  $hashOptions
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    private function makeCryptPassword(string $password, array $hashOptions): string
    {

        $saltLength = 2;
        if (isset($hashOptions['crypt_salt_length'])) {
            $saltLength = $hashOptions['crypt_salt_length'];
        }

        // Generate salt
        $possible = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ./';
        $salt = '';

        while (\strlen($salt) < $saltLength) {
            try {
                $salt .= $possible[random_int(0, \strlen($possible) - 1)];
            } catch (\Exception $e) {
                throw new CryptographyBrokenException('Unable to generate salt');
            }
        }

        if (isset($hashOptions['crypt_salt_prefix'])) {
            $salt = $hashOptions['crypt_salt_prefix'].$salt;
        }

        return '{CRYPT}'.crypt($password, $salt);
    }

    /**
     * Create MD4 password (Microsoft NT password format)
     *
     * @param string $password
     *
     * @return string
     */
    private function makeMd4Password(string $password): string
    {
        // TODO mb_convert_encoding has a polyfill, iconv does not
        return strtoupper(hash('md4', iconv('UTF-8', 'UTF-16LE', $password)));
    }

    /**
     * Create AD password (Microsoft Active Directory password format)
     *
     * @param string $password
     *
     * @return string
     */
    private function makeAdPassword($password): string
    {
        $password = '"'.$password.'"';

        return mb_convert_encoding($password, 'UTF-16LE', 'UTF-8');
    }
}
