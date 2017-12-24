<?php
/*
 * LTB Self Service Password
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

use App\Service\RecaptchaService;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait CaptchaTrait
 */
trait CaptchaTrait
{
    /**
     * @return bool
     */
    protected function isCaptchaEnabled()
    {
        return $this->getParameter('enable_captcha');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isCaptchaSubmitted(Request $request)
    {
        $captcha = $request->request->get('captcha', '');
        return !empty($captcha);
    }

    /**
     * @param Request $request
     * @param $login
     *
     * @return bool
     */
    protected function verifyCaptcha(Request $request, $login)
    {
        switch ($this->getParameter('captcha_type')) {
            case 'recaptcha':
                $recaptchaService = $this->get('recaptcha_service');

                $result = $recaptchaService->verify($request->request->get('g-recaptcha-response'), $login);
                if ('' !== $result) {
                    return false;
                }
                break;
            case 'gregwar':
                /** @var RecaptchaService $recaptchaService */
                $session = $request->getSession();
                $expected = $session->get('captcha');

                if (!hash_equals($expected, $request->request->get('captcha'))) {
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * @param $request
     *
     * @return array
     */
    protected function getCaptchaTemplateExtraVars($request)
    {
        if (!$this->isCaptchaEnabled()) {
            return [];
        }

        $extra = [];

        if ($this->getParameter('captcha_type') == 'gregwar') {
            $extra['captcha_image'] = $this->generateCaptchaImage($request);
        }

        return $extra;
    }


    /**
     * @param Request $request
     *
     * @return string
     */
    private function generateCaptchaImage(Request $request)
    {
        $builder = new CaptchaBuilder();
        $builder->build();
        $inline = $builder->inline();
        $phrase = $builder->getPhrase();
        $session = $request->getSession();
        $session->set('captcha', $phrase);
        return $inline;
    }
}
