<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;
use Page\ResetByEmail as ResetByEmailPage;
use Page\ResetPassword as ResetPasswordPage;
use Step\Acceptance\TesterWithValidToken;

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
     * @param TesterWithValidToken $I
     */
    public function resetPasswordByEmailWorks(TesterWithValidToken $I)
    {
        $url = $I->getValidUrlWithToken();
        $I->amOnPage($url);
        $I->expectTo('see the reset form because the token is valid');
        $I->see('Login');
        $I->see('Password');
        $I->see('Confirm');
        $I->see('Send', 'form');
        $I->expectTo('see my login already filled and disabled');
        $I->canSeeInField('Login', 'user1');
        $I->seeInField('form input[disabled]','user1');
        $I->fillField('New password', 'myNewPa0$$');
        $I->fillField('Confirm', 'myNewPa0$$');
        $I->click('Send');
        $I->see('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetByEmailPage $resetByEmailPage
     */
    public function resetPasswordFailsWhenLoginIsMissing(AcceptanceTester $I, ResetByEmailPage $resetByEmailPage)
    {
        $resetByEmailPage->askResetEmail(null, 'user1@example.com');
        $I->see('Your login is required');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetByEmailPage $resetByEmailPage
     */
    public function resetPasswordFailsWhenLoginHasInvalidCharacters(AcceptanceTester $I, ResetByEmailPage $resetByEmailPage)
    {
        $resetByEmailPage->askResetEmail('{&é"\'(-è_çà', 'user1@example.com');
        $I->see('Login or password incorrect');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetByEmailPage $resetByEmailPage
     */
    public function resetPasswordFailsWhenEmailIsMissing(AcceptanceTester $I, ResetByEmailPage $resetByEmailPage)
    {
        $resetByEmailPage->askResetEmail('user1', null);
        $I->see('Your email address is required');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetByEmailPage $resetByEmailPage
     */
    public function resetPasswordFailsWhenEmailIsIncorrect(AcceptanceTester $I, ResetByEmailPage $resetByEmailPage)
    {
        $resetByEmailPage->askResetEmail('user1', 'notuser1@example.com');
        $I->see('The email address does not match the submitted user name');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetByEmailPage $resetByEmailPage
     */
    public function resetPasswordFailsWhenAccountDoesNotExist(AcceptanceTester $I, ResetByEmailPage $resetByEmailPage)
    {
        $resetByEmailPage->askResetEmail('user34567890', 'user34567890@example.com');
        //TODO fix message, "password incorrect does not make sense here"
        $I->see('Login or password incorrect');
        $I->dontSee('A confirmation email has been sent');
    }

    /**
     * @param TesterWithValidToken $I
     * @param ResetPasswordPage $resetPasswordPage
     */
    public function tokenCannotBeReused(TesterWithValidToken $I, ResetPasswordPage $resetPasswordPage)
    {
        $url = $I->getValidUrlWithToken();
        $I->amOnPage($url);
        $I->expectTo('see the reset form because the token is valid');

        $resetPasswordPage->resetPassword('myNewPa0$$', 'myNewPa0$$');
        $I->see('Your password was changed');

        // Reuse attempt
        $I->amOnPage($url);
        $I->expectTo('see don\'t see the form because the token is reused');
        $I->see('Token is not valid');
        $I->dontSee($resetPasswordPage::$newPasswordField);
        $I->dontSee($resetPasswordPage::$confirmPasswordField);
        $I->dontSee($resetPasswordPage::$sendButton);
    }

    /**
     * @param TesterWithValidToken $I
     * @param ResetPasswordPage $resetPasswordPage
     */
    public function resetPasswordFailWhenPasswordAndConfirmMismatch(TesterWithValidToken $I, ResetPasswordPage $resetPasswordPage)
    {
        $url = $I->getValidUrlWithToken();
        $I->amOnPage($url);

        $resetPasswordPage->resetPassword('newpass', 'notnewpass');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param TesterWithValidToken $I
     * @param ResetPasswordPage $resetPasswordPage
     */
    public function resetPasswordFailsWhenPasswordRefusedByServer(TesterWithValidToken $I, ResetPasswordPage $resetPasswordPage)
    {
        $url = $I->getValidUrlWithToken('user10');
        $I->amOnPage($url);
        $resetPasswordPage->resetPassword('should be rejected', 'should be rejected');
        $I->see('password was refused');
        $I->dontSee('Your password was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ResetPasswordPage $resetPasswordPage
     */
    public function resetPasswordFailsWhenTokenMissing(AcceptanceTester $I, ResetPasswordPage $resetPasswordPage)
    {
        $I->amOnPage($resetPasswordPage::$URL);
        $I->see('Token is required');
        $I->dontSee('Confirm');
        $I->dontSee('Send', 'form');
    }
}
