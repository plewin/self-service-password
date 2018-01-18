<?php

namespace App\Tests\Integration\ApacheDirectoryServer;

use App\Service\LdapClient;
use App\Tests\Integration\LdapIntegrationTestCase;
use App\Utils\PasswordEncoder;
use App\Utils\PasswordVerifier;
use Psr\Log\NullLogger;

/**
 * Class LdapClientTest
 */

class LdapClientTest extends LdapIntegrationTestCase
{
    protected function setUp()
    {
        if (getenv('TRAVIS') == 'true') {
            $this->markTestSkipped('Cannot test Apache Directory Server integration on Travis');
        }
    }

    /**
     * Test that we can connect to Apache Directory Server
     */
    public function testConnect()
    {
        $client = $this->createLdapClient();

        // expect no exception
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());
    }

    public function testConnectBrokenTls()
    {
        // use tls but but port non tls
        $client = $this->createLdapClient(['use_tls' => true]);

        $this->setExpectedException('App\Exception\LdapErrorException');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());
    }

    public function testConnectWrongCredentials()
    {
        // use tls but but port non tls
        $client = $this->createLdapClient(['ldap_bind_dn' => 'bad_dn']);

        $this->setExpectedException('App\Exception\LdapErrorException');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());
    }

    public function testCheckOldPassword()
    {
        $client = $this->createLdapClient();

        /** @noinspection PhpUnhandledExceptionInspection */
        $client->connect();

        $context = [
            'user_dn' => 'uid=user1,ou=People,dc=example,dc=com',
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $client->checkOldPassword('password1', $context);

        // now we expect the next one to throw an exception
        $this->setExpectedException('App\Exception\LdapInvalidUserCredentialsException');
        /** @noinspection PhpUnhandledExceptionInspection */
        $client->checkOldPassword('badpassword1', $context);
    }

    public function testCheckMailValid()
    {
        $client = $this->createLdapClient();

        /** @noinspection PhpUnhandledExceptionInspection */
        $client->connect();

        $this->assertTrue($client->checkMail('user1', 'user1@example.com'));
    }

    public function testCheckMailInvalidUser()
    {
        $client = $this->createLdapClient();

        $client->connect();

        $this->setExpectedException('App\Exception\LdapInvalidUserCredentialsException');
        $client->checkMail('user456789', 'user1@example.com');
    }

    public function testCheckMailInvalidMail()
    {
        $client = $this->createLdapClient();

        $client->connect();

        $this->setExpectedException('App\Exception\LdapEntryFoundInvalidException');
        $client->checkMail('user1', 'user3456789@example.com');
    }

    public function testCheckMailUserMailMissing()
    {
        $client = $this->createLdapClient();

        $client->connect();

        $this->setExpectedException('App\Exception\LdapEntryFoundInvalidException');
        $client->checkMail('user2', 'user2@example.com');
    }

    public function testFetchUserEntryContextBadFilter()
    {
        $client = $this->createLdapClient(['ldap_filter' => 'badfilter']);

        $client->connect();

        $context = [];
        $this->setExpectedException('App\Exception\LdapErrorException');
        $client->fetchUserEntryContext('user1', ['dn']);
    }

    public function testFetchUserEntryContextBadBase()
    {
        $client = $this->createLdapClient(['ldap_base' => 'ou=sdfghjklkoijuhgyf,dc=invalid']);

        $client->connect();

        $context = [];
        $this->setExpectedException('App\Exception\LdapErrorException');
        $client->fetchUserEntryContext('user1', ['dn']);
    }

    public function testFetchUserEntryContext()
    {
        $client = $this->createLdapClient();

        $client->connect();

        $context = $client->fetchUserEntryContext('user1', ['dn']);
        $this->assertSame($context['user_dn'], 'uid=user1,ou=People,dc=example,dc=com');

        $context = $client->fetchUserEntryContext('user1', ['mail']);
        $this->assertSame($context['user_mail'], 'user1@example.com');
        $this->assertContains('user1@example.com', $context['user_mails']);
        $this->assertCount(1, $context['user_mails']);

        $context = $client->fetchUserEntryContext('user1', ['sms']);
        $this->assertSame($context['user_sms_raw'], '0123456789');

        $context = $client->fetchUserEntryContext('user1', ['displayname']);
        $this->assertSame($context['user_displayname'], 'User1CN');

        $context = $client->fetchUserEntryContext('user1', ['shadow', 'samba']);
        $this->assertFalse($context['user_is_samba_account']);
        $this->assertFalse($context['user_is_shadow_account']);

        $context = $client->fetchUserEntryContext('user2', ['shadow', 'samba']);
        $this->assertTrue($context['user_is_samba_account']);
        $this->assertFalse($context['user_is_shadow_account']);

        $context = $client->fetchUserEntryContext('user3', ['shadow', 'samba']);
        $this->assertFalse($context['user_is_samba_account']);
        $this->assertTrue($context['user_is_shadow_account']);

        $context = $client->fetchUserEntryContext('user5', ['questions']);
        $this->assertTrue(isset($context['user_answers']));
        $this->assertCount(1, $context['user_answers']);
    }

    public function testFetchUserEntryNotFound()
    {
        $client = $this->createLdapClient();

        $client->connect();

        $this->setExpectedException('App\Exception\LdapInvalidUserCredentialsException');
        $context = $client->fetchUserEntryContext('user456789', ['dn']);
    }

    public function testCheckQuestionAnswer()
    {
        $client = $this->createLdapClient();

        $client->connect();

        $context = $client->fetchUserEntryContext('user5', ['questions']);
        $this->assertTrue($client->checkQuestionAnswer('user5', 'ice', 'vanilla', $context));
        $this->assertFalse($client->checkQuestionAnswer('user5', 'ice', 'chocolate', $context));
    }

    public function testChangeQuestion()
    {
        $client = $this->createLdapClient();
        $client->connect();

        $context =$client->fetchUserEntryContext('user5', ['dn', 'questions']);
        $this->assertFalse($client->checkQuestionAnswer('user5', 'ice', 'rhum', $context));

        $client->changeQuestion($context['user_dn'], 'ice', 'rhum');

        $context = $client->fetchUserEntryContext('user5', ['dn', 'questions']);
        $this->assertTrue($client->checkQuestionAnswer('user5', 'ice', 'rhum', $context));

        // reset to default
        $client->changeQuestion($context['user_dn'], 'ice', 'vanilla');
    }

    public function testChangePasswordClear()
    {
        $client = $this->createLdapClient();

        $client->connect();

        $accountDn = 'uid=user2,ou=People,dc=example,dc=com';
        $client->changePassword($accountDn, 'password2', '');

        // Default Scheme is SSHA, clear -> SSHA by server
        $this->assertDirectoryAccountPasswordScheme($client, $accountDn, 'SSHA');
    }

    public function testChangePasswordAuto()
    {
        $client = $this->createLdapClient([
            'hash' => 'smd5',
        ]);

        $client->connect();

        $accountDn = 'uid=user2,ou=People,dc=example,dc=com';

        $client->changePassword($accountDn, 'mysmd5', '');

        $this->assertDirectoryAccountPasswordScheme($client, $accountDn, 'SMD5');
        $this->assertDirectoryAccountPasswordNotSame($client, $accountDn, 'notmypass');
        $this->assertDirectoryAccountPasswordSame($client, $accountDn, 'mysmd5');

        $client = $this->createLdapClient([
            'hash' => 'auto',
        ]);

        $client->connect();

        $client->changePassword($accountDn, 'password2', '');

        $this->assertDirectoryAccountPasswordScheme($client, $accountDn, 'SMD5');
        $this->assertDirectoryAccountPasswordNotSame($client, $accountDn, 'mysmd5');
        $this->assertDirectoryAccountPasswordSame($client, $accountDn, 'password2');
    }

    /**
     * @dataProvider hashProvider
     */
    public function testChangePasswordHash($scheme, $password)
    {
        $client = $this->createLdapClient([
            'hash' => $scheme,
        ]);

        $client->connect();

        $accountDn = 'uid=user2,ou=People,dc=example,dc=com';
        $client->changePassword($accountDn, $password, '');

        $this->assertDirectoryAccountPasswordScheme($client, $accountDn, $scheme);
        $this->assertDirectoryAccountPasswordNotSame($client, $accountDn, 'notmypass');
        $this->assertDirectoryAccountPasswordSame($client, $accountDn, $password);
    }

    public function hashProvider()
    {
        return [
            ['SHA', 'passwordsha'],
            ['SHA256', 'passwordsha256'],
            ['SHA384', 'passwordsha384'],
            ['SHA512', 'passwordsha512'],
            ['SSHA', 'passwordssha'],
            ['SSHA256', 'passwordssha256'],
            ['SSHA384', 'passwordssha384'],
            ['SSHA512', 'passwordssha512'],
            ['MD5', 'passwordmd5'],
            ['SMD5', 'passwordsmd5'],
        ];
    }

    public function testChangePasswordShadowMode()
    {

    }

    public function testChangePasswordSambaModeBasics()
    {
        $client = $this->createLdapClient([
            'samba_mode' => true,
        ]);
        $client->connect();

        $password = 'mysambapassword';
        $accountDn = 'uid=user2,ou=People,dc=example,dc=com';
        $time = time();
        $badTime = $time + 50;
        $client->changePassword($accountDn, $password, '');
        // praying that the changePassword was executed in less that a second
        //TODO rigor for time sensitive tests
        $this->assertDirectoryObjectAttributeValueSame($client, $accountDn, 'sambaPwdLastSet', (string) $time);
        $this->assertDirectoryObjectAttributeValueNotSame($client, $accountDn, 'sambaPwdLastSet', (string) $badTime);

        $expectedPassword = strtoupper(hash('md4', iconv('UTF-8', 'UTF-16LE', $password)));
        $this->assertDirectoryObjectAttributeValueSame($client, $accountDn, 'sambaNTPassword', $expectedPassword);
    }

    public function testChangePasswordSambaEnabledButAccountNotSamba()
    {
        $client = $this->createLdapClient([
            'samba_mode' => true,
        ]);
        $client->connect();

        $context = $client->fetchUserEntryContext('user3', ['samba']);

        $password = 'mysambapassword';
        $accountDn = 'uid=user3,ou=People,dc=example,dc=com';

        $this->assertDirectoryObjectAttributeNotPresent($client, $accountDn, 'sambaPwdLastSet');

        $client->changePassword($accountDn, $password, '', $context);

        $this->assertDirectoryObjectAttributeNotPresent($client, $accountDn, 'sambaPwdLastSet');
    }

    public function testChangeSshKey()
    {
        $client = $this->createLdapClient();
        $client->connect();
        $sshKey1 = 'new sshkey1';
        $sshKey2 = 'new sshkey2';
        $attribute = 'sshPublicKey';
        $accountDn = 'uid=user4,ou=People,dc=example,dc=com';

        $client->changeSshKey($accountDn, $sshKey1);
        $this->assertDirectoryObjectAttributeValueSame($client, $accountDn, $attribute, $sshKey1);
        $this->assertDirectoryObjectAttributeValueNotSame($client, $accountDn, $attribute, $sshKey2);
        $client->changeSshKey($accountDn, $sshKey2);
        $this->assertDirectoryObjectAttributeValueNotSame($client, $accountDn, $attribute, $sshKey1);
        $this->assertDirectoryObjectAttributeValueSame($client, $accountDn, $attribute, $sshKey2);
    }

    private function assertDirectoryAccountPasswordSame(LdapClient $client, $dn, $expected)
    {
        $attribute = 'userPassword';
        $connection = $client->getConnection();

        $searchUserPassword = ldap_read($connection, $dn, '(objectClass=*)', [$attribute]);
        $values = ldap_get_values($connection, ldap_first_entry($connection, $searchUserPassword), $attribute);
        $passwordVerifier = new PasswordVerifier(new PasswordEncoder([]));
        $this->assertTrue($passwordVerifier->verify($expected, $values[0]), "Password in directory {$values[0]} was not $expected");
    }

    private function createLdapClient($options = [])
    {
        $passwordEncoder = new PasswordEncoder([]);
        $ldapUrl = 'ldap://localhost:10389';
        $ldapUseTls = isset($options['use_tls']) ? $options['use_tls'] : false;
        $ldapBindDn = isset($options['ldap_bind_dn']) ? $options['ldap_bind_dn'] : 'uid=admin,ou=system';
        $ldapBindPw = 'secret';
        $whoChangePassword = 'user';
        $adMode = false;
        $ldapFilter = isset($options['ldap_filter']) ? $options['ldap_filter'] : '(&(objectClass=person)(uid={login}))';
        $ldapBase = isset($options['ldap_base']) ? $options['ldap_base'] : 'ou=People,dc=example,dc=com';
        $hash = isset($options['hash']) ? $options['hash'] : 'clear';
        $smsAttribute = 'telephoneNumber';
        $answerObjectClass = "extensibleObject";
        $answerAttribute = 'info';
        $whoChangeSshKey = 'user';
        $sshKeyAttribute = 'sshPublicKey';
        $mailAttribute = 'mail';
        $fullnameAttribute = 'cn';
        $adOptions = [];
        $sambaMode = isset($options['samba_mode']) ? $options['samba_mode'] : false;
        $sambaOptions = isset($options['samba_options']) ? $options['samba_options'] : [];
        $shadowOptions = [
            'update_shadowLastChange' => false,
            'update_shadowExpire' => false,
        ];
        $mailAddressUseLdap = false;

        $ldapClient = new LdapClient(
            $passwordEncoder,
            $ldapUrl,
            $ldapUseTls,
            $ldapBindDn,
            $ldapBindPw,
            $whoChangePassword,
            $adMode,
            $ldapFilter,
            $ldapBase,
            $hash,
            $smsAttribute,
            $answerObjectClass,
            $answerAttribute,
            $whoChangeSshKey,
            $sshKeyAttribute,
            $mailAttribute,
            $fullnameAttribute,
            $adOptions,
            $sambaMode,
            $sambaOptions,
            $shadowOptions,
            $mailAddressUseLdap
        );
        $ldapClient->setLogger(new NullLogger());

        return $ldapClient;
    }
}

