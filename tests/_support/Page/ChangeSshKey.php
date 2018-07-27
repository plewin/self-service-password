<?php
namespace Page;

use AcceptanceTester;

class ChangeSshKey
{
    public static $URL = '/change-ssh-key';

    public static $loginField = 'login';

    public static $passwordField = 'password';

    public static $sshKeyField = 'sshkey';

    public static $sendButton = 'Send';

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function changeSshKey($login, $password, $sshKey)
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        $I->fillField(self::$loginField, $login);
        $I->fillField(self::$passwordField, $password);
        $I->fillField(self::$sshKeyField, $sshKey);
        $I->click(self::$sendButton);

        return $this;
    }
}
