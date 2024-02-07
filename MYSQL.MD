
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

### Enable/Disable Full Group By Mode

https://stackoverflow.com/questions/23921117/disable-only-full-group-by

```sql
SET PERSIST sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
```

## For local server
 
```sh
CREATE USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'your_password';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
flush privileges;

CREATE USER 'admin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
flush privileges;

// client ip: local ip address which database software use it in your machine
//
CREATE USER 'admin'@'192.168.75.1' IDENTIFIED WITH mysql_native_password BY 'your_password';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'192.168.75.1' WITH GRANT OPTION;
flush privileges;
```

### mysqld.conf for LOCAL

```sh
bind-address            = 0.0.0.0
mysqlx-bind-address     = 127.0.0.1

character-set-server = utf8
init-connect='SET NAMES utf8'
collation-server=utf8_general_ci
#
# * Fine Tuning
#
key_buffer_size         = 16M
```

## For prod server

```sh
CREATE USER 'admin'@'79.2.244.xxx' IDENTIFIED WITH mysql_native_password BY 'your_password';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'79.2.244.xxx' WITH GRANT OPTION;
flush privileges;
```

### Show mysql users

```sh
SELECT User, Host FROM mysql.user;

+------------------+----------------+
| User             | Host           |
+------------------+----------------+
| admin            | 213.185.73.xxx |
| admin            | 31.177.240.xxx |
| debian-sys-maint | localhost      |
| mysql.infoschema | localhost      |
| mysql.session    | localhost      |
| mysql.sys        | localhost      |
| root             | localhost      |
+------------------+----------------+
```

Show Grants For

```
SELECT CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') FROM mysql.user;

+------------------------------------------------------+
| CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') |
+------------------------------------------------------+
| SHOW GRANTS FOR 'test'@'213.185.73.xxx';             |
| SHOW GRANTS FOR 'debian-sys-maint'@'localhost';      |
| SHOW GRANTS FOR 'mysql.infoschema'@'localhost';      |
| SHOW GRANTS FOR 'mysql.session'@'localhost';         |
| SHOW GRANTS FOR 'mysql.sys'@'localhost';             |
| SHOW GRANTS FOR 'test'@'localhost';                  |
| SHOW GRANTS FOR 'root'@'localhost';                  |
+------------------------------------------------------+
```

Shor Privileges

```
SHOW GRANTS FOR 'test'@'213.185.73.xxx';
```

### MYSQL Server 8 Remote Access Configruation

https://www.configserverfirewall.com/ubuntu-linux/enable-mysql-remote-access-ubuntu/

```sh
vim /etc/mysql/mysql.conf.d/mysqld.cnf
```

```sh
bind_address = 0.0.0.0

character-set-server = utf8
init-connect='SET NAMES utf8'
collation-server=utf8_general_ci
```

### For Production

mysql > 

CREATE DATABASE db_name;
CREATE USER 'admin'@'213.185.73.xxx' IDENTIFIED WITH mysql_native_password BY 'your_password';
GRANT ALL PRIVILEGES ON db_name.* TO admin@213.185.73.xxx WITH GRANT OPTION;

FLUSH PRIVILEGES;

> Note: 213.185.73.183 is static ip address of your location.

### List Users;

SELECT CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') FROM mysql.user;

### Delete User;

```sql
DROP USER 'admin'@'213.185.73.xxx';
```
