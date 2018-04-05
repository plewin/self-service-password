#!/usr/bin/env bash

sudo yum -y check-update
sudo yum -y install epel-release
sudo yum -y install 389-ds-base 389-admin

# 127.0.0.2   localdirserv.localdomain


sudo setup-ds-admin.pl --silent --file=/vagrant/tests/_data/DirectoryServer389/setup-ds.inf

sudo systemctl enable dirsrv@localdirserv
sudo systemctl start dirsrv@localdirserv


sudo systemctl restart dirsrv@localdirserv

cp /vagrant/tests/_data/SSL/* .