<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Events\PasswordChangedEvent;
use App\Events\SshKeyChangedEvent;
use App\EventSubscriber\NotificationSubscriber;
use App\Service\MailNotificationService;

/**
 * Class NotificationSubscriberTest
 */
class NotificationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testNotifyOnPasswordChangedDisabled(): void
    {
        $mailNotificationService = $this
            ->getMockBuilder(MailNotificationService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mailNotificationService
            ->expects($this->exactly(0))
            ->method('send')
        ;

        /** @var MailNotificationService $mailNotificationService */
        $notificationSubscriber = new NotificationSubscriber(
            $mailNotificationService,
            false,
            true
        );

        $event = new PasswordChangedEvent('login', 'old_pass', 'new_pass', []);

        $notificationSubscriber->onPasswordChanged($event);
    }

    public function testNotifyOnSshKeyChangedDisabled(): void
    {
        $mailNotificationService = $this
            ->getMockBuilder(MailNotificationService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mailNotificationService
            ->expects($this->exactly(0))
            ->method('send')
        ;

        /** @var MailNotificationService $mailNotificationService */
        $notificationSubscriber = new NotificationSubscriber(
            $mailNotificationService,
            true,
            false
        );

        $event = new SshKeyChangedEvent('', '', []);

        $notificationSubscriber->onSshKeyChanged($event);
    }

    public function testNotifyOnPasswordChangedEnabled(): void
    {
        $mailNotificationService = $this
            ->getMockBuilder(MailNotificationService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mailNotificationService
            ->expects($this->once())
            ->method('send')
        ;

        /** @var MailNotificationService $mailNotificationService */
        $notificationSubscriber = new NotificationSubscriber(
            $mailNotificationService,
            true,
            false
        );

        $event = new PasswordChangedEvent(
            'login',
            'new_password',
            'old_password',
            [
                'user_mail' => 'user1@example.com'
            ]
        );

        $notificationSubscriber->onPasswordChanged($event);
    }

    public function testNotifyOnSshKeyChangedEnabled(): void
    {
        $mailNotificationService = $this
            ->getMockBuilder(MailNotificationService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mailNotificationService
            ->expects($this->once())
            ->method('send')
        ;

        /** @var MailNotificationService $mailNotificationService */
        $notificationSubscriber = new NotificationSubscriber(
            $mailNotificationService,
            false,
            true
        );

        $event = new SshKeyChangedEvent('login', 'edrtjiok', ['user_mail' => 'user1@example.com']);

        $notificationSubscriber->onSshKeyChanged($event);
    }
}

