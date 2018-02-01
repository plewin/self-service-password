<?php

namespace App\Tests\Unit\Service;

use App\Service\PosthookExecutor;

/**
 * Class PosthookExecutorTest
 */
class PosthookExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function testEchoPosthookLinux()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Skip posthook test for linux because we are not on linux');
        }

        $posthookExecutor = new PosthookExecutor('echo');

        // without current password
        $result = $posthookExecutor->execute('login', 'newpassword');

        $this->assertSame(0, $result['return_var']);
        $this->assertSame('login newpassword', $result['output'][0]);

        // with current password
        $result = $posthookExecutor->execute('login', 'newpassword', 'oldpassword');

        $this->assertSame(0, $result['return_var']);
        $this->assertSame('login newpassword oldpassword', $result['output'][0]);
    }

    public function testEchoPosthookWindows()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $this->markTestSkipped('Skip posthook test for windows because we are not on windows');
        }

        $posthookExecutor = new PosthookExecutor('echo');

        // without current password
        $result = $posthookExecutor->execute('login', 'newpassword');

        $this->assertSame(0, $result['return_var']);
        $this->assertSame('"login" "newpassword"', $result['output'][0]);

        // with current password
        $result = $posthookExecutor->execute('login', 'newpassword', 'oldpassword');

        $this->assertSame(0, $result['return_var']);
        $this->assertSame('"login" "newpassword" "oldpassword"', $result['output'][0]);
    }

}
