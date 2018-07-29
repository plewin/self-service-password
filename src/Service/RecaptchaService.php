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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RecaptchaService
 */
class RecaptchaService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string shared secret with reCAPTCHA server
     */
    private $privateKey;

    /**
     * @var null|string FQCN of request method, null for default
     */
    private $requestMethod;

    /**
     * RecaptchaService constructor.
     *
     * @param string $privateKey
     * @param string|RequestMethod $requestMethod
     */
    public function __construct(string $privateKey, $requestMethod)
    {
        $this->privateKey    = $privateKey;
        $this->requestMethod = $requestMethod;
    }

    /**
     * Check if $response verifies the reCAPTCHA by asking the recaptcha server, logs if errors
     *
     * @param Request $request request provided by user with "g-recaptcha-response"
     * @param string  $login   for logging purposes only
     *
     * @return string empty string if the response is verified successfully, else string 'badcaptcha'
     */
    public function verify(Request $request, string $login): string
    {
        $recaptcha = new ReCaptcha($this->privateKey, null === $this->requestMethod ? null : new $this->requestMethod());
        $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

        if (!$resp->isSuccess()) {
            $this->logger->notice("Bad reCAPTCHA attempt with user $login");
            foreach ($resp->getErrorCodes() as $code) {
                $this->logger->notice("reCAPTCHA error: $code");
            }

            return 'badcaptcha';
        }

        return '';
    }
}
