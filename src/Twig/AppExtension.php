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

namespace App\Twig;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\GlobalsInterface;

/**
 * Class AppExtension
 */
class AppExtension extends \Twig_Extension implements GlobalsInterface
{
    /** @var string */
    private $pwd_show_policy;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var array */
    private $meta;

    /**
     * AppExtension constructor.
     *
     * @param string $pwd_show_policy
     * @param CsrfTokenManagerInterface $csrfTokenManager
     */
    public function __construct($pwd_show_policy, $csrfTokenManager)
    {
        $this->pwd_show_policy = $pwd_show_policy;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('fa_class', [$this, 'getFaClass']),
            new TwigFilter('criticality', [$this, 'getCriticality']),
            new TwigFilter('max_criticality', [$this, 'getMaxCriticality']),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('show_policy_for', [$this, 'showPolicyFor']),
            new TwigFunction('csrf_token', [$this, 'renderCsrfToken']),
            new TwigFunction('set_meta', [$this, 'setMeta']),
        ];
    }

    /**
     * Renders a CSRF token.
     *
     * @param string $tokenId The ID of the CSRF token
     *
     * @return string A CSRF token
     */
    public function renderCsrfToken($tokenId)
    {
        return $this->csrfTokenManager->getToken($tokenId)->getValue();
    }

    /**
     * @param string $result
     *
     * @return bool
     */
    public function showPolicyFor($result)
    {
        return ( $this->pwd_show_policy === 'always' or ( $this->pwd_show_policy === 'onerror' and $this->isError($result)));
    }

    /**
     * Get FontAwesome class icon
     *
     * @param string $msg
     *
     * @return string
     */
    public function getFaClass($msg)
    {
        $criticality = $this->getCriticality($msg);

        if ('danger' === $criticality) {
            return 'fa-exclamation-circle';
        }
        if ('warning' === $criticality) {
            return 'fa-exclamation-triangle';
        }
        if ('success' === $criticality) {
            return 'fa-check-square';
        }

        return '';
    }

    /**
     * Get message criticality
     *
     * @param $msg
     *
     * @return string
     */
    public function getCriticality($msg)
    {
        $dangerList = [
            'nophpldap',
            'phpupgraderequired',
            'nokeyphrase',
            'ldaperror',
            'nophpmhash',
            'nokeyphrase',
            'nomatch',
            'badcredentials',
            'passworderror',
            'tooshort',
            'toobig',
            'minlower',
            'minupper',
            'mindigit',
            'minspecial',
            'forbiddenchars',
            'sameasold',
            'answermoderror',
            'answernomatch',
            'mailnomatch',
            'tokennotsent',
            'tokennotvalid',
            'notcomplex',
            'smsnonumber',
            'nophpmbstring',
            'nophpxml',
            'smsnotsent',
            'sameaslogin',
            'sshkeyerror',
            'badcaptcha',
        ];

        if (in_array($msg, $dangerList)) {
            return 'danger';
        }

        $warningList = [
            'loginrequired',
            'oldpasswordrequired',
            'newpasswordrequired',
            'confirmpasswordrequired',
            'answerrequired',
            'questionreqyured',
            'passwordrequired',
            'mailrequired',
            'tokenrequired',
            'captcharequired',
            'sshkeyrequired',
        ];

        if (in_array($msg, $warningList)) {
            return 'warning';
        }

        return 'success';
    }

    public function getMaxCriticality(array $msgs)
    {
        $maxCriticality = 'success';

        foreach ($msgs as $msg) {
            $criticality = $this->getCriticality($msg);
            if ('danger' === $criticality) {
                return 'danger';
            }

            if ('warning' === $criticality) {
                $maxCriticality = 'warning';
            }
        }

        return $maxCriticality;
    }

    /**
     * @param string $msg
     *
     * @return bool
     */
    private function isError($msg)
    {
        $errorList = [
            'tooshort',
            'toobig',
            'minlower',
            'minupper',
            'mindigit',
            'minspecial',
            'forbiddenchars',
            'sameasold',
            'notcomplex',
            'sameaslogin',
        ];

        return in_array($msg, $errorList);
    }

    public function setMeta(array $meta)
    {
        $this->meta = $meta;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    /*
    public function getGlobals()
    {
        return [
         'show_change_help_reset' => !$conf['show_menu'] and ( $conf['use_questions'] or $conf['use_tokens'] or $conf['use_sms'] or $conf['change_sshkey'] ),
        ];
    }*/
}