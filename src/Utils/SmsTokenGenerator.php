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
 * Class SmsTokenGenerator
 */
class SmsTokenGenerator
{
    private $smsTokenLength;

    /**
     * SmsTokenGenerator constructor.
     * @param int $smsTokenLength
     */
    public function __construct(int $smsTokenLength)
    {
        $this->smsTokenLength = $smsTokenLength;
    }

    /**
     * Generate SMS token
     *
     * @return string
     *
     * @throws CryptographyBrokenException
     */
    public function generateSmsCode(): string
    {
        try {
            $code = random_int(0, $this->smsTokenLength * 10 - 1);
        } catch (\Exception $e) {
            throw new CryptographyBrokenException('Could not generate sms code');
        }

        return str_pad($code, $this->smsTokenLength, '0', STR_PAD_LEFT);
    }
}
