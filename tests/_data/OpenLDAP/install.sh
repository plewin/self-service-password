#!/usr/bin/env bash

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install slapd ldap-utils

sudo ldapadd -Y EXTERNAL -H ldapi:// -f /vagrant/tests/_data/OpenLDAP/00-install-admin.ldif
sudo ldapadd -Y EXTERNAL -H ldapi:// -f /vagrant/tests/_data/OpenLDAP/01-load-ppolicy.ldif
sudo ldapadd -Y EXTERNAL -H ldapi:// -f /etc/ldap/schema/ppolicy.ldif
sudo ldapadd -Y EXTERNAL -H ldapi:// -f /vagrant/tests/_data/OpenLDAP/02-policy-config.ldif

sudo /etc/init.d/slapd restart

sudo ldapadd -Y EXTERNAL -H ldapi:// -f /vagrant/tests/_data/OpenLDAP/10-policies.ldif
sudo ldapadd -Y EXTERNAL -H ldapi:// -f /vagrant/tests/_data/OpenLDAP/20-service.ldif
sudo ldapadd -Y EXTERNAL -H ldapi:// -f /vagrant/tests/_data/OpenLDAP/21-people.ldif