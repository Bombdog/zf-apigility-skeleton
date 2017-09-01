#!/bin/sh

# If you would like to do some extra provisioning you may
# add any commands you wish to this file and they will
# be run after the Homestead machine is provisioned.

file="/usr/bin/mongod"
if [ -f "$file" ]
then
	echo "Mongo is already installed"
else
	echo "Mongodb: $file not found. Installing mongo and the mongodb pecl module"
	sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
    echo "deb [ arch=amd64,arm64 ] http://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.4.list
    sudo apt-get update
    sudo apt-get install -y pkg-config
    sudo apt-get install -y mongodb-org
    sudo service mongod start
    sudo pecl install mongodb

    # test this...
    touch /etc/php/7.1/mods-available/mongodb.ini
    sudo echo "extension=mongodb.so" > "/etc/php/7.1/mods-available/mongodb.ini"
    sudo ln -s /etc/php/7.1/mods-available/mongodb.ini /etc/php/7.1/fpm/conf.d/20-mongodb.ini
fi
