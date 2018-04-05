#!/usr/bin/env bash
sudo yum update -y

# https://www.howtoforge.com/tutorial/samba-4-with-active-directory-on-centos-7-rpm-based-installation-with-share-support/

sudo yum install -y epel-release

sudo yum install -y wget authconfig krb5-workstation

cd /etc/yum.repos.d/
wget http://wing-net.ddo.jp/wing/7/EL7.wing.repo

sed -i 's@enabled=0@enabled=1@g' /etc/yum.repos.d/EL7.wing.repo

sudo yum remove -y samba-common
rm /etc/krb5.conf

#TODO de we need all of this ?
sudo yum install -y samba46 samba46-winbind-clients samba46-winbind samba46-client samba46-dc samba46-pidl samba46-python samba46-winbind-krb5-locator perl-Parse-Yapp perl-Test-Base python2-crypto samba46-common-tool

sudo samba-tool domain provision --use-rfc2307 --interactive

