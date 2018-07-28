<?php

namespace App\Tests\Unit\Extra;

use ReCaptcha\RequestMethod;

/**
 * Class ReCaptchaRequestMethodsTest
 */
class ReCaptchaRequestMethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verifies that alternative request method is available (for $recaptcha_request_method in config)
     * @dataProvider requestMethodsProvider
     * @param $requestMethod string Request method FQCN
     */
    public function testRequestMethodAvailable($requestMethod): void
    {
        $this->assertInstanceOf(RequestMethod::class, new $requestMethod);
    }

    /**
     * @return array
     */
    public function requestMethodsProvider(): array
    {
        return [
            [RequestMethod\Post::class], // default reCAPTCHA request method
            [RequestMethod\SocketPost::class],
            [RequestMethod\CurlPost::class],
        ];
    }
}

