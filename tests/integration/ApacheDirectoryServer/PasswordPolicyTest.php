<?php

namespace App\Tests\Integration\ApacheDirectoryServer;

use App\Ldap\Client;
use App\Tests\Integration\LdapIntegrationTestCase;
use App\Utils\PasswordEncoder;
use Psr\Log\NullLogger;

/**
 * Class PasswordPolicyTest
 */

class PasswordPolicyTest extends LdapIntegrationTestCase
{
    protected function setUp()
    {
        if (getenv('TRAVIS') == 'true') {
            $this->markTestSkipped('Cannot test Apache Directory Server integration on Travis');
        }
    }

    public function testUserCannotChangeOwnPassword()
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

    private function createLdapClient($options = [])
    {
        $passwordEncoder = new PasswordEncoder([]);
        $ldapUrl = 'ldap://localhost:10389';
        $ldapUseTls = isset($options['use_tls']) ? $options['use_tls'] : false;
        $ldapBindDn = isset($options['ldap_bind_dn']) ? $options['ldap_bind_dn'] : 'uid=admin,ou=system';
        $ldapBindPw = isset($options['ldap_bind_pw']) ? $options['ldap_bind_pw'] : 'secret';
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

