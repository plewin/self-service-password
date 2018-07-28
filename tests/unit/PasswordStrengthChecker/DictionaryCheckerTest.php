<?php

namespace App\Tests\Unit\PasswordStrengthChecker;

use App\PasswordStrengthChecker\DictionaryChecker;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class DictionaryCheckerTest
 */
class DictionaryCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testCheckPasswordStrength(): void
    {
        $mockRouter = $this
            ->getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $requestStack = new RequestStack();

        $checker = new DictionaryChecker([
            'dirs' => [__DIR__.'/../../../var/dictionaries'],
            'enable' => true,
        ], $requestStack, $mockRouter);

        $this->assertSame(['indictionary'], $checker->evaluate('123456'));
        $this->assertSame([], $checker->evaluate('P455w0rd'));
    }
}

