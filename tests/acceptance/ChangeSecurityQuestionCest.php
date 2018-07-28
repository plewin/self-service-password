<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;
use Page\ChangeSecurityQuestion as ChangeSecurityQuestionPage;

/**
 * Class ChangeSecurityQuestionCest
 */
class ChangeSecurityQuestionCest
{
    /**
     * @param AcceptanceTester $I
     * @param ChangeSecurityQuestionPage $changeSecurityQuestionPage
     */
    public function changeSecurityQuestionWorks(AcceptanceTester $I, ChangeSecurityQuestionPage $changeSecurityQuestionPage): void
    {
        $I->amGoingTo('fill the form with valid data');
        $changeSecurityQuestionPage->changePassword('user1', 'password1', 'When is your birthday?', '42');
        $I->expect('the new answer is accepted');
        $I->see('Your answer has been registered');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSecurityQuestionPage $changeSecurityQuestionPage
     */
    public function changeSecurityQuestionFailWhenMissingLogin(AcceptanceTester $I, ChangeSecurityQuestionPage $changeSecurityQuestionPage): void
    {
        $I->amGoingTo('fill the form with valid data');
        $changeSecurityQuestionPage->changePassword(null, 'password1', 'When is your birthday?', '42');
        $I->expect('the new answer is not accepted');
        $I->see('Your login is required');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSecurityQuestionPage $changeSecurityQuestionPage
     */
    public function changeSecurityQuestionFailWhenPasswordWrong(AcceptanceTester $I, ChangeSecurityQuestionPage $changeSecurityQuestionPage): void
    {
        $I->amGoingTo('fill the form with wrong password');
        $changeSecurityQuestionPage->changePassword('user1', 'badpassword', 'When is your birthday?', '42');
        $I->expect('the new answer is not accepted');
        $I->see('Login or password incorrect');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSecurityQuestionPage $changeSecurityQuestionPage
     */
    public function changeSecurityQuestionFailWhenMissingPassword(AcceptanceTester $I, ChangeSecurityQuestionPage $changeSecurityQuestionPage): void
    {
        $I->amGoingTo('fill the form with valid data but missing password');
        $changeSecurityQuestionPage->changePassword('user1', null, 'When is your birthday?', '42');
        $I->expect('the new answer is not accepted');
        $I->see('Your password is required');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSecurityQuestionPage $changeSecurityQuestionPage
     */
    public function changeSecurityQuestionFailWhenMissingAnswer(AcceptanceTester $I, ChangeSecurityQuestionPage $changeSecurityQuestionPage): void
    {
        $I->amGoingTo('fill the form with valid data but missing answer');
        $changeSecurityQuestionPage->changePassword('user1', 'password1', 'When is your birthday?', null);
        $I->expect('the new answer is not accepted');
        $I->see('No answer given');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSecurityQuestionPage $changeSecurityQuestionPage
     */
    public function changeSecurityQuestionFailWhenLoginHasInvalidCharacters(AcceptanceTester $I, ChangeSecurityQuestionPage $changeSecurityQuestionPage): void
    {
        $I->amGoingTo('fill the form with invalid data');
        $changeSecurityQuestionPage->changePassword('&é"\'(-è_çà)', 'password1', 'When is your birthday?', '42');
        $I->expect('the new answer is not accepted');
        //TODO this message should not say password incorrect
        $I->see('Login or password incorrect');
    }

}
