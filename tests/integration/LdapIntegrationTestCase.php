<?php

namespace App\Tests\Integration;

use App\Ldap\ClientInterface;
use App\Utils\PasswordEncoder;
use App\Utils\PasswordVerifier;

class LdapIntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    protected function assertDirectoryAccountPasswordNotSame(ClientInterface $client, $dn, $expected)
    {
        $attribute = 'userPassword';
        $connection = $client->getConnection();

        $searchUserPassword = ldap_read($connection, $dn, '(objectClass=*)', [$attribute]);
        $values = ldap_get_values($connection, ldap_first_entry($connection, $searchUserPassword), $attribute);
        $passwordVerifier = new PasswordVerifier(new PasswordEncoder([]));
        $this->assertFalse($passwordVerifier->verify($expected, $values[0]));
    }


    protected function assertDirectoryObjectAttributeNotPresent(ClientInterface $client, $dn, $attribute)
    {
        $connection = $client->getConnection();

        $searchUserPassword = @ldap_read($connection, $dn, '(objectClass=*)', [$attribute]);
        $values = @ldap_get_values($connection, ldap_first_entry($connection, $searchUserPassword), $attribute);

        $this->assertFalse($values);
    }

    protected function assertDirectoryObjectAttributeValueSame(ClientInterface $client, $dn, $attribute, $expected)
    {
        $connection = $client->getConnection();

        $searchUserPassword = ldap_read($connection, $dn, '(objectClass=*)', [$attribute]);
        $values = ldap_get_values($connection, ldap_first_entry($connection, $searchUserPassword), $attribute);

        if (is_array($expected)) {
            $this->assertSame($values, $expected);
        } else {
            $this->assertSame($values[0], $expected);
        }
    }

    protected function assertDirectoryObjectAttributeValueNotSame(ClientInterface $client, $dn, $attribute, $expected)
    {
        $connection = $client->getConnection();

        $searchUserPassword = ldap_read($connection, $dn, '(objectClass=*)', [$attribute]);
        $values = ldap_get_values($connection, ldap_first_entry($connection, $searchUserPassword), $attribute);

        if (is_array($expected)) {
            $this->assertNotSame($values, $expected);
        } else {
            $this->assertNotSame($values[0], $expected);
        }
    }

    protected function assertDirectoryAccountPasswordScheme(ClientInterface $client, $dn, $scheme)
    {
        $connection = $client->getConnection();

        $attribute = 'userPassword';
        $searchUserPassword = ldap_read($connection, $dn, '(objectClass=*)', [$attribute]);
        $values = ldap_get_values($connection, ldap_first_entry($connection, $searchUserPassword), $attribute);
        $hashedPassword = $values[0];
        $this->assertStringStartsWith('{'.$scheme.'}', $hashedPassword);

    }
}