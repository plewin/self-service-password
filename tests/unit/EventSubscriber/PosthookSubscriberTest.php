<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Events\PasswordChangedEvent;
use App\EventSubscriber\PosthookSubscriber;
use App\Service\PosthookExecutor;

/**
 * Class PosthookSubscriberTest
 */
class PosthookSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testPosthookSubscriberEnabled(): void
    {
        $mock = $this->getMockBuilder(PosthookExecutor::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mock
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('login'), $this->equalTo('new_password'), $this->equalTo('old_password'));
        ;

        /** @var PosthookExecutor $mock */

        $posthookSubscriber = new PosthookSubscriber(true, $mock);

        $event = new PasswordChangedEvent(
            'login',
            'old_password',
            'new_password',
            []
        );

        $posthookSubscriber->onPasswordChanged($event);
    }

    public function testPosthookSubscriberDisabled(): void
    {
        $mock = $this->getMockBuilder(PosthookExecutor::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mock
            ->expects($this->exactly(0))
            ->method('execute')
        ;

        /** @var PosthookExecutor $mock */

        $posthookSubscriber = new PosthookSubscriber(false, $mock);

        $event = new PasswordChangedEvent(
            'login',
            'old_password',
            'new_password',
            []
        );

        $posthookSubscriber->onPasswordChanged($event);
    }

}

