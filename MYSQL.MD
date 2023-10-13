
## MYSQL CheatSheet

## MYSQL 8 LOCAL-DEV INSTALLATION

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

### For local development
 
```sh
CREATE USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'tTxKry84@';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
flush privileges;

CREATE USER 'admin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'tTxKry84@';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
flush privileges;

CREATE USER 'admin'@'192.168.75.1' IDENTIFIED WITH mysql_native_password BY 'tTxKry84@';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'192.168.75.1' WITH GRANT OPTION;
flush privileges;
```

### For prod server

```sh
CREATE USER 'admin'@'213.194.88.202' IDENTIFIED WITH mysql_native_password BY 'tTxKry84@';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'213.194.88.202' WITH GRANT OPTION;
flush privileges;
```

### Show mysql users

```sh
SELECT User, Host FROM mysql.user;

+------------------+----------------+
| User             | Host           |
+------------------+----------------+
| demo            | 213.194.73.202 |
| demo            | 31.177.240.250 |
| debian-sys-maint | localhost      |
| mysql.infoschema | localhost      |
| mysql.session    | localhost      |
| mysql.sys        | localhost      |
| demo            | localhost      |
| demo_test       | localhost      |
| root             | localhost      |
+------------------+----------------+
```

Show Grants For

```
SELECT CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') FROM mysql.user;

+------------------------------------------------------+
| CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') |
+------------------------------------------------------+
| SHOW GRANTS FOR 'demo'@'213.194.88.202';            |
| SHOW GRANTS FOR 'debian-sys-maint'@'localhost';      |
| SHOW GRANTS FOR 'mysql.infoschema'@'localhost';      |
| SHOW GRANTS FOR 'mysql.session'@'localhost';         |
| SHOW GRANTS FOR 'mysql.sys'@'localhost';             |
| SHOW GRANTS FOR 'demo'@'localhost';                 |
| SHOW GRANTS FOR 'demo_test'@'localhost';            |
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
CREATE USER 'demo'@'213.194.88.202' IDENTIFIED WITH mysql_native_password BY '{<y2,nwk+>67P5XP';
GRANT ALL PRIVILEGES ON test.* TO demo@213.194.88.202 WITH GRANT OPTION;

FLUSH PRIVILEGES;

Example static ip address:

Kullanıcı listelemek;
SELECT CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') FROM mysql.user;

Droppin user;

DROP USER 'demo'@'188.166.34.93';

### Selinux

https://www.configserverfirewall.com/ubuntu-linux/enable-mysql-remote-access-ubuntu/

