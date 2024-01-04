
# Php Demo

Php REST demo application.

```sh
git clone git@github.com:olomadev/demo-php.git
```

Install composer packages

```sh
/var/www/demo-php$ composer install
```

### Install Demo.sql

Create a database named <b>va_demo</b>. Import the demo.sql file which is located in your project root.

### Create a Host Name

Go to your host file

```sh
vim /etc/hosts
```

Give your name host to <b>demo-php.local</b>. 

```sh
127.0.0.1       localhost
127.0.0.1       demo-php.local

# The following lines are desirable for IPv6 capable hosts
::1     ip6-localhost ip6-loopback
fe00::0 ip6-localnet
ff00::0 ip6-mcastprefix
ff02::1 ip6-allnodes
ff02::2 ip6-allrouters

```

Don't forget this value defined in your <b>demo-vuetify</b> front-end project <b>.env.dev</b> file.

```sh
VITE_API_URL=http://demo-php.local/api
```

### How to Build Swagger Docs

```
$ /var/www/demo-php composer swagger
```

<a href="https://medium.com/@tatianaensslin/how-to-add-swagger-ui-to-php-server-code-f1610c01dc03">https://medium.com/@tatianaensslin/how-to-add-swagger-ui-to-php-server-code-f1610c01dc03</a>

## Apache2 Installation

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
cp 00-default.conf demo-php.local.conf
vim demo-php.local.conf
```

Update config

```
<VirtualHost *:80>
        SetEnv "APP_ENV" "local"
        ServerAdmin webmaster@localhost
        ServerName demo-php.local
        DocumentRoot /var/www/demo-php/public
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Enable vhost and restart apache

```
a2ensite demo-php.local.conf
sudo service apache2 restart
sudo systemctl enable apache2
```

## Php Installation

```
sudo apt install php libapache2-mod-php php-cli
sudo apt install php-common php-mysql php-xml php-curl php-json php-opcache php-mbstring php-intl php-gd php-zip
```

## Installation Redis-Server for Local

<a href="https://tecadmin.net/install-redis-ubuntu-20-04//">https://tecadmin.net/install-redis-ubuntu-20-04/</a>

```
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo apt install php-redis
sudo phpenmod redis
```

```sh
vim /etc/redis/redis.conf
bind 0.0.0.0
protected-mode no
```

Remove all keys

```
redis-cli FLUSHALL
```

To set a password

```
https://stackoverflow.com/questions/7537905/how-to-set-password-for-redis
```


## Installation Redis Desktop Manager

Sign up and download free.

<a href="https://resp.app/">https://resp.app/</a>

## Installation Of MySQL

https://www.digitalocean.com/community/tutorials/how-to-install-mysql-on-ubuntu-22-04

## Installation of Composer Packages

```
#var/www/demo-php$ composer install
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
