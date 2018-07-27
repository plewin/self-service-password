<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;
use Page\ChangePassword as ChangePasswordPage;

/**
 * Class ChangePasswordCest
 */
class ChangePasswordCest
{
    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordWorks(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with valid data');
        $passwordChangePage->changePassword('user1', 'password1', 'myNewpa0$$', 'myNewpa0$$');
        $I->expect('the new password is accepted');
        $I->see('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordFailsWhenConfirmIsWrong(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with a wrong confirmation');
        $passwordChangePage->changePassword('user1', 'password1', 'mynewpass', 'notmynewpass');
        $I->expect('the new password is not accepted');
        $I->see('Passwords mismatch');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordFailsWhenOldPasswordIsWrong(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with a wrong current password');
        $passwordChangePage->changePassword('user1', 'invalidpassword', 'myNewPa0$$', 'myNewPa0$$');
        $I->expect('the new password is not accepted');
        $I->see('Login or password incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordFailsWhenLoginMissing(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with valid data');
        $passwordChangePage->changePassword(null, 'password1', 'mynewpass', 'mynewpass');
        $I->expect('the new password is not accepted');
        $I->see('Your login is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordFailsWhenOldPasswordMissing(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with valid data');
        $passwordChangePage->changePassword('user1', null, 'mynewpass', 'mynewpass');
        $I->expect('the new password is not accepted');
        $I->see('Your current password is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordFailsWhenNewPasswordMissing(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with valid data');
        $passwordChangePage->changePassword('user1', 'password1', null, 'mynewpass');
        $I->expect('the new password is not accepted');
        $I->see('Your new password is required');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordFailsWhenConfirmPasswordMissing(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with valid data');
        $passwordChangePage->changePassword('user1', 'password1', 'mynewpass', null);
        $I->expect('the new password is not accepted');
        $I->see('Please confirm your new password');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordFailsWhenLoginContainsInvalidCharacters(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with valid data');
        $passwordChangePage->changePassword('&é"\'(-è_ç)', 'mynewpass', 'password1', 'mynewpass');
        $I->expect('the new password is not accepted');
        $I->see('Login or password incorrect');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangePasswordPage $passwordChangePage
     */
    public function changePasswordFailsWhenAccountHasForbiddenChangeInPasswordPolicy(AcceptanceTester $I, ChangePasswordPage $passwordChangePage)
    {
        $I->amGoingTo('fill the form with valid data');
        $passwordChangePage->changePassword('user10', 'password10', 'myNewPa0$$', 'myNewPa0$$');
        $I->expect('the new password is not accepted');
        $I->see('Password was refused');
        $I->dontSee('Your password was changed');
    }
}
