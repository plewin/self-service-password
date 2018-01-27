<?php

namespace App\Tests\Unit\Service;
use App\Service\SmsNotificationService;
use Psr\Log\NullLogger;

/**
 * Class SmsNotificationServiceTest
 */
class SmsNotificationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSmsNotificationByApi()
    {
        $mock = $this
            ->getMockBuilder('App\\Utils\\MailSender')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mock
            ->expects($this->never())
            ->method('send')
        ;

        $smsNotificationService = new SmsNotificationService(
            'api',
            $mock,
            'mailto@example.org',
            'mailfrom@example.org',
            'MailFromName',
            __DIR__ . '/../../../config/smsapi-example.inc.php'
        );

        $smsNotificationService->setLogger(new NullLogger());

        $sms = '0612345678';
        $login = 'user1';
        $smsMailSubject = 'sms mail subject';
        $smsMessage = '{smsresetmessage} {smstoken}';
        $smsCode = '1234';
        $data = [
            'smsresetmessage' => 'thesmsresetmessage',
            'smstoken' => $smsCode,
        ];


        $this->assertSame('smssent', $smsNotificationService->send($sms, $login, $smsMailSubject, $smsMessage, $data, $smsCode));
    }

}
