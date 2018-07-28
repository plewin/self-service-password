<?php

namespace App\Tests\Integration\ActiveDirectory;

use App\Exception\LdapErrorException;
use App\Exception\LdapInvalidUserCredentialsException;
use App\Ldap\Client;
use App\Ldap\ClientInterface;
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
        if ('true' === getenv('TRAVIS')) {
            $this->markTestSkipped('Cannot test Active Directory integration on Travis');
        }

        //ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    }

    /**
     * Test that we can connect to Active Directory
     */
    public function testConnect(): void
    {
        $client = $this->createLdapClient();

        // expect no exception
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());
    }

    public function testConnectWrongCredentials(): void
    {
        $client = $this->createLdapClient(['ldap_bind_dn' => 'bad_dn']);

        $this->expectException(LdapErrorException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());
    }

    public function testCheckOldPassword(): void
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
        $this->expectException(LdapInvalidUserCredentialsException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $client->checkOldPassword('badpassword1', $context);
    }

    /**
     * @param array $options
     * @return ClientInterface
     */
    private function createLdapClient(array $options = []): ClientInterface
    {
        $passwordEncoder = new PasswordEncoder([]);
        $ldapUrl = 'ldap://localhost:7389';
        $ldapUseTls = $options['use_tls'] ?? false;
        $ldapBindDn = $options['ldap_bind_dn'] ?? 'cn=ssp,ou=service,dc=example,dc=com';
        $ldapBindPw = 'Passw0rd!';
        $whoChangePassword = 'user';
        $adMode = false;
        $ldapFilter = $options['ldap_filter'] ?? '(&(objectClass=person)(uid={login}))';
        $ldapBase = $options['ldap_base'] ?? 'ou=People,dc=example,dc=com';
        $hash = $options['hash'] ?? 'clear';
        $smsAttribute = 'telephoneNumber';
        $answerObjectClass = 'extensibleObject';
        $answerAttribute = 'info';
        $whoChangeSshKey = 'user';
        $sshKeyAttribute = 'sshPublicKey';
        $mailAttribute = 'mail';
        $fullnameAttribute = 'cn';
        $adOptions = [];
        $sambaMode = $options['samba_mode'] ?? false;
        $sambaOptions = $options['samba_options'] ?? [];
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

