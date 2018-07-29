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

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class DictionaryChecker
 */
class DictionaryChecker implements CheckerInterface
{
    private $dirs;
    private $enable;
    private $requestStack;
    private $router;

    /**
     * DictionaryChecker constructor.
     *
     * @param array $config Config array, $config['dirs'] list of directories where SSP can find txt files
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     */
    public function __construct(array $config, RequestStack $requestStack, RouterInterface $router)
    {
        $this->enable       = $config['enable'];
        $this->dirs         = $config['dirs'];
        $this->requestStack = $requestStack;
        $this->router       = $router;
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
        if (!$this->enable){
            return [];
        }

        $pattern = escapeshellarg('^'.preg_quote($newPassword).'$');

        $finder = new Finder();
        $finder->files()->in($this->dirs);

        foreach ($finder as $file) {
            $filepath = escapeshellarg($file->getRealPath());

            if (0 === stripos(PHP_OS, 'WIN')) {
                // Use findstr on Windows. It is equivalent to grep.
                // https://technet.microsoft.com/en-us/library/bb490907.aspx
                $command = "findstr /r /c:$pattern $filepath";
            } else {
                $command = "grep -q $pattern $filepath";
            }

            $output = '';
            $returnVar = null;
            exec($command, $output, $returnVar);

            if (0 === $returnVar) {
                // string found
                return ['indictionary'];
            }
            // else command has failed or password not found
        }

        return [];
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        if (!$this->enable) {
            return [];
        }

        $context = new RequestContext();
        $context->fromRequest($this->requestStack->getCurrentRequest());
        $this->router->setContext($context);
        $apiUrl = $this->router->generate('api-dictionary-check');

        return [
            'policynotindictionary' => [
                'onerror' => 'indictionary',
                'apiUrl'  => $apiUrl,
            ],
        ];
    }
}
