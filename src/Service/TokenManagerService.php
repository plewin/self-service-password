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
use App\Exception\TokenExpiredException;
use App\Exception\TokenNotFoundException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class TokenManagerService
 */
class TokenManagerService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var SessionInterface */
    private $session;

    /**
     * @var EncryptionService
     */
    private $encryptionService;

    private $tokenLifetime;

    /**
     * TokenManagerService constructor.
     *
     * @param SessionInterface  $session
     * @param EncryptionService $encryptionService
     * @param int|null          $tokenLifetime
     */
    public function __construct(SessionInterface $session, $encryptionService, ?int $tokenLifetime)
    {
        $this->session           = $session;
        $this->encryptionService = $encryptionService;
        $this->tokenLifetime     = $tokenLifetime;
    }

    /**
     * @param string $login
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    public function createToken(string $login): string
    {
        $token = [
            'login' => $login,
            'time'  => time(),
        ];

        $this->session->start();
        $this->session->set('token', $token);

        $token = $this->encryptionService->encrypt($this->session->getId());

        return $token;
    }

    /**
     * @param string $token
     *
     * @return string
     *
     * @throws TokenExpiredException
     * @throws TokenNotFoundException
     * @throws CryptographyBrokenException
     */
    public function openToken(string $token): string
    {
        // Open session with the token
        $sessionId = $this->encryptionService->decrypt($token);

        $this->session->setId($sessionId);
        $this->session->start();

        if (!$this->session->has('token')) {
            $this->logger->notice("Unable to open session $sessionId");
            throw new TokenNotFoundException("Unable to open session $sessionId");
        }

        $token = $this->session->get('token');

        if ($this->tokenLifetime !== null) {
            $tokenTime = $token['time'];
            $tokenAgeInSeconds = time() - $tokenTime;
            if ($tokenAgeInSeconds > $this->tokenLifetime) {
                $this->logger->notice('Token lifetime expired');
                throw new TokenExpiredException('Token lifetime expired');
            }
        }

        return $token['login'];
    }

    /**
     * Destroy token from session
     */
    public function destroyToken(): void
    {
        $this->session->remove('token');
    }
}
