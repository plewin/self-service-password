<?php

namespace App\Tests\Service;

use App\Service\PasswordStrengthChecker;

class PasswordStrengthCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test check_password_strength function
     */
    public function testCheckPasswordStrength()
    {
        # Password policy
        $pwd_policy_config = array(
            "pwd_show_policy"         => true,
            "pwd_min_length"          => 6,
            "pwd_max_length"          => 12,
            "pwd_min_lower"           => 1,
            "pwd_min_upper"           => 1,
            "pwd_min_digit"           => 1,
            "pwd_min_special"         => 1,
            "pwd_special_chars"       => "^a-zA-Z0-9",
            "pwd_forbidden_chars"     => "@",
            "pwd_no_reuse"            => true,
            "pwd_diff_login"          => true,
            "pwd_complexity"          => 0
        );

        $login = "coudot";
        $oldpassword = "secret";

        $passwordChecker = new PasswordStrengthChecker($pwd_policy_config);

        $this->assertEquals("sameaslogin", $passwordChecker->evaluate( "coudot", $oldpassword, $login ) );
        $this->assertEquals("sameasold", $passwordChecker->evaluate( "secret", $oldpassword, $login ) );
        $this->assertEquals("forbiddenchars", $passwordChecker->evaluate( "p@ssword", $oldpassword, $login ) );
        $this->assertEquals("minspecial", $passwordChecker->evaluate( "password", $oldpassword, $login ) );
        $this->assertEquals("mindigit", $passwordChecker->evaluate( "!password", $oldpassword, $login ) );
        $this->assertEquals("minupper", $passwordChecker->evaluate( "!1password", $oldpassword, $login ) );
        $this->assertEquals("minlower", $passwordChecker->evaluate( "!1PASSWORD", $oldpassword, $login ) );
        $this->assertEquals("toobig", $passwordChecker->evaluate( "!1verylongPassword", $oldpassword, $login ) );
        $this->assertEquals("tooshort", $passwordChecker->evaluate( "!1Pa", $oldpassword, $login ) );

        $pwd_policy_config = array(
            "pwd_show_policy"         => true,
            "pwd_min_length"          => 6,
            "pwd_max_length"          => 12,
            "pwd_min_lower"           => 0,
            "pwd_min_upper"           => 0,
            "pwd_min_digit"           => 0,
            "pwd_min_special"         => 0,
            "pwd_special_chars"       => "^a-zA-Z0-9",
            "pwd_forbidden_chars"     => "@",
            "pwd_no_reuse"            => true,
            "pwd_diff_login"          => true,
            "pwd_complexity"          => 3
        );

        $passwordChecker = new PasswordStrengthChecker($pwd_policy_config);

        $this->assertEquals("notcomplex", $passwordChecker->evaluate( "simple", $oldpassword, $login ) );
        $this->assertEquals("", $passwordChecker->evaluate( "C0mplex", $oldpassword, $login ) );

    }
}

