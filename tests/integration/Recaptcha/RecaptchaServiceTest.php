<?php

namespace App\Tests\Integration\Recaptcha;

use App\Service\EncryptionService;
use App\Service\RecaptchaService;
use Psr\Log\NullLogger;
use ReCaptcha\RequestMethod\Post;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RecaptchaServiceTest
 */
class RecaptchaServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testRecaptchaService()
    {
        // this test key return always valid from recaptcha
        $privateKey = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe";
        $recaptchaService = new RecaptchaService($privateKey, new Post());
        $recaptchaService->setLogger(new NullLogger());

        $request = new Request([], ['g-recaptcha-response' => 'alwaysvalid']);

        $this->assertSame('', $recaptchaService->verify($request, 'login'));
    }
}

