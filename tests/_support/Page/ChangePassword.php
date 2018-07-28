<?php
namespace Page;

use AcceptanceTester;

class ChangePassword
{
    public static $URL = '/change-password';

    public static $loginField = 'login';

    public static $oldPasswordField = 'oldpassword';

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

    public function changePassword(?string $login, ?string $oldPassword, ?string $newPassword, ?string $confirmPassword): void
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        $I->fillField(self::$loginField, $login);
        $I->fillField(self::$oldPasswordField, $oldPassword);
        $I->fillField(self::$newPasswordField, $newPassword);
        $I->fillField(self::$confirmPasswordField, $confirmPassword);
        $I->click(self::$sendButton);
    }
}
