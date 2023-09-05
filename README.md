
# Php demo api

Vuetify admin php demo application.


## Installation Instructions

* 
*
*

## Sharing VMWare Ubuntu Files with Windows

https://opensource.com/article/21/4/share-files-linux-windows
https://unix.stackexchange.com/questions/206309/how-to-create-a-samba-share-that-is-writable-from-windows-without-777-permission

```
sudo apt-get update
sudo apt-get instal samba
sudo systemctl enable smbd
```

If you have not system user create a one

```sh
adduser --system yourusername
```

Add ownership to user

```sh
chown -R yourusername /var/www
```

```
vim /etc/samba/smb.conf
service smbd restart
```

```
[www]
   comment = Home Directories
   path = /var/www
   browseable = yes
   writable = yes
   public = yes
   create mask = 0644
   directory mask = 0755
   force user = yourusername
```

Start samba service

```
service smbd restart
```

## How to Create Swagger.json

Use this command

### Xampp

```
$ vendor/bin/openapi c:/xampp/htdocs/va-demo-api -e vendor -o public/swagger/web/swagger.json
```

### Linux

```
$ vendor/bin/openapi /var/www/va-demo-api -e vendor -o public/swagger/web/swagger.json
```

<a href="https://medium.com/@tatianaensslin/how-to-add-swagger-ui-to-php-server-code-f1610c01dc03">https://medium.com/@tatianaensslin/how-to-add-swagger-ui-to-php-server-code-f1610c01dc03</a>

## Redis-Server & Php Redis Extension under the Ubuntu 20

<a href="https://tecadmin.net/install-redis-ubuntu-20-04//">https://tecadmin.net/install-redis-ubuntu-20-04/</a>

```
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo apt install php-redis
```

Remove all keys

```
redis-cli FLUSHALL
```

To set a password

```
https://stackoverflow.com/questions/7537905/how-to-set-password-for-redis
```

## Apache2

```
sudo a2enmod rewrite
```

vim /etc/apache2/apache2.conf

```
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
```

Create virtual host

```
cd /etc/apache2/sites-available
cp 00-default.conf va-demo-api.conf
vim va-demo-api.conf
```

Update config

```
<VirtualHost *:80>
        SetEnv "APP_ENV" "test"
        ServerAdmin webmaster@localhost
        ServerName api.anindadoktor.com
        DocumentRoot /var/www/va-demo-api/public
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Enable vhost and restart apache

```
a2ensite va-demo-api.conf
sudo service apache2 restart
sudo systemctl enable apache2
```

Default config for all sub domain alias

```
<VirtualHost *:80>
        SetEnv "APP_ENV" "test"
        ServerAdmin webmaster@localhost
        ServerName www.anindadoktor.com
        ServerAlias [^api|www].*.anindadoktor.com
        DocumentRoot /var/www/va-demo-api/public
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### Apache2 IP den girişi yasaklama

https://serverfault.com/questions/607137/restrict-direct-ip-access-to-website/1063315#1063315

### Sendmail

https://gist.github.com/adamstac/7462202

 echo "test message" | sendmail -v your@mail.com

## Composer

https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-20-04

#### How to Add Listen 8080 to Apache2 ports.conf

<a href="https://askubuntu.com/questions/338218/why-am-i-getting-permission-denied-make-sock-could-not-bind-to-address-when">https://askubuntu.com/questions/338218/why-am-i-getting-permission-denied-make-sock-could-not-bind-to-address-when</a>

## Php Installation

```
sudo apt install php libapache2-mod-php php-cli
sudo apt install php-common php-mysql php-xml php-curl php-json php-opcache php-mbstring php-intl php-gd php-zip
```

## Php 8.0 Composer Laminas Framework

```
composer install
```

## Redis Desktop Manager

Sign up and download free.

<a href="https://resp.app/">https://resp.app/</a>

## Mysql

Login

```
mysql -u root - p
```

Connection Test

```
mysql --host=localhost --user=omega --password='' {database-name}
```

Create new db user

```
CREATE USER 'username'@'localhost' IDENTIFIED BY 'd6uSa#VH7hK[[![p';
GRANT ALL PRIVILEGES ON *.* TO 'username'@'localhost';
veya

GRANT ALL PRIVILEGES ON va-demo-api_test.* TO username@localhost WITH GRANT OPTION;

SHOW GRANTS FOR 'username'@'localhost';
FLUSH PRIVILEGES;
```

adding user only for one db

```
GRANT ALL PRIVILEGES ON mydbname.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
```

Connection Test

mysql --host=10.34.232.3 --user=username --password=password


## Cron job

https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-ubuntu-1804

```
service cron status
```

```
sudo crontab -e 
```

https://crontab.guru/every-1-minutes

```
* * * * * php /var/www/va-demo-php/bin/notifications.php test > /dev/null 2>&1
```

Every 5 seconds

```
* * * * * sleep  0 ; php /var/www/project/bin/xls-parser.php local > /dev/null 2>&1
* * * * * sleep  5 ; php /var/www/project/bin/xls-parser.php local > /dev/null 2>&1
* * * * * sleep 10 ; php /var/www/project/bin/xls-parser.php local > /dev/null 2>&1
```

Files must be included by specifying the root path

```
require dirname(__DIR__).'/vendor/autoload.php';
```

cron job logs

```
grep -i cron /var/log/syslog|tail -2
```

## PHP-CS-Fixer

Install: 

```
https://github.com/FriendsOfPHP/PHP-CS-Fixer
```

To fix all codes: 

```
tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
```

Single file

```
tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src/App/src/Model/PriceModel.php  
```

Add to Sublime: 

```
https://gist.github.com/mvsneha/9f265c07523e13aa6571fe9dd9ef19fc
```

## Tests

All files

```
vendor/bin/phpunit
```

Single file test

```
vendor/bin/phpunit --filter AppointmentHandlerTest
````

## Listening Apache Error Logs

```
tail -n 10 -f /var/log/apache2/error.log
```

## Error Responses

### 404

Response: Status 404 Not Found

```
{
    "title": "Not Found",
    "type": "https://httpstatus.es/404",
    "status": 404,
    "error": "Cannot POST http://va-demo-api/user/create!"
}
```

### Exception

Response: Status 400 Bad Request

```
{
    "title": "Exception Class",
    "file": "App\\src\\Filter\\UserNewFilter.php",
    "line": "115",
    "type": "https://httpstatus.es/500",
    "status": 400,
    "error": "Detailed message",
    "trace": "debug string"
}
```

### Validation errors

Response: Status 400 Bad Request 

```
{
    "error": {
        "email": [
            "Value is required and can't be empty"
        ],
        "password": [
            "Value is required and can't be empty"
        ]
        "photo": [
            "file_id": [
                "Value is required and can't be empty"
            ],
            "file_name": [
                "Value is required and can't be empty"
            ]
        ]
    }
}
```

### Single error

```
{
    "error": {
         "General error",
    }
}
```

### Multiple error

```
{
    "error": {
         ["Error string"],
         ["Another error string"],
    }
}
```
