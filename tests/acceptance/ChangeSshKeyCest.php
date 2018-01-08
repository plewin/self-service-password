<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;

/**
 * Class ChangeSshKeyCest
 */
class ChangeSshKeyCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function accessingFromMenuWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->expectTo('see SSH Key in the menu');
        $I->see('SSH Key');
        $I->click('SSH Key');
        $I->see('Change your SSH Key');
        $I->expectTo('see SSH Key in the menu active');
        $I->see('SSH Key', '.active');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changeSshKeyWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/change-ssh-key');
        $I->amGoingTo('fill the form with valid data');
        $I->fillField('login', 'user1');
        $I->fillField('password', 'password1');
        $I->fillField('sshkey', 'dftyguijok');
        $I->click('Send');
        $I->expect('the new ssh key is accepted');
        $I->see('Your SSH Key was changed');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changeSshKeyFailsWhenMissingLogin(AcceptanceTester $I)
    {
        $I->amOnPage('/change-ssh-key');
        $I->amGoingTo('fill the form with valid data without a login');
        $I->fillField('password', 'password1');
        $I->fillField('sshkey', 'dftyguijok');
        $I->click('Send');
        $I->expect('the new ssh key is not accepted');
        $I->see('Login is required');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changeSshKeyFailsWhenPasswordWrong(AcceptanceTester $I)
    {
        $I->amOnPage('/change-ssh-key');
        $I->amGoingTo('fill the form with valid data without a login');
        $I->fillField('login', 'user1');
        $I->fillField('password', 'bad password');
        $I->fillField('sshkey', 'dftyguijok');
        $I->click('Send');
        $I->expect('the new ssh key is not accepted');
        $I->see('login or password incorrect');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changeSshKeyFailsWhenMissingPassword(AcceptanceTester $I)
    {
        $I->amOnPage('/change-ssh-key');
        $I->amGoingTo('fill the form with valid data without a password');
        $I->fillField('login', 'user1');
        $I->fillField('sshkey', 'dftyguijok');
        $I->click('Send');
        $I->expect('the new ssh key is not accepted');
        $I->see('Your password is required');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changeSshKeyFailsWhenMissingSshKey(AcceptanceTester $I)
    {
        $I->amOnPage('/change-ssh-key');
        $I->amGoingTo('fill the form with valid data without a ssh key');
        $I->fillField('login', 'user1');
        $I->fillField('password', 'password1');
        $I->click('Send');
        $I->expect('the new ssh key is not accepted');
        $I->see('SSH Key is required');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function changeSshKeyFailsWhenLoginHasInvalidCharacters(AcceptanceTester $I)
    {
        $I->amOnPage('/change-ssh-key');
        $I->amGoingTo('fill the form with valid data without a ssh key');
        $I->fillField('login', '&é"\'(-è_çà)');
        $I->fillField('password', 'password1');
        $I->fillField('sshkey', 'dftyguijok');
        $I->click('Send');
        $I->expect('the new ssh key is not accepted');
        $I->see('Login or password incorrect');
    }
}
