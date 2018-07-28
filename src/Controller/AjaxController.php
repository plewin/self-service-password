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

namespace App\Controller;

use App\PasswordStrengthChecker\CheckerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function dictionaryCheckAction(Request $request): Response
    {
        if (!$this->getParameter('enable_as_you_type_policy_enforcement')) {
            throw $this->createAccessDeniedException();
        }

        $password = $request->get('password');

        /** @var CheckerInterface $dictionaryChecker */
        $dictionaryChecker = $this->get('password_strength_checker.dictionary');

        $result = $dictionaryChecker->evaluate($password);

        return $this->json(['found' => in_array('indictionary', $result)]);
    }

}
