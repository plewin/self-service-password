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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class MultiChecker
 */
class MultiChecker implements CheckerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var CheckerInterface[] */
    private $checkers = [];

    /**
     * @param CheckerInterface $checker
     */
    public function addChecker(CheckerInterface $checker)
    {
        $this->checkers[] = $checker;
    }

    /**
     * @param string      $newpassword
     * @param string|null $oldpassword
     * @param string|null $login
     *
     * @return string[]
     */
    public function evaluate(string $newpassword, ?string $oldpassword = null, ?string $login = null): array
    {
        $violations = [];

        foreach ($this->checkers as $checker) {
            $violations += $checker->evaluate($newpassword, $oldpassword, $login);
        }

        return $violations;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        $rules = [];

        foreach ($this->checkers as $checker) {
            $rules += $checker->getRules();
        }

        return $rules;
    }
}
