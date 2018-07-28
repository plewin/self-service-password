<?php

namespace App\Tests\Integration\OpenLdap;

use App\Ldap\Client;
use App\Ldap\ClientInterface;
use App\Tests\Integration\LdapIntegrationTestCase;
use App\Utils\PasswordEncoder;
use Psr\Log\NullLogger;

/**
 * Class OpenLdapClientTest
 */

class OpenLdapClientTest extends LdapIntegrationTestCase
{
    protected function setUp()
    {
        if ('true' === getenv('TRAVIS')) {
            $this->markTestSkipped('Cannot test Apache Directory Server integration on Travis');
        }

        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    }

    /**
     * Test that we can connect to Apache Directory Server
     */
    public function testConnect(): void
    {
        $client = $this->createLdapClient();

        // expect no exception
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());
    }


    /**
     * @param array $options
     * @return ClientInterface
     */
    private function createLdapClient(array $options = []): ClientInterface
    {
        $passwordEncoder = new PasswordEncoder([]);
        $ldapUrl = 'ldap://localhost:8389';
        $ldapUseTls = $options['use_tls'] ?? false;
        $ldapBindDn = $options['ldap_bind_dn'] ?? 'uid=ssp,ou=service,dc=nodomain';
        $ldapBindPw = 'password10';
        $whoChangePassword = 'user';
        $adMode = false;
        $ldapFilter = $options['ldap_filter'] ?? '(&(objectClass=person)(uid={login}))';
        $ldapBase = $options['ldap_base'] ?? 'ou=People,dc=nodomain';
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

