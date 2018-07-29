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

namespace App\EventSubscriber;

use App\Events;
use App\Service\MailNotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class NotificationSubscriber
 */
class NotificationSubscriber implements EventSubscriberInterface
{
    /** @var MailNotificationService */
    private $mailNotificationService;

    private $notifyOnPasswordChanged;

    private $notifyOnSshKeyChanged;

    /**
     * NotificationSubscriber constructor.
     *
     * @param MailNotificationService $mailNotificationService
     * @param bool                    $notifyOnPasswordChanged
     * @param bool                    $notifyOnSshKeyChanged
     */
    public function __construct(MailNotificationService $mailNotificationService, bool $notifyOnPasswordChanged, bool $notifyOnSshKeyChanged)
    {
        $this->mailNotificationService = $mailNotificationService;
        $this->notifyOnPasswordChanged = $notifyOnPasswordChanged;
        $this->notifyOnSshKeyChanged   = $notifyOnSshKeyChanged;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::PASSWORD_CHANGED => 'onPasswordChanged',
            Events::SSH_KEY_CHANGED  => 'onSshKeyChanged',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function onPasswordChanged(GenericEvent $event): void
    {
        if (!$this->notifyOnPasswordChanged) {
            return;
        }

        $context = $event['context'];

        if (empty($context['user_mail'])) {
            // TODO log when missing email
            return;
        }

        $data = [
            'login'    => $event['login'],
            'mail'     => $context['user_mail'],
            'password' => $event['new_password'],
            'context'  => $context,
        ];

        $this->mailNotificationService->send('mail/user-password-changed', $data);
    }

    /**
     * @param GenericEvent $event
     */
    public function onSshKeyChanged(GenericEvent $event): void
    {
        if (!$this->notifyOnSshKeyChanged) {
            return;
        }

        $context = $event['context'];

        if (empty($context['user_mail'])) {
            // TODO log when missing email
            return;
        }

        $data = [
            'login'   => $event['login'],
            'mail'    => $context['user_mail'],
            'sshkey'  => $event['ssh_key'],
            'context' => $context,
        ];

        $this->mailNotificationService->send('mail/user-ssh-key-changed', $data);
    }
}
