<?php
namespace Page;

use AcceptanceTester;

class ResetBySms
{
    public static $URL = '/reset-password-by-sms';

    public static $loginField = 'login';

    public static $smsCodeField = 'smstoken';

    public static $findUserButton = 'Get user';
    public static $sendSmsButton = 'Send';
    public static $submitSmsCodeButton = 'Send';

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function findUser($login): void
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        $I->fillField(self::$loginField, $login);
        $I->click(self::$findUserButton);
    }

    public function sendSms(): void
    {
        $I = $this->tester;

        $I->click(self::$sendSmsButton);
    }

    public function submitSmsCode($code): void
    {
        $I = $this->tester;

        $I->fillField(self::$smsCodeField, $code);
        $I->click(self::$submitSmsCodeButton);
    }
}
