<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Events;
use App\EventSubscriber\NotificationSubscriber;
use App\Service\MailNotificationService;
use Symfony\Component\EventDispatcher\GenericEvent;

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

        $event = new GenericEvent(Events::PASSWORD_CHANGED, [/*not important*/]);

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

        $event = new GenericEvent(Events::SSH_KEY_CHANGED, [/*not important*/]);

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

        $event = new GenericEvent(Events::PASSWORD_CHANGED, [
            'login' => 'login',
            'new_password' => 'new_password',
            'old_password' => 'old_password',
            'context' => [
                'user_mail' => 'user1@example.com'
            ]
        ]);

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

        $event = new GenericEvent(Events::PASSWORD_CHANGED, [
            'login' => 'login',
            'ssh_key' => 'edrtjiok',
            'context' => [
                'user_mail' => 'user1@example.com'
            ]
        ]);

        $notificationSubscriber->onSshKeyChanged($event);
    }
}

