<?php

namespace App\Tests\Integration\ApacheDirectoryServer;

use App\Ldap\Client;
use App\Ldap\ClientInterface;
use App\Tests\Integration\LdapIntegrationTestCase;
use App\Utils\PasswordEncoder;
use Psr\Log\NullLogger;

/**
 * Class ApacheDirectoryServerPasswordPolicyTest
 */

class ApacheDirectoryServerPasswordPolicyTest extends LdapIntegrationTestCase
{
    protected function setUp()
    {
        if ('true' === getenv('TRAVIS')) {
            $this->markTestSkipped('Cannot test Apache Directory Server integration on Travis');
        }
    }

    public function testUserCannotChangeOwnPassword(): void
    {
        $userDn = 'uid=user10,ou=People,dc=example,dc=com';

        $client = $this->createLdapClient([
            'ldap_bind_dn' => $userDn,
            'ldap_bind_pw' => 'password10',
        ]);

        // expect no exception
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());


        $newPassword = 'shouldberejected';
        //TODO
        //$client->changePassword($userDn, $newPassword, '');
    }

    private function createLdapClient(array $options = []): ClientInterface
    {
        $passwordEncoder = new PasswordEncoder([]);
        $ldapUrl = 'ldap://localhost:9389';
        $ldapUseTls = $options['use_tls'] ?? false;
        $ldapBindDn = $options['ldap_bind_dn'] ?? 'uid=admin,ou=system';
        $ldapBindPw = $options['ldap_bind_pw'] ?? 'secret';
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

