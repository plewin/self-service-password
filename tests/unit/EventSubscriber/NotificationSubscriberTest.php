<?php

namespace App\Tests\Unit\EventSubscriber;
use App\Events;
use App\EventSubscriber\NotificationSubscriber;
use Symfony\Component\EventDispatcher\GenericEvent;


/**
 * Class NotificationSubscriberTest
 */
class NotificationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testNotifyOnPasswordChangedDisabled()
    {
        $mailNotificationService = $this
            ->getMockBuilder('App\Service\MailNotificationService')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mailNotificationService
            ->expects($this->exactly(0))
            ->method('send')
        ;

        $translator = $this
            ->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $notificationSubscriber = new NotificationSubscriber(
            $mailNotificationService,
            $translator,
            'signature',
            false,
            true
        );

        $event = new GenericEvent(Events::PASSWORD_CHANGED, [/*not important*/]);

        $notificationSubscriber->onPasswordChanged($event);
    }

    public function testNotifyOnSshKeyChangedDisabled()
    {
        $mailNotificationService = $this
            ->getMockBuilder('App\Service\MailNotificationService')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mailNotificationService
            ->expects($this->exactly(0))
            ->method('send')
        ;

        $translator = $this
            ->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $notificationSubscriber = new NotificationSubscriber(
            $mailNotificationService,
            $translator,
            'signature',
            true,
            false
        );

        $event = new GenericEvent(Events::SSH_KEY_CHANGED, [/*not important*/]);

        $notificationSubscriber->onSshKeyChanged($event);
    }

    public function testNotifyOnPasswordChangedEnabled()
    {
        $mailNotificationService = $this
            ->getMockBuilder('App\Service\MailNotificationService')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mailNotificationService
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo('user1@example.com'), $this->equalTo('thesubject'), $this->equalTo('thebodysignature'))
        ;

        $translator = $this
            ->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $translator
            ->method('trans')
            ->withConsecutive(
                [$this->equalTo('changesubject')],
                [$this->equalTo('changemessage')]
            )
            ->willReturnOnConsecutiveCalls('thesubject', 'thebody')
        ;

        $notificationSubscriber = new NotificationSubscriber(
            $mailNotificationService,
            $translator,
            'signature',
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

    public function testNotifyOnSshKeyChangedEnabled()
    {
        $mailNotificationService = $this
            ->getMockBuilder('App\Service\MailNotificationService')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mailNotificationService
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo('user1@example.com'), $this->equalTo('thesubject'), $this->equalTo('thebodysignature'))
        ;

        $translator = $this
            ->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $translator
            ->method('trans')
            ->withConsecutive(
                [$this->equalTo('changesshkeysubject')],
                [$this->equalTo('changesshkeymessage')]
            )
            ->willReturnOnConsecutiveCalls('thesubject', 'thebody')
        ;

        $notificationSubscriber = new NotificationSubscriber(
            $mailNotificationService,
            $translator,
            'signature',
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

