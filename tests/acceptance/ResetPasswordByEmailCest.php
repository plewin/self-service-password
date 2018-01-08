<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;

/**
 * Class ResetPasswordByEmailCest
 */
class ResetPasswordByEmailCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function accessingFromMenuWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->expectTo('see Email in the menu');
        $I->see('Email');
        $I->click('Email');
        $I->see('Email a password reset link');
        $I->expectTo('see Email in the menu active');
        $I->see('Email', '.active');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordByEmailWorks(AcceptanceTester $I)
    {
        $url = $this->getValidUrlWithToken($I);
        $I->amOnPage($url);
        $I->expectTo('see the reset form because the token is valid');
        $I->see('Login');
        $I->see('Password');
        $I->see('Confirm');
        $I->see('Send', 'form');
        $I->expectTo('see my login already filled and disabled');
        $I->canSeeInField('Login', 'user1');
        $I->seeInField('form input[disabled]','user1');
        $I->fillField('New password', 'newpass');
        $I->fillField('Confirm', 'newpass');
        $I->click('Send');
        $I->see('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordFailsWhenLoginIsMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-email');
        $I->fillField('mail', 'user1@example.com');
        $I->click('Send');
        $I->see('Your login is required');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordFailsWhenLoginHasInvalidCharacters(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-email');
        $I->fillField('login', '{&é"\'(-è_çà');
        $I->fillField('mail', 'user1@example.com');
        $I->click('Send');
        $I->see('Login or password incorrect');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordFailsWhenEmailIsMissing(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-email');
        $I->fillField('login', 'user1');
        $I->click('Send');
        $I->see('Your email address is required');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordFailsWhenEmailIsIncorrect(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-email');
        $I->fillField('login', 'user1');
        $I->fillField('mail', 'notuser1@example.com');
        $I->click('Send');
        $I->see('The email address does not match the submitted user name');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordFailsWhenAccountDoesNotExist(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-by-email');
        $I->fillField('login', 'user34567890');
        $I->fillField('mail', 'user34567890@example.com');
        $I->click('Send');
        //TODO fix message, "password incorrect does not make sense here"
        $I->see('Login or password incorrect');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function tokenCannotBeReused(AcceptanceTester $I)
    {
        $url = $this->getValidUrlWithToken($I);
        $I->amOnPage($url);
        $I->expectTo('see the reset form because the token is valid');
        $I->fillField('New password', 'newpass');
        $I->fillField('Confirm', 'newpass');
        $I->click('Send');
        $I->see('Your password was changed');

        // Reuse attempt
        $I->amOnPage($url);
        $I->expectTo('see don\'t see the form because the token is reused');
        $I->see('Token is not valid');
        $I->dontSee('New password');
        $I->dontSee('Confirm');
        $I->dontSee('Send');

    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordFailsWhenTokenMissing(AcceptanceTester $I)
    {
        $url = $this->getValidUrlWithToken($I);
        $I->amOnPage($url);
        $I->fillField('New password', 'newpass');
        $I->fillField('Confirm', 'notnewpass');
        $I->click('Send');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordFailsWhenPasswordRefusedByServer(AcceptanceTester $I)
    {
        $url = $this->getValidUrlWithToken($I, 'user10');
        $I->amOnPage($url);
        $I->fillField('New password', 'should be rejected');
        $I->fillField('Confirm', 'should be rejected');
        $I->click('Send');
        $I->see('password was refused');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function resetPasswordFailWhenPasswordAndConfirmMismatch(AcceptanceTester $I)
    {
        $I->amOnPage('/reset-password-with-token');
        $I->see('Token is required');
        $I->dontSee('Confirm');
        $I->dontSee('Send', 'form');
    }

    /**
     * @param AcceptanceTester $I
     * @param string $user
     *
     * @return string Url with valid token
     */
    private function getValidUrlWithToken(AcceptanceTester $I, $user='user1')
    {
        $I->amOnPage('/reset-password-by-email');
        $I->fillField('login', $user);
        $I->fillField('mail', $user . '@example.com');
        $I->click('Send');
        $I->see('A confirmation email has been sent');
        $I->seeEmailIsSent();
        $myEmailMessage = $I->grabLastSentEmail();
        $I->seeUrlInMail($myEmailMessage);
        return $I->grabUrlInMail($myEmailMessage);
    }
}
