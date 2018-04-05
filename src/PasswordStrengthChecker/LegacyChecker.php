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

/**
 * Class LegacyChecker
 */
class LegacyChecker implements CheckerInterface
{
    private $pwdPolicyConfig;

    /**
     * PasswordStrengthChecker constructor.
     *
     * @param array $pwdPolicyConfig
     */
    public function __construct(array $pwdPolicyConfig)
    {
        $defaults = [
            'pwd_min_length'      => 0,
            'pwd_max_length'      => 0,
            'pwd_min_lower'       => 0,
            'pwd_min_upper'       => 0,
            'pwd_min_digit'       => 0,
            'pwd_min_special'     => 0,
            'pwd_special_chars'   => '^a-zA-Z0-9',
            'pwd_forbidden_chars' => '%@',
            'pwd_no_reuse'        => true,
            'pwd_diff_login'      => true,
            'pwd_complexity'      => 0,
        ];

        $this->pwdPolicyConfig = array_merge($defaults, $pwdPolicyConfig);
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

        //TODO hum... why utf8 decode ?
        $length = strlen(utf8_decode($newpassword));
        preg_match_all("/[a-z]/", $newpassword, $lowerRes);
        $lower = count($lowerRes[0]);
        preg_match_all("/[A-Z]/", $newpassword, $upperRes);
        $upper = count($upperRes[0]);
        preg_match_all("/[0-9]/", $newpassword, $digitRes);
        $digit = count($digitRes[0]);

        $special = 0;
        if (!empty($this->pwdPolicyConfig['pwd_special_chars'])) {
            $specialChars = $this->pwdPolicyConfig['pwd_special_chars'];
            preg_match_all("/[$specialChars]/", $newpassword, $specialRes);
            $special = count($specialRes[0]);
        }

        $forbidden = 0;
        if (!empty($this->pwdPolicyConfig['pwd_forbidden_chars'])) {
            $forbiddenChars = $this->pwdPolicyConfig['pwd_forbidden_chars'];
            preg_match_all("/[$forbiddenChars]/", $newpassword, $forbiddenRes);
            $forbidden = count($forbiddenRes[0]);
        }

        // Complexity: checks for lower, upper, special, digits
        if ($this->pwdPolicyConfig['pwd_complexity']) {
            $complex = 0;
            if ($special > 0) {
                ++$complex;
            }
            if ($digit > 0) {
                ++$complex;
            }
            if ($lower > 0) {
                ++$complex;
            }
            if ($upper > 0) {
                ++$complex;
            }
            if ($complex < $this->pwdPolicyConfig['pwd_complexity']) {
                $violations[] = 'notcomplex';
            }
        }

        // Minimal lenght
        if ($this->pwdPolicyConfig['pwd_min_length'] and $length < $this->pwdPolicyConfig['pwd_min_length']) {
            $violations[] = 'tooshort';
        }

        // Maximal lenght
        if ($this->pwdPolicyConfig['pwd_max_length'] and $length > $this->pwdPolicyConfig['pwd_max_length']) {
            $violations[] = 'toobig';
        }

        // Minimal lower chars
        if ($this->pwdPolicyConfig['pwd_min_lower'] and $lower < $this->pwdPolicyConfig['pwd_min_lower']) {
            $violations[] = 'minlower';
        }

        // Minimal upper chars
        if ($this->pwdPolicyConfig['pwd_min_upper'] and $upper < $this->pwdPolicyConfig['pwd_min_upper']) {
            $violations[] = 'minupper';
        }

        // Minimal digit chars
        if ($this->pwdPolicyConfig['pwd_min_digit'] and $digit < $this->pwdPolicyConfig['pwd_min_digit']) {
            $violations[] = 'mindigit';
        }

        // Minimal special chars
        if ($this->pwdPolicyConfig['pwd_min_special'] and $special < $this->pwdPolicyConfig['pwd_min_special']) {
            $violations[] = 'minspecial';
        }

        // Forbidden chars
        if ($forbidden > 0) {
            $violations[] = 'forbiddenchars';
        }

        // Same as current password?
        if ($this->pwdPolicyConfig['pwd_no_reuse'] and $newpassword === $oldpassword) {
            $violations[] = 'sameasold';
        }

        // Same as login?
        if ($this->pwdPolicyConfig['pwd_diff_login'] and $newpassword === $login) {
            $violations[] = 'sameaslogin';
        }

        return $violations;
    }
}
