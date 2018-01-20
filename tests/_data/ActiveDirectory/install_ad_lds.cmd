Enable-WindowsOptionalFeature -FeatureName "DirectoryServices-ADAM" -Online
c://Windows/ADAM/adaminstall.exe /answer:c:/vagrant/tests/_data/ActiveDirectory/ADAMInstall.txt
ldifde -i -f "c:/vagrant/tests/_data/ActiveDirectory/99-purge.ldif" -s "localhost" -h -k -z
ldifde -i -f "c:/vagrant/tests/_data/ActiveDirectory/20-service.ldif" -s "localhost" -h
ldifde -i -f "c:/vagrant/tests/_data/ActiveDirectory/30-service.ldif" -s "localhost" -h