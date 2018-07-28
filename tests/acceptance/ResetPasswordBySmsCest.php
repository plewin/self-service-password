<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;
use Page\ResetBySms as ResetBySmsPage;
/**
 * Class ResetPasswordBySmsCest
 */
class ResetPasswordBySmsCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function accessingFromMenuWorks(AcceptanceTester $I): void
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
     * @param ResetBySmsPage $resetBySmsPage
     */
    public function resetPasswordBySmsWorks(AcceptanceTester $I, ResetBySmsPage $resetBySmsPage): void
    {
        $resetBySmsPage->findUser('user1');
        $I->see('Check that user information are correct and press Send to get SMS token');
        $I->see('User full name');
        $I->see('User1 DisplayName');
        $I->see('Login');
        $I->see('user1');
        $I->see('SMS number');
        $I->see('0612****78');
        $resetBySmsPage->sendSms();
        $I->seeSmsIsSent();
        $I->see('A confirmation code has been send by SMS');
        $I->see('SMS token');
        $code = $I->grabCodeInSms();
        $resetBySmsPage->submitSmsCode($code);
        $I->see('Reset your password');
        $I->see('The token sent by sms allows you to reset your password.');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySmsPage $resetBySmsPage
     */
    public function findUserFailsWhenLoginHasInvalidCharacters(AcceptanceTester $I, ResetBySmsPage $resetBySmsPage): void
    {
        $resetBySmsPage->findUser('&é"\'(-è_)');
        //TODO this message should be better
        $I->see('Login or password incorrect');
        $I->dontSee('Check that user information are correct and press Send to get SMS token');
        $I->dontSee('User full name');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySmsPage $resetBySmsPage
     */
    public function findUserFailsWhenAccountDoesNotExist(AcceptanceTester $I, ResetBySmsPage $resetBySmsPage): void
    {
        $resetBySmsPage->findUser('user456789');
        //TODO this message should be better
        $I->see('Login or password incorrect');
        $I->dontSee('Check that user information are correct and press Send to get SMS token');
        $I->dontSee('User full name');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySmsPage $resetBySmsPage
     */
    public function findUserFailsWhenAccountHasNoPhoneNumber(AcceptanceTester $I, ResetBySmsPage $resetBySmsPage): void
    {
        $resetBySmsPage->findUser('user3');
        $I->see('Can\'t find mobile number');
        $I->dontSee('Check that user information are correct and press Send to get SMS token');
        $I->dontSee('User full name');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySmsPage $resetBySmsPage
     */
    public function noTokenLinkWhenSmsCodeIsWrong(AcceptanceTester $I, ResetBySmsPage $resetBySmsPage): void
    {
        $resetBySmsPage->findUser('user1');
        $I->expect('The user details to be ok');
        $resetBySmsPage->sendSms();
        $I->seeSmsIsSent();
        $I->see('A confirmation code has been send by SMS');
        $I->see('SMS token');
        $code = $I->grabCodeInSms();
        $I->amGoingTo("put the wrong code");
        $wrongCode = $code + 1;
        $resetBySmsPage->submitSmsCode($wrongCode);
        //TODO change token to sms code
        $I->see('Invalid token, try again');
        $I->see('SMS token');
        $I->see('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetBySmsPage $resetBySmsPage
     */
    public function smsCode3AttemptsAllowed(AcceptanceTester $I, ResetBySmsPage $resetBySmsPage): void
    {
        $resetBySmsPage->findUser('user1');
        $I->expect('The user details to be ok');
        $resetBySmsPage->sendSms();
        $I->seeSmsIsSent();
        $I->see('A confirmation code has been send by SMS');
        $I->see('SMS token');
        $code = $I->grabCodeInSms();
        $I->amGoingTo("put the wrong code");
        $wrongCode = $code + 1;

        $resetBySmsPage->submitSmsCode($wrongCode);
        $I->see('Invalid token, try again');
        $I->see('SMS token');
        $I->see('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');

        $resetBySmsPage->submitSmsCode($wrongCode);
        $I->see('Invalid token, try again');
        $I->see('SMS token');
        $I->see('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');

        $resetBySmsPage->submitSmsCode($wrongCode);
        //TODO message incorrect, there is no try again because it is the last attempt
        $I->see('Invalid token, try again');
        //TODO Should not see this, no more attempts
        $I->see('SMS token');
        $I->see('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');

        $resetBySmsPage->submitSmsCode($wrongCode);
        // TODO message incorrect, there is no try again because it is the last attempt
        $I->see('Token is not valid');
        $I->dontSee('SMS token');
        $I->dontSee('Send');
        $I->dontSee('Your new password is required');
        $I->dontSee('The token sent by sms allows you to reset your password.');
    }
}
