<?php
namespace Step\Acceptance;

use Codeception\Scenario;
use Page\ResetByEmail as ResetByEmailPage;

class TesterWithValidToken extends \AcceptanceTester
{
    private $resetByEmailPage;

    public function __construct(Scenario $scenario, ResetByEmailPage $resetByEmailPage)
    {
        parent::__construct($scenario);
        $this->resetByEmailPage = $resetByEmailPage;
    }

    /**
     * @param string $user
     *
     * @return string Url with valid token
     */
    public function getValidUrlWithToken($user='user1'): string
    {
        $I = $this;

        $this->resetByEmailPage->askResetEmail($user, $user . '@example.com');
        $I->see('A confirmation email has been sent');
        $I->seeEmailIsSent();
        $myEmailMessage = $I->grabLastSentEmail();
        $I->seeInMail($user,$myEmailMessage);
        $I->seeUrlInMail($myEmailMessage);
        return $I->grabUrlInMail($myEmailMessage);
    }
}