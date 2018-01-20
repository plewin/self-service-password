<?php

namespace App\Tests\Unit\Utils;

use App\Twig\AppExtension;

class AppExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testFilters()
    {
        $extension = new AppExtension('always', null);

        $filters = $extension->getFilters();
        $this->assertTrue(is_array($filters));
        // these filters must be available for all templates
        $this->assertPresent('fa_class', $filters);
        $this->assertPresent('criticality', $filters);
        $this->assertPresent('max_criticality', $filters);
    }

    public function testFunctions()
    {
        $extension = new AppExtension('always', null);

        $functions = $extension->getFunctions();
        $this->assertTrue(is_array($functions));
        $this->assertPresent('show_policy_for', $functions);
        // this function is normally provided by symfony form, so here our own
        $this->assertPresent('csrf_token', $functions);
    }

    public function testShowPolicyFor()
    {
        $extension = new AppExtension('always', null);

        $this->assertTrue($extension->showPolicyFor('anything'));
        $this->assertTrue($extension->showPolicyFor('forbiddenchars'));

        $extension = new AppExtension('onerror', null);

        $this->assertFalse($extension->showPolicyFor('anything'));
        $this->assertTrue($extension->showPolicyFor('forbiddenchars'));
    }

    public function assertPresent($filterName, array $filters)
    {
        $present = false;
        foreach ($filters as $filter) {
            if ($filter->getName() == $filterName) {
                $present = true;
            }
        }

        if ($present === false) {
            $this->fail("Filter $filterName missing");
        }
    }
}
