# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.define "test-389ds" do |ds389|
    # Minimal centos 7 with virtualbox guest additions
    ds389.vm.box = "geerlingguy/centos7"
    ds389.vm.network "forwarded_port", guest: 389, host: 10389
  end

  config.vm.define "test-samba4" do |ds389|
    # Minimal centos 7 with virtualbox guest additions
    ds389.vm.box = "geerlingguy/centos7"
    ds389.vm.network "forwarded_port", guest: 389, host: 11389
  end

  config.vm.define "test-apacheds" do |apacheds|
    apacheds.vm.box = "ubuntu/xenial64"
    apacheds.vm.network "forwarded_port", guest: 10389, host: 9389

    apacheds.vm.provision "install apacheds", :type => :shell, path: "./tests/_data/ApacheDirectoryServer/install.sh"
  end

  config.vm.define "test-openldap" do |openldap|
    openldap.vm.box = "ubuntu/xenial64"
    openldap.vm.network "forwarded_port", guest: 389, host: 8389

    openldap.vm.provision "install openldap", :type => :shell, path: "./tests/_data/OpenLDAP/install.sh"
  end

  config.vm.define "test-adlds-2012r2" do |ad|
    ad.vm.box = "opentable/win-2012r2-standard-amd64-nocm"
    ad.vm.box_version = "1.0"
    ad.vm.guest = :windows

    ad.winrm.username = "Administrator"
    ad.winrm.password = "vagrant"

    ad.vm.network "forwarded_port", guest: 389, host: 7389

    ad.windows.set_work_network = true

    ad.vm.provision "install ad lds", :type => :shell, path: "./tests/_data/ActiveDirectory/install_ad_lds.cmd"
  end
end