<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;

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
     */
    public function changePasswordWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user1');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('answer', 'goodbirthday1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->see('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenAnswerIsWrong(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user1');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('answer', 'bad answer');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->see('Your answer is incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenAccountDoesNotExists(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user1ER5T6Y7U890');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('answer', 'goodbirthday1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->see('Login or password incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenConfirmationIsWrong(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form with valid data except confirmation');
        $I->fillField('login', 'user1');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('answer', 'bad answer');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpasd');
        $I->click('Send');
        $I->see('Passwords mismatch');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenLoginMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form without a login');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('answer', 'goodbirthday1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->see('Your login is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenAnswerMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form without a login');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('login', 'user1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->see('No answer given');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenNewPasswordMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form without a login');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('login', 'user1');
        $I->fillField('answer', 'goodbirthday1');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->see('Your new password is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenConfirmPasswordMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form without a login');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('login', 'user1');
        $I->fillField('answer', 'goodbirthday1');
        $I->fillField('newpassword', 'mynewpass');
        $I->click('Send');
        $I->see('Please confirm your new password');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenLoginHasInvalidCharacters(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form without a login');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('login', 'é"(-è_çà)');
        $I->fillField('answer', 'goodbirthday1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        //TODO better message, there is no old password here
        $I->see('Login or password incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenPasswordRefusedByServer(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-question');
        $I->amGoingTo('fill the form without a login');
        $I->selectOption('Question','When is your birthday?');
        $I->fillField('login', 'user10');
        $I->fillField('answer', 'goodbirthday10');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->see('Password was refused');
        $I->dontSee('Your password was changed');
    }
}
