<?php
namespace Page;

use AcceptanceTester;

class ResetPassword
{
    public static $URL = '/reset-password-with-token';

    public static $newPasswordField = 'newpassword';

    public static $confirmPasswordField = 'confirmpassword';

    public static $sendButton = 'Send';

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function resetPassword($newPassword, $confirmPassword)
    {
        $I = $this->tester;

        //$I->amOnPage(self::$URL);
        $I->fillField(self::$newPasswordField, $newPassword);
        $I->fillField(self::$confirmPasswordField, $confirmPassword);
        $I->click(self::$sendButton);

        return $this;
    }
}
