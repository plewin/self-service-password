<?php

namespace App\Tests\Unit\Service;

use App\Service\EncryptionService;
use Psr\Log\NullLogger;

/**
 * Class EncryptionServiceTest
 */
class EncryptionServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test encrypt and decrypt functions
     */
    public function testEncryptionAndDecryption(): void
    {
        // first encrypt use case : phone number + login
        $plaintext1 = '+33123456789:ldap_username';

        // second encrypt use case : session_id
        $plaintext2 = 'azAZ09,-';

        // secret from config
        $passphrase = 'secret';

        $encryptionService = new EncryptionService($passphrase);
        $encryptionService->setLogger(new NullLogger());

        $encrypted1 = $encryptionService->encrypt($plaintext1);
        $decrypted1 = $encryptionService->decrypt($encrypted1);

        $this->assertNotEquals($plaintext1, $encrypted1);
        $this->assertEquals($plaintext1, $decrypted1);

        $encrypted2 = $encryptionService->encrypt($plaintext2);
        $decrypted2 = $encryptionService->decrypt($encrypted2);

        $this->assertNotEquals($plaintext2, $encrypted2);
        $this->assertEquals($plaintext2, $decrypted2);
    }

    /**
     * Test decrypt gives empty string if token is invalid
     */
    public function testDecryptionWithIncorrectTokenGivesEmptyString(): void
    {
        // second encrypt use case : session_id
        $plaintext1 = 'azAZ09,-';
        $passphrase = 'secret';

        $mockLogger = $this
            ->getMockBuilder(NullLogger::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mockLogger
            ->expects($this->once())
            ->method('notice')
        ;

        $encryptionService = new EncryptionService($passphrase);
        /** @noinspection PhpParamsInspection */
        $encryptionService->setLogger($mockLogger);

        $token = $encryptionService->encrypt($plaintext1);

        // corrupted token, badly copy pasted
        // base64 has 0, 1 or 2 "=" padding, it does not affect decryption
        $token = substr($token, 0, -3);

        $decrypted = $encryptionService->decrypt($token);
        $this->assertEquals('', $decrypted);
    }
}

