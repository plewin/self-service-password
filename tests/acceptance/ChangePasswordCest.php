<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;

/**
 * Class ChangePasswordCest
 */
class ChangePasswordCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user1');
        $I->fillField('oldpassword', 'password1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->expect('the new password is accepted');
        $I->see('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenConfirmIsWrong(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with a wrong confirmation');
        $I->fillField('login', 'user1');
        $I->fillField('oldpassword', 'password1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'notmynewpass');
        $I->click('Send');
        $I->expect('the new password is not accepted');
        $I->see('Passwords mismatch');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenOldPasswordIsWrong(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with a wrong old password');
        $I->fillField('login', 'user1');
        $I->fillField('oldpassword', 'invalidpassword');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->expect('the new password is not accepted');
        $I->see('Login or password incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenLoginMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('oldpassword', 'password1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->expect('the new password is not accepted');
        $I->see('Your login is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenOldPasswordMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->expect('the new password is not accepted');
        $I->see('Your old password is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenNewPasswordMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user1');
        $I->fillField('oldpassword', 'password1');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->expect('the new password is not accepted');
        $I->see('Your new password is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenConfirmPasswordMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user1');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('oldpassword', 'password1');
        $I->click('Send');
        $I->expect('the new password is not accepted');
        $I->see('Please confirm your new password');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenLoginContainsInvalidCharacters(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', '&é"\'(-è_ç)');
        $I->fillField('newpassword', 'mynewpass');
        $I->fillField('oldpassword', 'password1');
        $I->fillField('confirmpassword', 'mynewpass');
        $I->click('Send');
        $I->expect('the new password is not accepted');
        $I->see('Login or password incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changePasswordFailsWhenAccountHasForbiddenChangeInPasswordPolicy(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');
        $I->see('Self-service password');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user10');
        $I->fillField('oldpassword', 'password10');
        $I->fillField('newpassword', 'password11');
        $I->fillField('confirmpassword', 'password11');
        $I->click('Send');
        $I->expect('the new password is not accepted');
        $I->see('Password was refused');
        $I->dontSee('Your password was changed');
    }
}
