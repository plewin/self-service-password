<?php

namespace App\Tests\Functional;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class FunctionalTestCase
 */
abstract class FunctionalTestCase extends WebTestCase {

    /**
     * Returns the kernel location
     *
     * @return string The Kernel class name
     */
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}