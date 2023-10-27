
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

### For local development
 
```sh
CREATE USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
flush privileges;

CREATE USER 'admin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
flush privileges;

// mysql client ip adresi bağlanmaya çalışınca uyarı veren ip
CREATE USER 'admin'@'192.168.75.1' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
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
| admin            | 213.194.73.183 |
| admin            | 31.177.240.250 |
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
| SHOW GRANTS FOR 'omega'@'213.194.73.183';            |
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

CREATE DATABASE db_name;
CREATE USER 'admin'@'213.194.73.183' IDENTIFIED WITH mysql_native_password BY '{<y2,nwk+>67P5XP';
GRANT ALL PRIVILEGES ON db_name.* TO admin@213.194.73.183 WITH GRANT OPTION;

FLUSH PRIVILEGES;

> Note: 213.194.73.183 is static ip address of your location.

Kullanıcı listelemek;
SELECT CONCAT('SHOW GRANTS FOR ''',user,'''@''',host,''';') FROM mysql.user;

Delete the user;

```sql
DROP USER 'admin'@'213.194.73.183';
```

https://www.configserverfirewall.com/ubuntu-linux/enable-mysql-remote-access-ubuntu/

To active Group by feature:

```sql
SET PERSIST sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
```
