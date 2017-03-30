<?php

require_once __DIR__ . '/../lib/vendor/defuse-crypto.phar';
require_once __DIR__ . '/../lib/functions.inc.php';

class HashingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test hashing functions
     */
    public function testHashingFunctions()
    {
        $password = "p4ssw0rd";
        $wrongpassword = "badpass";

        // MD5
        $hash = make_md5_password($password);
        $hash_details = user_password_analyzer($hash);

        $this->assertSame("MD5", $hash_details['scheme']);
        $this->assertFalse($hash_details['salt']);

        $this->assertSame($hash, make_md5_password($password));

        $this->assertTrue(ldap_password_verify($password, $hash));
        $this->assertFalse(ldap_password_verify($wrongpassword, $hash));

        // SMD5
        $hash = make_smd5_password($password);
        $hash_details = user_password_analyzer($hash);

        $this->assertSame("SMD5", $hash_details['scheme']);
        $this->assertNotFalse($hash_details['salt']);

        $this->assertSame($hash, make_smd5_password($password, $hash_details['salt']));
        $this->assertNotSame($hash, make_smd5_password($password, "wrongsalt"));

        $this->assertTrue(ldap_password_verify($password, $hash));
        $this->assertFalse(ldap_password_verify($wrongpassword, $hash));

        // SHA
        $hash = make_sha_password($password);
        $hash_details = user_password_analyzer($hash);

        $this->assertSame("SHA", $hash_details['scheme']);
        $this->assertFalse($hash_details['salt']);

        $this->assertSame($hash, make_sha_password($password));

        $this->assertTrue(ldap_password_verify($password, $hash));
        $this->assertFalse(ldap_password_verify($wrongpassword, $hash));

        // SSHA
        $hash = make_ssha_password($password);
        $hash_details = user_password_analyzer($hash);

        $this->assertSame("SSHA", $hash_details['scheme']);
        $this->assertNotFalse($hash_details['salt']);

        $this->assertSame($hash, make_ssha_password($password, $hash_details['salt']));
        $this->assertNotSame($hash, make_ssha_password($password, "wrongsalt"));

        $this->assertTrue(ldap_password_verify($password, $hash));
        $this->assertFalse(ldap_password_verify($wrongpassword, $hash));

        // SHA512
        $hash = make_sha512_password($password);
        $hash_details = user_password_analyzer($hash);

        $this->assertSame("SHA512", $hash_details['scheme']);
        $this->assertFalse($hash_details['salt']);

        $this->assertSame($hash, make_sha512_password($password));

        $this->assertTrue(ldap_password_verify($password, $hash));
        $this->assertFalse(ldap_password_verify($wrongpassword, $hash));

        // crypt
        $hash_options = array('crypt_salt_prefix' => '$6$32');
        $hash = make_crypt_password($password, $hash_options);
        $hash_details = user_password_analyzer($hash);

        $this->assertSame("CRYPT", $hash_details['scheme']);
        $this->assertNotSame($hash, make_crypt_password("bad password", $hash_options, $hash_details['salt']));
        $this->assertSame($hash, make_crypt_password($password, $hash_options, $hash_details['salt']));

        $this->assertTrue(ldap_password_verify($password, $hash));
        $this->assertFalse(ldap_password_verify($wrongpassword, $hash));
    }
}

