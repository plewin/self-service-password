<?php
namespace Page;

use AcceptanceTester;

class ResetByEmail
{
    public static $URL = '/reset-password-by-email';

    public static $loginField = 'login';

    public static $mailField = 'mail';

    public static $sendButton = 'Send';

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function askResetEmail($login, $email)
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        $I->fillField(self::$loginField, $login);
        $I->fillField(self::$mailField, $email);
        $I->click(self::$sendButton);

        return $this;
    }
}
