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

namespace App\Service;

use App\Exception\CryptographyBrokenException;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class EncryptionService
 */
class EncryptionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $keyphrase;

    /**
     * EncryptionService constructor.
     *
     * @param string $keyphrase Password for encryption
     */
    public function __construct(string $keyphrase)
    {
        $this->keyphrase = $keyphrase;
    }

    /**
     * Encrypt a data
     *
     * @param string $data Data to encrypt
     *
     * @return string Encrypted data, base64 encoded
     *
     * @throws CryptographyBrokenException
     */
    public function encrypt(string $data): string
    {
        try {
            return base64_encode(Crypto::encryptWithPassword($data, $this->keyphrase, true));
        } catch (EnvironmentIsBrokenException $e) {
            $this->logger->alert('crypto: encryption error '.$e->getMessage());
            throw new CryptographyBrokenException('Unable to encrypt data', 500, $e);
        }
    }

    /**
     * Decrypt a data
     *
     * @param string $data Encrypted data, base64 encoded
     *
     * @return string Decrypted data
     *
     * @throws CryptographyBrokenException
     */
    public function decrypt(string $data): string
    {
        try {
            return Crypto::decryptWithPassword(base64_decode($data), $this->keyphrase, true);
        } catch (EnvironmentIsBrokenException $e) {
            $this->logger->alert('crypto: decryption error '.$e->getMessage());
            throw new CryptographyBrokenException('Unable to encrypt data', 500, $e);
        } catch (WrongKeyOrModifiedCiphertextException $e) {
            $this->logger->notice('crypto: decryption error '.$e->getMessage());
            return '';
        }
    }
}
