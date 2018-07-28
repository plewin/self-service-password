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

namespace App\Service;

use App\Utils\MailSender;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Twig\Environment;
use App\Twig\AppExtension;

/**
 * Class MailNotificationService
 */
class MailNotificationService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var MailSender */
    private $mailSender;
    private $mailFromAddress;
    private $mailFromName;

    /** @var @var Environment */
    private $twig;

    /**
     * MailNotificationService constructor.
     *
     * @param Environment $twig
     * @param MailSender  $mailerSender
     * @param string      $mailFromAddress
     * @param string      $mailFromName
     */
    public function __construct(Environment $twig, MailSender $mailerSender, string $mailFromAddress, string $mailFromName)
    {
        $this->twig = $twig;
        $this->mailSender = $mailerSender;
        $this->mailFromAddress = $mailFromAddress;
        $this->mailFromName = $mailFromName;
    }

    /**
     * @param string $template Twig template name
     * @param array  $data
     *
     * @return bool
     */
    public function send(string $template, array $data): bool
    {
        $template = $this->twig->load($template.'.mail.twig');

        //ignore result, fills metadata
        $template->renderBlock('meta', $data);

        $bodyHtml = $template->renderBlock('body_html', $data);
        $bodyText = $template->renderBlock('body_text', $data);

        /** @var \App\Twig\AppExtension $extension */
        $extension = $this->twig->getExtension(AppExtension::class);

        $metadata = $extension->getMeta();

        $success = $this->mailSender->send(
            $this->array2addresses($metadata['to']),
            $this->array2addresses($metadata['from']),
            $metadata['subject'],
            $bodyText,
            $bodyHtml
        );

        if (!$success) {
            $this->logger->critical("Error while sending email notification to ${data['mail']} (user ${data['login']})");
        }

        return $success;
    }

    /**
     * @param array $e
     *
     * @return array
     */
    private function array2addresses(array $e): array
    {
        return [
            $e['address'] => $e['name'],
        ];
    }
}
