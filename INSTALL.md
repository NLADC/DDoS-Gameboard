## Installing DDoS Game Board 2.1

The DDoS gameboard 2.1, abbreviated **GB**, is built on the basis of the following frameworks:
- Laravel
- Winter CMS
- Vue.js

Furthermore, the starting point is that the following packages are installed on the server:
- (https://wintercms.com/docs/setup/installation)
- PHP 7.4 (working version on ubuntu 22)
- PDO PHP Extension (and relevant driver for the database you want to connect to)
- cURL PHP Extension
- OpenSSL PHP Extension
- Mbstring PHP Extension
- ZipArchive PHP Extension
- GD PHP Extension
- SimpleXML PHP Extension

You can pull this in at once by running the following command
```shell
sudo apt-get update &&
sudo apt-get install php php-ctype php-curl php-xml php-fileinfo php-gd php-json php-mbstring php-mysql php-sqlite3 php-zip
```

And also you need to have Composer
- composer 1.x, version that works with ubuntu 22 is 1.10.26. Never install with sudo!
(https://getcomposer.org/download/)
```shell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --version=1.10.26
php -r "unlink('composer-setup.php');"
```
We use composer locally by calling 'composer'. This is possible after:
```shell
sudo mv composer.phar /usr/local/bin/composer
```
- nginx (apt install nginx) or alternatively apache (apt install apache2) 
- mariadb (apt install mariadb-server)
- npm (via NodeSource), verified version is 8.5.1

### Install WinterCMS

Install a WinterCMS environment:

```shell
composer self-update --1
composer create-project wintercms/winter <hoofdmap> "dev-develop"
```

Then go into the <root> folder and run:

```shell
php artisan winter:env
```

This makes the configuration available from the (root) .env file.

Create your own (local) database, with a user and password:

```shell
CREATE DATABASE ddosspelbord;
CREATE USER 'ddosspelbord'@localhost IDENTIFIED BY 'mypassword';
GRANT ALL ON ddosspelbord.* TO 'ddosspelbord'@'localhost';
FLUSH PRIVILEGES;
QUIT;
```

You can enter the database data in the .env file in the main folder.

```.env
DB_CONNECTION="mysql"
DB_HOST="localhost"
DB_PORT=3306
DB_DATABASE="ddosspelbord"
DB_USERNAME="ddosspelbord"
DB_PASSWORD="mypassword"
```

Provide a nginx or apache configuration that ends up in the root folder:
- /etc/nginx/sites-available/
- https://wintercms.com/docs/setup/configuration#nginx-configuration
- provide chown -R www-data:www-data from (root)
- provide chmod -R 755 from (root)
- get chmod -r 775 from (root)/storage

#### nginx config

Add extra as an exception (at the bottom of nginx config):

```
location ~ ^/json/.* { try_files $uri 404; }
location ~ ^/img/.* { try_files $uri 404; }
location ~ ^/fonts/.* { try_files $uri 404; }
location ~ ^/favicon.ico { try_files $uri 404; }

# status page block
location ~ ^/(status|ping)$ {
    access_log off;
    allow 127.0.0.1;
    deny all;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_pass 127.0.0.1:9000;
}

location /bootstrap {
  rewrite ^/bootstrap/.* /index.php break;
}

location /config {
  rewrite ^/config/.* /index.php break;
}

location /vendor {
  rewrite ^/vendor/.* /index.php break;
}

location /storage {
  rewrite ^/storage/cms/.* /index.php break;
  rewrite ^/storage/logs/.* /index.php break;
  rewrite ^/storage/framework/.* /index.php break;
  rewrite ^/storage/temp/protected/.* /index.php break;
  rewrite ^/storage/app/uploads/protected/.* /index.php break;
  rewrite ^/storage/app/uploads/public/.* /index.php break;
}

location / {
  if (-e $request_filename){
    rewrite !^index.php /index.php break;
  }
  if (-e $request_filename){
    rewrite !^index.php /index.php break;
  }
  if (!-e $request_filename){
    rewrite ^(.*)$ /index.php break;
  }
}

# Pass the phpmyadmin PHP scripts to FastCGI server
location ~ ^/phpmyadmin/(.+\.php)$ {
    allow 127.0.0.1;
    allow 87.251.37.123;
    deny all;
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    fastcgi_param PATH_INFO $fastcgi_path_info;
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

#### apache config
Apache only works properly after enabling the AllowOverride and mod_rewrite:

Add the following to VirtualHost (i.e. line below #LogLevel info ssl:warn in apache config):

```shell
<Directory /server/www/dgb/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Enable mod_rewrite and restart the apache server

Make sure you don't forgot to disable acces to the phpmyadmin page when deploying

```shell
sudo a2enmod rewrite
sudo systemctl apache2 restart
```


#### timezone server

Set the timezone of your server to the correct time.
For example to amsterdam:
```shell
timedatectl set-ntp true
timedatectl set-timezone Europe/Amsterdam
```


Configuration is already in the .htaccess file and apache should pick it up automatically

#### Install wintercms
Now you can install WinterCMS by filling in the data in the installation via script

```shell
php artisan winter:install
```

If you cannot navigate to <site>/backend, go through the configuration of your web server under the following url under webserver configuration:
https://wintercms.com/docs/setup/configuration#webserver-configuration

If you get stuck installing WinterCMS, please also check the latest documentation at https://wintercms.com/docs/help/using-composer#installing-winter
whether you have followed all the steps correctly or whether something is still missing in your setup.


Then configure the wintercms environment:

```shell
php artisan winter:up
```

(Write down password) Then install the following plugins:

```shell
composer require winter/wn-user-plugin
composer require --dev winter/wn-builder-plugin
php artisan winter:up
```

You should now have a WinterCMS environment on:
- https://(server); front end
- https://(server)/backend/; backend (login area)

### Install DDoS gameboard plugin & theme

Gameboard is integrated in WinterCMS based as a plugin and theme.
The additional sources are loaded via a `git clone` from https://github.com/ADC-NL/DDoS-Gameboard in the main folder.

Web root / www root is in the commands below: `/server/www/dgb`

```shell
sudo mkdir -p /server/www/dgb
sudo chown www-data:www-data /server/www/dgb
```

Clone the repository.
```shell
mkdir /server/www/dgb/ddos-gameboard-git
sudo -u www-data git clone git@github.com:ADC-NL/DDoS-Gameboard.git gitsource
```
By default, the `main` branch is active. If you want to switch to another branch (e.g. branch `localdev`) do the following:
```shell
cd /server/www/dgb/ddos-gameboard-git
sudo -u www-data git checkout localdev
cd ..
```

To get the data from the GitLab repository into the WinterCMS installation, run the following command:
```shell
sudo -u www-data rsync --archive --progress --verbose /server/www/dgb/ddos-gameboard-git /server/www/dgb --exclude ".git"
```
You no longer want to copy the `.git` folder in `gitsource`, so you skip that.

You can run this `rsync` command as many times as you like.
If rsync sees that there is no change, it will skip the files and the sync will be done in no time.

Now you can delete your source folder /server/www/dgb/ddos-gameboard-git again


Then you do `composer update`:
```shell
composer update
```
You specify the `--no-cache` option because otherwise Composer will give messages about not being able to create a cache directory.

```shell
composer --no-cache update
```

Now set all permissions correctly:
```shell
# www-data as owner of the web root:
sudo chown -R www-data:www-data /server/www/dgb
# Set all files to read+write for owner and group and to read for others; except some files like scripts:
sudo find /server/www/dgb -type f ! -name '*.sh' ! -name bulklogin.sh -exec chmod 0664 {} \;
# Set folder mode correctly:
sudo find /server/www/dgb -type d -exec chmod 0755 {} \;
sudo find /server/www/dgb/storage -type d -exec chmod 0755 {} \;
```

instead of To do `chmod` recursive (`-R`) on the entire directory, use the `find` command.
This is because otherwise all files will get *executable* permission, which is less secure.
Some files must be *executable*, see the first `find` command.
Add additional files to this command that must be *executable*.

If you experience problems, you can test by loosening the mode of the entire map:
```
sudo chmod -R 0775 /server/www/dgb
```

Now the ddosgameboard database has to be initiated. We do this by running the sql in the following file:

The sql can be executed as sql via phpmyadmin or your database management tool of choice, but can also be done via the terminal.
```shell
mysql
mysql> use ddosspelbord;
mysql> source plugins/bld/ddosspelbord/updates/init_ddos_spelbord_database.sql;
```

Then update the WinterCMS environment (set theme to DDoS game board):
```shell
sudo -u www-data composer --no-cache update
sudo -u www-data php artisan winter:up
sudo -u www-data php artisan theme:use ddos-gameboard
sudo -u www-data php artisan winter:mirror public
```

Then install the NPM/Vue/Node environment based on the (git clone) package.json file:
```shell
curl -fsSL https://rpm.nodesource.com/setup_16.x | sudo bash -
sudo apt install nodejs
node --version
```

Check if the sass loader is installed on version 7.1.0 otherwise doe the following:
```shell
npm uninstall --save-dev sass-loader
npm install --save-dev sass-loader@7.1.0
```


Working versions on Ubuntu 20.04.5 LTS are:
Nodejs: v16.18.1
NPM: 8.5.1

Working versions on Ubuntu 22.04 LTS are:
Nodejs: v12.22.9
NPM: 8.5.1

in the terminal enter
```shell
npm install
npm update
npm run prod
```

#### initialize language
Dont forgot to init the language strings
```shell
php artisan translate:scan --purge
php artisan cache:clear
```


Now all packages are ready to start.
See development.md to proceed.

Set the nginx root directory to (root)/public:
`/etc/nginx/sites-available/ddosgameboard`:
```
root /server/www/dgb
is going to be:
root /server/www/dgb/public
```

Now the environment should be up and running:
- Access to the Admin section via the /backend
- Access to the DDoS game board via the / base url
- npm run prod -> to compile Vue to use for production

To start a game it is important to set all settings on the next page:
  /backend/system/settings/update/bld/ddosgameboard/settings


#### Database population

Nice stuffing actions:
- plugins/bld/ddosgameboard/updates/init_ddos_gameboard_actions.sql;
- plugins/bld/ddosspelboard/updates/init_ddos_spelboard_transactions.sql;

Pay attention; the times in the settings (see below) must match the test data in the database.

#### Backend setting DDoS game board

Don't forget to set the DDoS game board under backend -> settings:
- /backend/system/settings/update/bld/ddosspelbord/settings


#### And last but not least

Pay close attention while configuring to keep the owner and permissions of the files correct.
