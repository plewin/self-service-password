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
     * @param string      $newPassword
     * @param string|null $oldPassword
     * @param string|null $login
     *
     * @return string[]
     */
    public function evaluate(string $newPassword, ?string $oldPassword = null, ?string $login = null): array
    {
        $violations = [];

        $zxcvbn = new Zxcvbn();

        $userData = [
            $oldPassword,
            $login,
        ];

        $strength = $zxcvbn->passwordStrength($newPassword, $userData);
        if ($strength['score'] < $this->minStrength) {
            $violations[] = 'notstrong';
        }

        return $violations;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        $rules = [];

        if ($this->minStrength) {
            $rules['policyzxcvbnmin'] = [
                'onerror' => 'notstrong',
                'minStrength' => $this->minStrength,
            ];
        }

        return $rules;
    }
}
