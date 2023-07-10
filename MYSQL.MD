
## MYSQL 8 LOCAL DEV ENVIRONMENT INSTALLATION

https://www.digitalocean.com/community/tutorials/how-to-install-mysql-on-ubuntu-22-04

```sh
sudo apt update
sudo apt-get remove --purge mysql*
sudo apt-get autoremove
sudo apt-get autoclean
sudo apt install mysql-server
sudo systemctl enable mysql
sudo mysql_secure_installation
```

### For local development
 
```sh
CREATE USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
flush privileges;

CREATE USER 'admin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
flush privileges;

CREATE USER 'admin'@'192.168.75.1' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'192.168.75.1' WITH GRANT OPTION;
flush privileges;
```

### For prod server

```sh
CREATE USER 'admin'@'46.2.244.250' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'46.2.244.250' WITH GRANT OPTION;
flush privileges;
```

### Show mysql users

```sh
SELECT User, Host FROM mysql.user;

+------------------+----------------+
| User             | Host           |
+------------------+----------------+
| omega            | 213.194.73.202 |
| omega            | 31.177.240.250 |
| debian-sys-maint | localhost      |
| mysql.infoschema | localhost      |
| mysql.session    | localhost      |
| mysql.sys        | localhost      |
| omega            | localhost      |
| omega_test       | localhost      |
| root             | localhost      |
+------------------+----------------+
```

Show Grants For

```
SELECT CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') FROM mysql.user;

+------------------------------------------------------+
| CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') |
+------------------------------------------------------+
| SHOW GRANTS FOR 'omega'@'213.194.73.202';            |
| SHOW GRANTS FOR 'debian-sys-maint'@'localhost';      |
| SHOW GRANTS FOR 'mysql.infoschema'@'localhost';      |
| SHOW GRANTS FOR 'mysql.session'@'localhost';         |
| SHOW GRANTS FOR 'mysql.sys'@'localhost';             |
| SHOW GRANTS FOR 'omega'@'localhost';                 |
| SHOW GRANTS FOR 'omega_test'@'localhost';            |
| SHOW GRANTS FOR 'root'@'localhost';                  |
+------------------------------------------------------+
```

### MYSQL Server 8 Remote Access Configruation

/etc/mysql/mysql.conf.d/mysqld.cnf   dosyasına

bind_address = 0.0.0.0

character-set-server = utf8
init-connect='SET NAMES utf8'
collation-server=utf8_general_ci

### For Production

mysql > 

CREATE DATABASE aninda_doktor_test;
CREATE USER 'omega'@'213.194.73.202' IDENTIFIED WITH mysql_native_password BY '{<y2,nwk+>67P5XP';
GRANT ALL PRIVILEGES ON aninda_doktor_test.* TO omega@213.194.73.202 WITH GRANT OPTION;

FLUSH PRIVILEGES;

Not: 213.194.73.202 ofisten bağlanacağımız pc lerin statik ip adresi.

Kullanıcı listelemek;
SELECT CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') FROM mysql.user;

Kullanıcı silmek;
DROP USER 'omega'@'188.166.34.93';

https://www.configserverfirewall.com/ubuntu-linux/enable-mysql-remote-access-ubuntu/

Selinux disabled olmalı yada izin verilmiş olmalı.

Group by ı aktif etmek;
SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
