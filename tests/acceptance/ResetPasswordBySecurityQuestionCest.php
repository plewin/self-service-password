<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;
use Page\ResetBySecurityQuestion as ResetBySecurityQuestionPage;

/**
 * Class ResetPasswordBySecurityQuestionCest
 */
class ResetPasswordBySecurityQuestionCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function accessingFromMenuWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->expectTo('see Question in the menu');
        $I->see('Question');
        $I->click('Question');
        $I->see('Reset your password');
        $I->expectTo('see Question in the menu active');
        $I->see('Question', '.active');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordWorks(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form with valid data');
        $resetBySecurityQuestion->resetPassword('user1', 'When is your birthday?', 'goodbirthday1', 'myNewPa0$$', 'myNewPa0$$');
        $I->see('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenAnswerIsWrong(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form with valid data');
        $resetBySecurityQuestion->resetPassword('user1', 'When is your birthday?', 'bad answer', 'myNewPa0$$', 'myNewPa0$$');
        $I->see('Your answer is incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenAccountDoesNotExists(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form with valid data');
        $resetBySecurityQuestion->resetPassword('user1ER5T6Y7U890', 'When is your birthday?', 'goodbirthday1', 'myNewPa0$$', 'myNewPa0$$');
        $I->see('Login or password incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenConfirmationIsWrong(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form with valid data except confirmation');
        $resetBySecurityQuestion->resetPassword('user1', 'When is your birthday?', 'bad answer', 'mynewpass', 'mynewpasd');
        $I->see('Passwords mismatch');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenLoginMissing(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form without a login');
        $resetBySecurityQuestion->resetPassword(null, 'When is your birthday?', 'goodbirthday1', 'mynewpass', 'mynewpass');
        $I->see('Your login is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenAnswerMissing(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form without a login');
        $resetBySecurityQuestion->resetPassword('user1', 'When is your birthday?', null, 'mynewpass', 'mynewpass');
        $I->see('No answer given');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenNewPasswordMissing(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form without a login');
        $resetBySecurityQuestion->resetPassword('user1', 'When is your birthday?', 'goodbirthday1', null, 'mynewpass');
        $I->see('Your new password is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenConfirmPasswordMissing(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form without a login');
        $resetBySecurityQuestion->resetPassword('user1', 'When is your birthday?', 'goodbirthday1', 'mynewpass', null);
        $I->see('Please confirm your new password');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenLoginHasInvalidCharacters(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form without a login');
        $resetBySecurityQuestion->resetPassword('é"(-è_çà)', 'When is your birthday?', 'goodbirthday1', 'mynewpass', 'mynewpass');
        //TODO better message, there is no password here
        $I->see('Login or password incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySecurityQuestionPage $resetBySecurityQuestion
     */
    public function changePasswordFailsWhenPasswordRefusedByServer(AcceptanceTester $I, ResetBySecurityQuestionPage $resetBySecurityQuestion)
    {
        $I->amGoingTo('fill the form without a login');
        $resetBySecurityQuestion->resetPassword('user10', 'When is your birthday?', 'goodbirthday10', 'myNewPa0$$', 'myNewPa0$$');
        $I->see('Password was refused');
        $I->dontSee('Your password was changed');
    }
}
