<?php

namespace App\Tests\Unit\PasswordStrengthChecker;

use App\PasswordStrengthChecker\DictionaryChecker;

/**
 * Class DictionaryCheckerTest
 */
class DictionaryCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testCheckPasswordStrength()
    {
        $checker = new DictionaryChecker(['dirs' => [__DIR__.'/../../../var/dictionaries']]);

        $this->assertSame(['indictionary'], $checker->evaluate("123456"));
        $this->assertSame([], $checker->evaluate("P455w0rd"));
    }
}

