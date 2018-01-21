<h1 align="center">
  <a href="#user-content-------self-service-password--"><img src="https://ltb-project.org/_media/ltb-logo.png" alt="Logo LDAP Tool box" width="120" height="120"></a>
  <br>
  Self-Service Password
  <br>
</h1>

<p align="center">
  <a href="https://ltb-project.org" alt="LDAP Tool Box"><img src="https://img.shields.io/badge/A%20project%20-LDAP%20Toolbox-7ef80b.svg" /></a>
  <a href="https://bestpractices.coreinfrastructure.org/projects/372" alt="CII Best Practices"><img src="https://bestpractices.coreinfrastructure.org/projects/372/badge" /></a>
  <a href="https://travis-ci.org/ltb-project/self-service-password" alt="Build Status"><img src="https://travis-ci.org/ltb-project/self-service-password.svg?branch=master" /></a>
  <a href="https://github.com/ltb-project/self-service-password/blob/master/LICENCE" alt="GPL License"><img src="https://img.shields.io/github/license/ltb-project/self-service-password.svg" /></a>
</p>

<p align="center">
  <a href="https://secure.php.net/manual/en/intro-whatis.php" alt="PHP 5.4"><img src="https://img.shields.io/badge/PHP-^5.4-787cb4.svg" /></a>
  <a href="https://symfony.com/what-is-symfony" alt="Symfony 2.8"><img src="https://img.shields.io/badge/Symfony-2.8-7aba20.svg" /></a>
  <a href="https://getbootstrap.com/docs/3.3/" alt="Bootstrap 3.3"><img src="https://img.shields.io/badge/Bootstrap-3.3-5f4586.svg" /></a>
  <a href="https://jquery.com" alt="jQuery 3.2"><img src="https://img.shields.io/badge/jQuery-3.2-0769ad.svg" /></a>
  <a href="https://www.npmjs.com/package/@symfony/webpack-encore" alt="Webpack Encore 0.9"><img src="https://img.shields.io/badge/Webpack%20Encore-0.9-2b3a42.svg" /></a>
</p>


<p align="center"><b>Self-Service Password is a web application that allows users to change or reset their password in any LDAP directory.</b></p>

<p align="center">
  this project is part of <a href="https://ltb-project.org">LDAP Toolbox</a> 🛠️<br />
  a compilation of tools for LDAP administrators
</p>

<p align="center">
  <a href="#key-features">Key Features</a> •
  <a href="#demo">Demo</a> •
  <a href="#installation">Installation</a> •
  <a href="#documentation">Documentation</a>
</p>

## Key Features

* Support any LDAPv3 directory (OpenLDAP, ApacheDS, 389DS, generic LDAP, etc) including Active Directory
* Samba mode to change Samba passwords
* Local password policy:
  * Minimum/maximum length
  * Forbidden characters
  * Upper, Lower, Digit or Special characters counters
  * Reuse old password check
  * Password same as login
  * Complexity (different class of characters)
* Help messages
* Reset by questions
* Reset by mail challenge (token sent by mail)
* Reset by SMS (trough external Email 2 SMS service or SMS API)
* Change SSH Key in LDAP directory
* reCAPTCHA (Google API)
* Mail notification after password change
* Hook script after password change

## Demo

Soon

## Installation

* PHP extensions required:
  * php-ldap
  * php-gd2 (if using gregwar captcha)
* strong cryptography functions available (for random_compat, php 7 or libsodium or /dev/urandom readable or php-mcrypt extension installed)
* valid PHP mail server configuration (reset mail)
* valid PHP session configuration (reset mail)

Tarballs and packages for Debian and Red Hat are available on http://ltb-project.org/wiki/download#self_service_password


## Documentation

Documentation is available on http://ltb-project.org/wiki/documentation/self-service-password