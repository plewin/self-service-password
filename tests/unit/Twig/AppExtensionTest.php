<?php

namespace App\Tests\Unit\Utils;

use App\Twig\AppExtension;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AppExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testFilters(): void
    {
        $extension = new AppExtension('always', $this->createMockCsrfTokenManager());

        $filters = $extension->getFilters();
        $this->assertInternalType('array', $filters);
        // these filters must be available for all templates
        $this->assertPresent('fa_class', $filters);
        $this->assertPresent('criticality', $filters);
        $this->assertPresent('max_criticality', $filters);
    }

    public function testFunctions(): void
    {
        $extension = new AppExtension('always', $this->createMockCsrfTokenManager());

        $functions = $extension->getFunctions();
        $this->assertInternalType('array', $functions);
        $this->assertPresent('show_policy_for', $functions);
        // this function is normally provided by symfony form, so here our own
        $this->assertPresent('csrf_token', $functions);
    }

    public function testShowPolicyFor(): void
    {
        $extension = new AppExtension('always', $this->createMockCsrfTokenManager());

        $this->assertTrue($extension->showPolicyFor('anything'));
        $this->assertTrue($extension->showPolicyFor('forbiddenchars'));

        $extension = new AppExtension('onerror', $this->createMockCsrfTokenManager());

        $this->assertFalse($extension->showPolicyFor('anything'));
        $this->assertTrue($extension->showPolicyFor('forbiddenchars'));
    }

    public function assertPresent($filterName, array $filters): void
    {
        $present = false;
        foreach ($filters as $filter) {
            if ($filter->getName() === $filterName) {
                $present = true;
            }
        }

        if ($present === false) {
            $this->fail("Filter $filterName missing");
        }
    }

    /**
     * @return CsrfTokenManagerInterface
     */
    private function createMockCsrfTokenManager(): CsrfTokenManagerInterface
    {
        /** @var CsrfTokenManagerInterface $csrfTokenManger */
        $csrfTokenManger = $this->getMockBuilder(CsrfTokenManagerInterface::class)->getMock();

        return $csrfTokenManger;
    }
}
