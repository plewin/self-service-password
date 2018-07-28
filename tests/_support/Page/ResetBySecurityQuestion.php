<?php
namespace Page;

use AcceptanceTester;

class ResetBySecurityQuestion
{
    public static $URL = '/reset-password-by-question';

    public static $loginField = 'login';

    public static $questionField = 'Question';

    public static $answerField = 'answer';

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

    public function resetPassword(?string $login, ?string $question, ?string $answer, ?string $newpassword, ?string $confirmpassword): void
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        $I->fillField(self::$loginField, $login);
        $I->selectOption(self::$questionField, $question);
        $I->fillField(self::$answerField, $answer);
        $I->fillField(self::$newPasswordField, $newpassword);
        $I->fillField(self::$confirmPasswordField, $confirmpassword);
        $I->click(self::$sendButton);
    }
}
