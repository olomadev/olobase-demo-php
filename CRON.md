
### Install Cron on Ubuntu 

```sh
sudo apt install cron
```

### Enable and Start Cron Service

```sh
sudo systemctl enable cron
```

### Check Service Status

```sh
sudo systemctl status cron
```

### Start Cron Service

```sh
sudo systemctl start cron
```

## Cron Job Configurations

For Every 5 minutes

```sh
*/5 * * * * php /var/www/demo-php/bin/redis-listener.php  >> /dev/null 2>&1
```

<a href="https://crontab.guru/#*/5_*_*_*_*">https://crontab.guru/#*/5_*_*_*_*</a>


## List Cron Jobs Activiy Log

This allows you to isolate and display only the entries related to cron service activities.

```sh
grep CRON /var/log/syslog
```

## Create Crontab for Specific User

crontab -u www-data -e

```sh
* * * * * php /var/www/demo-php/bin/redis-listener.php  >> /dev/null 2>&1
* * * * * sleep 10; php /var/www/demo-php/bin/redis-listener.php prod  >> /dev/null 2>&1
* * * * * sleep 20; php /var/www/demo-php/bin/redis-listener.php prod  >> /dev/null 2>&1
* * * * * sleep 30; php /var/www/demo-php/bin/redis-listener.php prod  >> /dev/null 2>&1
* * * * * sleep 40; php /var/www/demo-php/bin/redis-listener.php prod  >> /dev/null 2>&1
* * * * * sleep 50; php /var/www/demo-php/bin/redis-listener.php prod  >> /dev/null 2>&1

```

### Delete Ubuntu Cron Job

If you want to delete a cron job on Linux, use the following command:

```sh
crontab -r
```

To delete a cron job of a specific user, use this command:

```sh
crontab -r -u <username>
```