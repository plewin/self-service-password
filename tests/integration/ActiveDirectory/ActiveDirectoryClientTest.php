<?php

namespace App\Tests\Integration\ActiveDirectory;

use App\Ldap\Client;
use App\Tests\Integration\LdapIntegrationTestCase;
use App\Utils\PasswordEncoder;
use Psr\Log\NullLogger;

/**
 * Class ActiveDirectoryClientTest
 */
class ActiveDirectoryClientTest extends LdapIntegrationTestCase
{
    protected function setUp()
    {
        if (getenv('TRAVIS') == 'true') {
            $this->markTestSkipped('Cannot test Active Directory integration on Travis');
        }

        //ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    }

    /**
     * Test that we can connect to Active Directory
     */
    public function testConnect()
    {
        $client = $this->createLdapClient();

        // expect no exception
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());
    }

    public function testConnectWrongCredentials()
    {
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
            'user_dn' => 'cn=user1,ou=People,dc=example,dc=com',
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $client->checkOldPassword('Passw0rd!', $context);

        // now we expect the next one to throw an exception
        $this->setExpectedException('App\Exception\LdapInvalidUserCredentialsException');
        /** @noinspection PhpUnhandledExceptionInspection */
        $client->checkOldPassword('badpassword1', $context);
    }

    /**
     * @param array $options
     * @return Client
     */
    private function createLdapClient($options = [])
    {
        $passwordEncoder = new PasswordEncoder([]);
        $ldapUrl = 'ldap://localhost:7389';
        $ldapUseTls = isset($options['use_tls']) ? $options['use_tls'] : false;
        $ldapBindDn = isset($options['ldap_bind_dn']) ? $options['ldap_bind_dn'] : 'cn=ssp,ou=service,dc=example,dc=com';
        $ldapBindPw = 'Passw0rd!';
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

        $ldapClient = new Client(
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

