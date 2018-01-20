#!/usr/bin/env bash

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install default-jre-headless ldap-utils

wget -c "http://apache.mirrors.ovh.net/ftp.apache.org/dist//directory/apacheds/dist/2.0.0-M24/apacheds-2.0.0-M24-amd64.deb"

sudo dpkg -i apacheds-2.0.0-M24-amd64.deb

sudo systemctl enable apacheds-2.0.0-M24-default

sudo /etc/init.d/apacheds-2.0.0-M24-default restart

ldapadd -h localhost -p 10389 -w secret -D "uid=admin,ou=system" -f /vagrant/tests/_data/ApacheDirectoryServer/02-enable-ldap-public-key.ldif
ldapadd -h localhost -p 10389 -w secret -D "uid=admin,ou=system" -f /vagrant/tests/_data/ApacheDirectoryServer/03-enable-samba.ldif
ldapadd -h localhost -p 10389 -w secret -D "uid=admin,ou=system" -f /vagrant/tests/_data/ApacheDirectoryServer/10-ppolicies.ldif
ldapadd -h localhost -p 10389 -w secret -D "uid=admin,ou=system" -f /vagrant/tests/_data/ApacheDirectoryServer/30-people.ldif
