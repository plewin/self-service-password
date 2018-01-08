<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;

/**
 * Class ResetPasswordBySmsCest
 */
class ResetPasswordBySmsCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function accessingFromMenuWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->expectTo('see SMS in the menu');
        $I->see('SMS');
        $I->click('SMS');
        $I->see('Get a reset code');
        $I->expectTo('see SMS in the menu active');
        $I->see('SMS', '.active');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordBySmsWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-sms');
        $I->fillField('login', 'user1');
        $I->click('Get user');
        $I->see('Check that user information are correct and press Send to get SMS token');
        $I->see('User full name');
        $I->see('User1 DisplayName');
        $I->see('Login');
        $I->see('user1');
        $I->see('SMS number');
        $I->see('0612****78');
        $I->click('Send');
        $I->seeSmsIsSent();
        $I->see('A confirmation code has been send by SMS');
        $I->see('SMS token');
        $code = $I->grabCodeInSms();
        $I->fillField('smstoken', $code);
        $I->click('Send');
        $I->see('Your new password is required');
        $I->see('The token sent by sms allows you to reset your password.');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function findUserFailsWhenLoginHasInvalidCharacters(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-sms');
        $I->fillField('login', '&é"\'(-è_)');
        $I->click('Get user');
        //TODO this message should be better
        $I->see('Login or password incorrect');
        $I->dontSee('Check that user information are correct and press Send to get SMS token');
        $I->dontSee('User full name');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function findUserFailsWhenAccountDoesNotExist(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-sms');
        $I->fillField('login', 'user456789');
        $I->click('Get user');
        //TODO this message should be better
        $I->see('Login or password incorrect');
        $I->dontSee('Check that user information are correct and press Send to get SMS token');
        $I->dontSee('User full name');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function findUserFailsWhenAccountHasNoPhoneNumber(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-sms');
        $I->fillField('login', 'user3');
        $I->click('Get user');
        $I->see('Can\'t find mobile number');
        $I->dontSee('Check that user information are correct and press Send to get SMS token');
        $I->dontSee('User full name');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function noTokenLinkWhenSmsCodeIsWrong(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-sms');
        $I->fillField('login', 'user1');
        $I->click('Get user');
        $I->expect('The user details to be ok');
        $I->click('Send');
        $I->seeSmsIsSent();
        $I->see('A confirmation code has been send by SMS');
        $I->see('SMS token');
        $code = $I->grabCodeInSms();
        $I->amGoingTo("put the wrong code");
        $wrongCode = $code + 1;
        $I->fillField('smstoken', $wrongCode);
        $I->click('Send');
        //TODO change token to sms code
        $I->see('Invalid token, try again');
        $I->see('SMS token');
        $I->see('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function smsCode3AttemptsAllowed(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-sms');
        $I->fillField('login', 'user1');
        $I->click('Get user');
        $I->expect('The user details to be ok');
        $I->click('Send');
        $I->seeSmsIsSent();
        $I->see('A confirmation code has been send by SMS');
        $I->see('SMS token');
        $code = $I->grabCodeInSms();
        $I->amGoingTo("put the wrong code");
        $wrongCode = $code + 1;

        $I->fillField('smstoken', $wrongCode);
        $I->click('Send');
        $I->see('Invalid token, try again');
        $I->see('SMS token');
        $I->see('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');

        $I->fillField('smstoken', $wrongCode);
        $I->click('Send');
        $I->see('Invalid token, try again');
        $I->see('SMS token');
        $I->see('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');

        $I->fillField('smstoken', $wrongCode);
        $I->click('Send');
        //TODO message incorrect, there is no try again because it is the last attempt
        $I->see('Invalid token, try again');
        //TODO Should not see this, no more attempts
        $I->see('SMS token');
        $I->see('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');

        $I->fillField('smstoken', $wrongCode);
        $I->click('Send');
        // TODO message incorrect, there is no try again because it is the last attempt
        $I->see('Token is not valid');
        $I->dontSee('SMS token');
        $I->dontSee('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');
    }
}
