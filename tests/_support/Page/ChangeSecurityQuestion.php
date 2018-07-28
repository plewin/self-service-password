<?php
namespace Page;

use AcceptanceTester;

class ChangeSecurityQuestion
{
    public static $URL = '/change-security-question';

    public static $loginField = 'login';

    public static $passwordField = 'password';

    public static $questionField = 'Question';

    public static $answerField = 'answer';

    public static $sendButton = 'Send';

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function changePassword(?string $login, ?string $password, ?string $question, ?string $answer): void
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        $I->fillField(self::$loginField, $login);
        $I->fillField(self::$passwordField, $password);
        $I->selectOption(self::$questionField,$question);
        $I->fillField(self::$answerField, $answer);
        $I->click(self::$sendButton);
    }
}
