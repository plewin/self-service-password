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

    public function findUser($login)
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        $I->fillField(self::$loginField, $login);
        $I->click(self::$findUserButton);

        return $this;
    }

    public function sendSms()
    {
        $I = $this->tester;

        $I->click(self::$sendSmsButton);

        return $this;
    }

    public function submitSmsCode($code)
    {
        $I = $this->tester;

        $I->fillField(self::$smsCodeField, $code);
        $I->click(self::$submitSmsCodeButton);

        return $this;
    }
}
