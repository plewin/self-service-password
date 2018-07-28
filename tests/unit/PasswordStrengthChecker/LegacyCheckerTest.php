<?php

namespace App\Tests\Unit\PasswordStrengthChecker;

use App\PasswordStrengthChecker\LegacyChecker;

/**
 * Class LegacyCheckerTest
 */
class LegacyCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test check_password_strength function
     */
    public function testCheckPasswordStrength(): void
    {
        // Password policy
        $pwd_policy_config = [
            'pwd_show_policy'     => true,
            'pwd_min_length'      => 6,
            'pwd_max_length'      => 12,
            'pwd_min_lower'       => 1,
            'pwd_min_upper'       => 1,
            'pwd_min_digit'       => 1,
            'pwd_min_special'     => 1,
            'pwd_special_chars'   => '^a-zA-Z0-9',
            'pwd_forbidden_chars' => '@',
            'pwd_no_reuse'        => true,
            'pwd_diff_login'      => true,
            'pwd_complexity'      => 0,
        ];

        $login = 'coudot';
        $oldpassword = 'secret';

        $passwordChecker = new LegacyChecker($pwd_policy_config);

        $this->assertContains('sameaslogin', $passwordChecker->evaluate('coudot', $oldpassword, $login));
        $this->assertContains('sameasold', $passwordChecker->evaluate('secret', $oldpassword, $login));
        $this->assertContains('forbiddenchars', $passwordChecker->evaluate('p@ssword', $oldpassword, $login));
        $this->assertContains('minspecial', $passwordChecker->evaluate('password', $oldpassword, $login));
        $this->assertContains('mindigit', $passwordChecker->evaluate('!password', $oldpassword, $login));
        $this->assertContains('minupper', $passwordChecker->evaluate('!1password', $oldpassword, $login));
        $this->assertContains('minlower', $passwordChecker->evaluate('!1PASSWORD', $oldpassword, $login));
        $this->assertContains('toobig', $passwordChecker->evaluate('!1verylongPassword', $oldpassword, $login));
        $this->assertContains('tooshort', $passwordChecker->evaluate('!1Pa', $oldpassword, $login));

        $pwd_policy_config = [
            'pwd_show_policy'     => true,
            'pwd_min_length'      => 6,
            'pwd_max_length'      => 12,
            'pwd_min_lower'       => 0,
            'pwd_min_upper'       => 0,
            'pwd_min_digit'       => 0,
            'pwd_min_special'     => 0,
            'pwd_special_chars'   => '^a-zA-Z0-9',
            'pwd_forbidden_chars' => '@',
            'pwd_no_reuse'        => true,
            'pwd_diff_login'      => true,
            'pwd_complexity'      => 3
        ];

        $passwordChecker = new LegacyChecker($pwd_policy_config);

        $this->assertContains('notcomplex', $passwordChecker->evaluate('simple', $oldpassword, $login));
        $this->assertEquals([], $passwordChecker->evaluate( 'C0mplex', $oldpassword, $login ) );

    }

    public function testForbiddenChars(): void
    {
        $pwd_policy_config = [
            'pwd_show_policy'      => true,
            'pwd_min_length'      => 6,
            'pwd_max_length'      => 12,
            'pwd_min_lower'       => 0,
            'pwd_min_upper'       => 0,
            'pwd_min_digit'       => 0,
            'pwd_min_special'     => 0,
            'pwd_special_chars'   => '^a-zA-Z0-9',
            'pwd_forbidden_chars' => '@',
            'pwd_no_reuse'        => true,
            'pwd_diff_login'      => true,
            'pwd_complexity'      => 3
        ];

        $passwordChecker = new LegacyChecker($pwd_policy_config);

        $this->assertContains('forbiddenchars', $passwordChecker->evaluate('p@sword'));
        $this->assertNotContains('forbiddenchars', $passwordChecker->evaluate('pa$$word'));
    }
}

