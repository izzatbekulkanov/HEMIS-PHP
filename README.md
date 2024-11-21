# HEMIS University Management System

This project uses the latest version 7.2 of PHP and Yii2 PHP framework. Therefore, you should use PHP features when writing code.

#### Happy coding!

Apache virtual host config
==========================

    <VirtualHost *:80>
        ServerName student.univer.uz
    
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/univer/frontend/web
    
        ErrorLog ${APACHE_LOG_DIR}/univer-error.log
        #CustomLog ${APACHE_LOG_DIR}/access.log combined
    
        <Directory /var/www/univer/frontend/web >
                Options FollowSymLinks
    
                AllowOverride All
                Order deny,allow
                allow from all
    
                Require all granted
        </Directory>
    </VirtualHost>
    
    <VirtualHost *:80>
            ServerName hemis.univer.uz
    
            ServerAdmin webmaster@localhost
            DocumentRoot /var/www/univer/backend/web
    
            ErrorLog ${APACHE_LOG_DIR}/univer-error.log
            #CustomLog ${APACHE_LOG_DIR}/access.log combined
    
            <Directory /var/www/univer/backend/web >
                    Options FollowSymLinks
    
                    AllowOverride All
                    Order deny,allow
                    allow from all
    
                    Require all granted
            </Directory>
            
            Alias /static /var/www/univer/static
                    
            <Directory /var/www/univer/static >
                    Options FollowSymLinks
        
                    AllowOverride All
                    Order deny,allow
                    allow from all
        
                    Require all granted
            </Directory>
    </VirtualHost>
    
/etc/hosts config
=================
    127.0.0.1       student.univer.uz
    127.0.0.1       backend.univer.uz
    
    
    
Yii 2 Advanced Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Build Status](https://travis-ci.com/yiisoft/yii2-app-advanced.svg?branch=master)](https://travis-ci.com/yiisoft/yii2-app-advanced)

DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
#PHP 7.2 settings
Edit ```/etc/php/7.2/apache2/php.ini``` file and change some settings as given below:
```
short_open_tag = on 
max_execution_time = 300
upload_max_filesize = 4M
```
#Install PostgreSql and PgAdmin on Debian 9/10

```
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" |sudo tee  /etc/apt/sources.list.d/pgdg.list
sudo apt update
sudo apt -y install postgresql-12 postgresql-client-12
sudo apt -y install pgadmin4 pgadmin4-apache2
sudo su - postgres
psql -c "alter user postgres with password 'StrongAdminP@ssw0rd'"
psql

postgres=# \conninfo
postgres=# CREATE DATABASE hemis;
postgres=# CREATE USER hemis WITH ENCRYPTED PASSWORD 'hemis@2020';
postgres=# GRANT ALL PRIVILEGES ON DATABASE hemis to hemis;
postgres=# \l
postgres-# \c hemis
postgres-# \q
```
#Configure auto-backup script

```
#switch to postgresql user
su - postgresql

#edit cron
crontab -e

#add cron entry, first parametr of backup.sh script is database name, cron runs the job every 12 hours
00 */12 * * * /var/www/univer/backups/backup.sh hemis

```
#Initialize project

Choose Production mode while initializing the project
```
#install vendor dependencies via composer
composer update

#install bower assets via bower
bower install

#initialize project with development mode
./yii init
```

#Configure environment

Creat or edit `.env` file to configure database credentials and other settings. 
See example configuration below:
```
YII_DEBUG   = false
YII_ENV     = prod

HEMIS_ENDPOINT      = http://ministry.hemis.uz/app/rest/
HEMIS_INTEGRATION   = true

DB_DSN           = pgsql:host=localhost;port=5433;dbname=hemis
DB_USERNAME      = hemis
DB_PASSWORD      = hemis@2020

RECAPTCHA_ENABLE    = 1
RECAPTCHA_KEY       = 6LebAMEZAAAAAG82WTPVxpcQegmzVAb73oxISPoK
RECAPTCHA_SECRET    = 6LebAMEZAAAAADgz9MhwFixPS5RHxNl-i2KfKFkU

FRONTEND_URL    = http://student.univer.uz/
BACKEND_URL     = http://hemis.univer.uz/
STATIC_URL      = http://hemis.univer.uz/static/
    
```
#Migrate initial database

To migrate initial database run following code. After migrating successfully, you can find 
super administrator password at `console/runtime/.passwd` file. Use this password to enter
HEMIS dashboard. After log in successfully, you should remove `.passwd` file and change super 
administrator password on dashboard.

```
./yii migrate
./yii indexer/resources
```

So, system initialized successfully and ready to start using HEMIS system. Login administrator dasboard
and follow further instructions. 

#Restore database from auto-backup file

```
#switch to postgresql user
su - postgresql

#goto backups folder
cd /var/www/univer/backups

#run script, pass database name as a first parameter, this restores last archieve file.
./restore.sh hemis

#If you pass archieve file name as a second parametr then it restores this file:
#./restore.sh hemis postg_hemis_15-05-2020_12_00.bak.tar

```

#Add following cron jobs:
```
* * * * * /var/www/univer/yii indexer/min1
*/5 * * * * /var/www/univer/yii indexer/min5
0 * * * * /var/www/univer/yii indexer/hour1
0 */6 * * * /var/www/univer/yii indexer/hour6
0 1 * * * /var/www/univer/yii indexer/day1
```

#Run following console command to index default roles and translations
```
./yii indexer/resources
```

#Configure supervisor for Yii2 queue
```
sudo apt-get install supervisor
sudo cp /var/www/univer/queue.conf /etc/supervisor/conf.d/
sudo service supervisor restart
sudo service supervisor status
```