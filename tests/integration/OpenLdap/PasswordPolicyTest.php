<?php

namespace App\Tests\Integration\OpenLdap;

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
            $this->markTestSkipped('Cannot test Open Ldap integration on Travis');
        }

        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    }

    public function testUserCannotChangeOwnPassword()
    {
        $client = $this->createLdapClient();

        // expect no exception
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($client->connect());

        $newPassword = 'pass';

        $context = $client->fetchUserEntryContext('user10', ['dn']);
        $client->checkOldPassword('password10', $context);

        //ldap_mod_replace(): Modify: Insufficient access
        //ldap_error: Insufficient access
        //ldap_get_option: User alteration of password is not allowed

        try {
            var_dump($client->changePassword($context['user_dn'], $newPassword, '', $context));
        }
        catch (\Exception $e) {
            //ignore
        }
        echo "ldap_error: " . ldap_error($client->getConnection());
        ldap_get_option($client->getConnection(), LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
        echo "ldap_get_option: $err";
    }

    private function createLdapClient($options = [])
    {
        $passwordEncoder = new PasswordEncoder([]);
        $ldapUrl = 'ldap://localhost:8389';
        $ldapUseTls = isset($options['use_tls']) ? $options['use_tls'] : false;
        $ldapBindDn = isset($options['ldap_bind_dn']) ? $options['ldap_bind_dn'] : 'uid=ssp,ou=service,dc=nodomain';
        $ldapBindPw = 'password10';
        $whoChangePassword = 'user';
        $adMode = false;
        $ldapFilter = isset($options['ldap_filter']) ? $options['ldap_filter'] : '(&(objectClass=person)(uid={login}))';
        $ldapBase = isset($options['ldap_base']) ? $options['ldap_base'] : 'ou=People,dc=nodomain';
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

