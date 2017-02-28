#!/usr/bin/env bash
     passwd -d -u ubuntu
     chage -d0 ubuntu
	 
	 apt-get update
apt-get install -y apache2 php libapache2-mod-php php-mcrypt
if ! [ -L /var/www ]; then
  rm -rf /var/www
  ln -fs /vagrant /var/www
fi


