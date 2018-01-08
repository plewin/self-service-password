<?php

namespace App\Tests\Unit\EventSubscriber;
use App\Events;
use App\EventSubscriber\PosthookSubscriber;
use Symfony\Component\EventDispatcher\GenericEvent;


/**
 * Class PosthookSubscriberTest
 */
class PosthookSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testPosthookSubscriberEnabled()
    {
        $mock = $this->getMock('App\Utils\PosthookExecutor', ['execute']);
        $mock
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('login'), $this->equalTo('new_password'), $this->equalTo('old_password'));
        ;

        $posthookSubscriber = new PosthookSubscriber(true, $mock);

        $event = new GenericEvent(Events::PASSWORD_CHANGED, [
            'login' => 'login',
            'new_password' => 'new_password',
            'old_password' => 'old_password',
        ]);

        $posthookSubscriber->onPasswordChanged($event);
    }

    public function testPosthookSubscriberDisabled()
    {
        $mock = $this->getMock('App\Utils\PosthookExecutor', ['execute']);
        $mock
            ->expects($this->exactly(0))
            ->method('execute')
        ;

        $posthookSubscriber = new PosthookSubscriber(false, $mock);

        $event = new GenericEvent(Events::PASSWORD_CHANGED, [
            'login' => 'login',
            'new_password' => 'new_password',
            'old_password' => 'old_password',
        ]);

        $posthookSubscriber->onPasswordChanged($event);
    }

}

