<?php

namespace App\Tests\Acceptance;

use AcceptanceTester;
use Page\ChangeSshKey as ChangeSshKeyPage;

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
     * @param ChangeSshKeyPage $changeSshKeyPage
     */
    public function changeSshKeyWorks(AcceptanceTester $I, ChangeSshKeyPage $changeSshKeyPage)
    {
        $I->amGoingTo('fill the form with valid data');
        $changeSshKeyPage->changeSshKey('user1', 'password1', 'dftyguijok');
        $I->expect('the new ssh key is accepted');
        $I->see('Your SSH Key was changed');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSshKeyPage $changeSshKeyPage
     */
    public function changeSshKeyFailsWhenMissingLogin(AcceptanceTester $I, ChangeSshKeyPage $changeSshKeyPage)
    {
        $I->amGoingTo('fill the form with valid data without a login');
        $changeSshKeyPage->changeSshKey(null, 'password1', 'dftyguijok');
        $I->expect('the new ssh key is not accepted');
        $I->see('Login is required');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSshKeyPage $changeSshKeyPage
     */
    public function changeSshKeyFailsWhenPasswordWrong(AcceptanceTester $I, ChangeSshKeyPage $changeSshKeyPage)
    {
        $I->amGoingTo('fill the form with valid data without a login');
        $changeSshKeyPage->changeSshKey('user1', 'bad password', 'dftyguijok');
        $I->expect('the new ssh key is not accepted');
        $I->see('login or password incorrect');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSshKeyPage $changeSshKeyPage
     */
    public function changeSshKeyFailsWhenMissingPassword(AcceptanceTester $I, ChangeSshKeyPage $changeSshKeyPage)
    {
        $I->amGoingTo('fill the form with valid data without a password');
        $changeSshKeyPage->changeSshKey('user1', null, 'dftyguijok');
        $I->expect('the new ssh key is not accepted');
        $I->see('Your password is required');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSshKeyPage $changeSshKeyPage
     */
    public function changeSshKeyFailsWhenMissingSshKey(AcceptanceTester $I, ChangeSshKeyPage $changeSshKeyPage)
    {
        $I->amGoingTo('fill the form with valid data without a ssh key');
        $changeSshKeyPage->changeSshKey('user1', 'password1', null);
        $I->expect('the new ssh key is not accepted');
        $I->see('SSH Key is required');
    }

    /**
     * @param AcceptanceTester $I
     * @param ChangeSshKeyPage $changeSshKeyPage
     */
    public function changeSshKeyFailsWhenLoginHasInvalidCharacters(AcceptanceTester $I, ChangeSshKeyPage $changeSshKeyPage)
    {
        $I->amGoingTo('fill the form with valid data without a ssh key');
        $changeSshKeyPage->changeSshKey('&é"\'(-è_çà)', 'password1', 'dftyguijok');
        $I->expect('the new ssh key is not accepted');
        $I->see('Login or password incorrect');
    }
}
