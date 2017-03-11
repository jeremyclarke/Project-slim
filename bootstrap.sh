apt-get update
apt-get install -y apache2 php libapache2-mod-php php-mcrypt php-mysql

if ! [ -L /var/www ]; then
  rm -rf /var/www
  ln -fs /vagrant /var/www
fi

a2enmod rewrite

service apache2 restart


