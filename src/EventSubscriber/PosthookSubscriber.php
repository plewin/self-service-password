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

use App\Events\PasswordChangedEvent;
use App\Service\PosthookExecutor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class PosthookSubscriber
 */
class PosthookSubscriber implements EventSubscriberInterface
{
    /** @var bool */
    private $posthookEnabled;

    /** @var PosthookExecutor */
    private $posthookExecutor;

    /**
     * PosthookSubscriber constructor.
     *
     * @param bool             $posthookEnabled
     * @param PosthookExecutor $posthookExecutor
     */
    public function __construct(bool $posthookEnabled, $posthookExecutor)
    {
        $this->posthookEnabled  = $posthookEnabled;
        $this->posthookExecutor = $posthookExecutor;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PasswordChangedEvent::class => 'onPasswordChanged',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function onPasswordChanged(GenericEvent $event): void
    {
        if (!$this->posthookEnabled) {
            return;
        }

        $this->posthookExecutor->execute($event['login'], $event['new_password'], $event['old_password']);
    }
}
