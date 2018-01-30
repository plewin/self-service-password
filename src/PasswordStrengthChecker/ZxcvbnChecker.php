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

namespace App\PasswordStrengthChecker;

use ZxcvbnPhp\Zxcvbn;

/**
 * Class ZxcvbnChecker
 */
class ZxcvbnChecker implements CheckerInterface
{
    private $minStrength;

    /**
     * ZxcvbnChecker constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->minStrength = $config['min_strength'];
    }

    /**
     * @param string      $newpassword
     * @param string|null $oldpassword
     * @param string|null $login
     *
     * @return string[]
     */
    public function evaluate($newpassword, $oldpassword = null, $login = null)
    {
        $violations = [];

        $zxcvbn = new Zxcvbn();

        $userData = [
            $oldpassword,
            $login,
        ];

        $strength = $zxcvbn->passwordStrength($newpassword, $userData);
        if ($strength['score'] < $this->minStrength) {
            $violations[] = 'notstrong';
        };

        return $violations;
    }
}
