
# HEMIS Universitetni Boshqarish Tizimi

Ushbu loyiha PHP 7.2 ning so‘nggi versiyasi va Yii2 PHP framework-dan foydalanadi. Kod yozishda PHP funksiyalaridan foydalanishingiz kerak.

#### Yaxshi dasturlash!

---

## Apache virtual host konfiguratsiyasi

```apacheconf
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
```

---

## `/etc/hosts` konfiguratsiyasi

```text
127.0.0.1       student.univer.uz
127.0.0.1       backend.univer.uz
```

---

## Loyihani tayyorlash va sozlash

Yii2 Advanced loyihasi 3 qismdan iborat: frontend, backend va konsol. Har bir qismi alohida ishlaydi va jamoaviy dasturlash uchun moslashgan.

---

## PHP 7.2 sozlamalari

Faylni tahrirlash: `/etc/php/7.2/apache2/php.ini`

```ini
short_open_tag = on
max_execution_time = 300
upload_max_filesize = 4M
```

---

## PostgreSQL va PgAdmin o‘rnatish (Debian 9/10)

```bash
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" | sudo tee /etc/apt/sources.list.d/pgdg.list
sudo apt update
sudo apt -y install postgresql-12 postgresql-client-12
sudo apt -y install pgadmin4 pgadmin4-apache2
sudo su - postgres
psql -c "alter user postgres with password 'StrongAdminP@ssw0rd'"
psql
```

---

## Avtomatik zaxira skriptini sozlash

```bash
su - postgres
crontab -e
```

Cron sozlamasi:
```cron
00 */12 * * * /var/www/univer/backups/backup.sh hemis
```

---

## Dastlabki bazani migratsiya qilish

```bash
./yii migrate
./yii indexer/resources
```

Superadmin parolini `console/runtime/.passwd` faylidan toping. Kirgandan so‘ng, ushbu faylni o‘chirib tashlang va parolni yangilang.

---

## Avto-zaxiradan qayta tiklash

```bash
su - postgres
cd /var/www/univer/backups
./restore.sh hemis
```

---

## Cron ishlov beruvchilarni qo‘shish

```cron
* * * * * /var/www/univer/yii indexer/min1
*/5 * * * * /var/www/univer/yii indexer/min5
0 * * * * /var/www/univer/yii indexer/hour1
0 */6 * * * /var/www/univer/yii indexer/hour6
0 1 * * * /var/www/univer/yii indexer/day1
```

---

## Yii2 navbatlarini sozlash uchun supervisor

```bash
sudo apt-get install supervisor
sudo cp /var/www/univer/queue.conf /etc/supervisor/conf.d/
sudo service supervisor restart
sudo service supervisor status
```

Tizim endi foydalanishga tayyor. Administrator paneliga kiring va qo‘shimcha ko‘rsatmalarga amal qiling.
