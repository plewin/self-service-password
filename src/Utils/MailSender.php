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

namespace App\Utils;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MailSender
 */
class MailSender implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var \Swift_Mailer */
    private $mailer;

    /**
     * MailSender constructor.
     *
     * @param \Swift_Mailer $mailer
     */
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send a mail, replace strings in body
     *
     * @param string|array $mail      Destination
     * @param array  $mailFrom        Sender
     * @param string $subject         Subject
     * @param string $body            Body Text
     * @param string|null $bodyHtml   Body Html
     *
     * @return bool
     */
    public function send($mail, array $mailFrom, $subject, $body, $bodyHtml = null)
    {
        $result = false;

        if (!$mail) {
            $this->logger->notice('send_mail: no mail given, exiting...');

            return $result;
        }

        $message = (new \Swift_Message($subject))
            ->setFrom($mailFrom)
            ->setTo($mail)
            ->setBody($body)
        ;

        if (null !== $bodyHtml) {
            $message->addPart($bodyHtml, 'text/html');
        }

        $nbDeliveries = $this->mailer->send($message);

        if ($nbDeliveries > 0) {
            $result = true;
        } else {
            $this->logger->critical("sendmail: error sending email");
        }

        return $result;
    }
}
