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
    protected function isCaptchaEnabled(): bool
    {
        return $this->getParameter('enable_captcha');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isCaptchaSubmitted(Request $request): bool
    {
        $submitted = false;

        switch ($this->getParameter('captcha_type')) {
            case 'recaptcha':
                $captcha = $request->request->get('g-recaptcha-response', '');
                $submitted = !empty($captcha);
                break;
            case 'gregwar':
                $captcha = $request->request->get('captcha', '');
                $submitted = !empty($captcha);
                break;
        }

        return $submitted;
    }

    /**
     * @param Request $request
     * @param string  $login
     *
     * @return bool
     */
    protected function verifyCaptcha(Request $request, $login): bool
    {
        $isCaptchaValid = false;
        switch ($this->getParameter('captcha_type')) {
            case 'recaptcha':
                /** @var RecaptchaService $recaptchaService */
                $recaptchaService = $this->get('recaptcha_service');

                $result = $recaptchaService->verify($request, $login);
                if ('' === $result) {
                    $isCaptchaValid = true;
                }
                break;
            case 'gregwar':
                $session = $request->getSession();
                /** @noinspection NullPointerExceptionInspection */
                $expected = $session->get('captcha');

                if (hash_equals($expected, $request->request->get('captcha'))) {
                    $isCaptchaValid = true;
                }
                break;
        }

        return $isCaptchaValid;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getCaptchaTemplateExtraVars(Request $request): array
    {
        if (!$this->isCaptchaEnabled()) {
            return [];
        }

        $extra = [];

        if ($this->getParameter('captcha_type') === 'gregwar') {
            $extra['captcha_image'] = $this->generateCaptchaImage($request);
        }

        return $extra;
    }

    /**
     * @param Request $request
     *
     * @return string HTML inline base64
     */
    private function generateCaptchaImage(Request $request): string
    {
        $builder = new CaptchaBuilder();
        $builder->build();
        $phrase = $builder->getPhrase();
        $session = $request->getSession();
        /** @noinspection NullPointerExceptionInspection */
        $session->set('captcha', $phrase);

        return $builder->inline();
    }
}
