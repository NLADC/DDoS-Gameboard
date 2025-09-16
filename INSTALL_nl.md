## Installing DDoS spelbord 2.1

Het DDoS gameboard 2.1, kortweg **GB** genaamd, is opgebouwd op basis van de volgende frameworks:
- laravel
- winterCMS
- vue.js

Verder is het uitgangspunt dat op de server de volgende packages zijn geinstalleerd:
- (https://wintercms.com/docs/setup/installation)
- PHP 7.4  (working version on ubuntu 22)
- PDO PHP Extension (and relevant driver for the database you want to connect to)
- cURL PHP Extension
- OpenSSL PHP Extension
- Mbstring PHP Extension
- ZipArchive PHP Extension
- GD PHP Extension
- SimpleXML PHP Extension

Deze kun je in een keer naar binnen trekken door
```shell
sudo apt-get update &&
sudo apt-get install php php-ctype php-curl php-xml php-fileinfo php-gd php-json php-mbstring php-mysql php-sqlite3 php-zip
```

En ook
- composer 1.x or higher, versie die werkt met ubuntu 22 is 1.10.26. Nooit met sudo installeren!
(https://getcomposer.org/download/)
```shell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --version=1.10.26
php -r "unlink('composer-setup.php');"
```
We gebruiken composer lokaal door het aanroepen van 'composer'. Dit is mogelijk na:
```shell
sudo mv composer.phar /usr/local/bin/composer
```
- nginx  (apt install nginx)
- mariadb  (apt install mariadb-server)
- npm  (via NodeSource), geverifieerde versie is 8.5.1

### Installeren WinterCMS

Installeer een WinterCMS omgeving:

```shell
composer self-update --1
composer create-project wintercms/winter <hoofdmap> "dev-develop"
```

Ga vervolgens in de <hoofdmap> folder en voer uit:

```shell
php artisan winter:env
```

Hiermee wordt de configuratie beschikbaar vanuit de (hoofdmap) .env file.

Maak een eigen (lokale) database aan, met db user en password:

```shell
CREATE DATABASE ddosspelbord;
CREATE USER 'ddosspelbord'@localhost IDENTIFIED BY 'mypassword';
GRANT ALL ON ddosspelbord.* TO 'ddosspelbord'@'localhost';
FLUSH PRIVILEGES;
QUIT;
```

In de .env file die in de hoofdmap staat kun je de database gegevens invoeren.

```.env
DB_CONNECTION="mysql"
DB_HOST="localhost"
DB_PORT=3306
DB_DATABASE="ddosspelbord"
DB_USERNAME="ddosspelbord"
DB_PASSWORD="mypassword"
```

Zorg voor een nginx of apache configuratie die in de hoofdmap uitkomt:
- /etc/nginx/sites-available/
- https://wintercms.com/docs/setup/configuration#nginx-configuration
- zorg voor chown -R www-data:www-data van (hoofdmap)
- zorg voor chmod -R 775 van (hoofdmap)/storage

#### nginx config

Extra toevoegen als uitzondering (onderaan nginx config):

```
location ~ ^/json/.* { try_files $uri 404; }
location ~ ^/img/.* { try_files $uri 404; }
location ~ ^/fonts/.* { try_files $uri 404; }
location ~ ^/favicon.ico { try_files $uri 404; }

# status page
location ~ ^/(status|ping)$ {
    access_log off;
    allow 127.0.0.1;
    deny all;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
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
Apache werk alleen als AllowOverride allemaal en Mod rewrite werkt:

Voeg het volgende toe aan je VirtualHost (d.w.z. regel onder #LogLevel info ssl:warn in apache config):

```shell
<Directory /server/www/dgb/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Apache werkt pas naar behoren na het enablen van de mod rewrite:

Vergeet niet de toegang naar phpmyadmin uit te zetten bij het opleveren van het ddosspelbord

#### php max_input_vars

Om te werken met Plandata groter dan 90 rijen moet het een van de volgende geconfigureerd worden.

```
php.ini	max_input_vars = 5000
.htaccess (Apache)	php_value max_input_vars 5000
Nginx site / fastcgi_param	fastcgi_param PHP_VALUE "max_input_vars=5000";
```


```shell
sudo a2enmod rewrite
sudo systemctl apache2 restart
```

Configuratie staat al in .htacces en apache zou die automatish moeten overnemen

#### tijdszone server

Zet de tijdszone naar de correcte tijd:
Bijvoorbeeld amsterdam:
```shell
timedatectl set-ntp true
timedatectl set-timezone Europe/Amsterdam
```

#### Wintercms instaleren
Nu kun je WinterCMS installeren door de gegevens in te vullen in de installatie via script

```shell
php artisan winter:install
```

Mocht je niet naar <site>/backend kunnen navigeren doorloop dan de configuratie van je webserver onder de volgende url onder webserver configuration:
https://wintercms.com/docs/setup/configuration#webserver-configuration

Mocht je vastlopen bij het installeren van WinterCMS kijk vooral ook naar de nieuwste documentatie op https://wintercms.com/docs/help/using-composer#installing-winter
of je alle stappen correct zijn nagelopen of er nog iets mist in jouw setup.


Configureer vervolgens de wintercms omgeving:

```shell
php artisan winter:up
```

(Noteer wachtwoord)

Installeer vervolgens de volgende plugins:

```shell
composer require winter/wn-user-plugin
composer require --dev winter/wn-builder-plugin
php artisan winter:up
```

Als het goed is heb je nu een WinterCMS omgeving op:
- https://(server); frontend
- https://(server)/backend/; backend (inlog gedeelte)

### Installeren DDoS gameboard plugin & theme

Gameboard is in WinterCMS geintegreerd op basis van een plugin en theme.
Via een `git clone` van https://github.com/ADC-NL/DDoS-Gameboard in de hoofdmap worden de aanvullende sources geladen.

Web root / www root is in onderstaande commando's: `/server/www/dgb`

```shell
sudo mkdir -p /server/www/dgb
sudo chown www-data:www-data /server/www/dgb
```

Clone de repository.
```shell
mkdir /server/www/dgb/ddos-gameboard-git
sudo -u www-data git clone git@github.com:ADC-NL/DDoS-Gameboard.git gitsource
```
Standaard is de `main` branch actief. Als je naar een andere branch (bijv. branch `localdev`) wilt wisselen doe je het volgende:
```shell
cd /server/www/dgb/ddos-gameboard-git
sudo -u www-data git checkout localdev
cd ..
```

Om de data uit de GitLab repository in de WinterCMS installatie te krijgen run je het volgende commando:
```shell
sudo -u www-data rsync --archive --progress --verbose /server/www/dgb/ddos-gameboard-git /server/www/dgb --exclude ".git"
```
De `.git` map in `gitsource` wil je niet meer kopiëren due die sla je over.

Dit `rsync` commando kun je zo vaak draaien als je wilt.
Als `rsync` ziet dat er geen wijziging is dan slaat ie de bestanden over en is de sync zo klaar.

Nu kun je je source map /server/www/dgb/ddos-gameboard-git weer verwijderen

Dan doe je `composer update`:
```shell
composer update
```
De optie `--no-cache` kun je mee meegeven omdat Composer anders meldingen geeft over het niet aan kunnen maken van een cache directory.

```shell
composer --no-cache update
```

Zet nu alle permissies goed:
```shell
# www-data als eigenaar van de web root:
sudo chown -R www-data:www-data /server/www/dgb
# Alle bestanden op read+write voor owner en group zetten en op read voor others; behalve sommige bestanden zoals scripts:
sudo find /server/www/dgb -type f ! -name '*.sh' ! -name bulklogin.sh -exec chmod 0664 {} \;
# Mode van de mappen goed zetten:
sudo find /server/www/dgb -type d -exec chmod 0755 {} \;
sudo find /server/www/dgb/storage -type d -exec chmod 0755 {} \;
```

I.p.v. `chmod` recursive (`-R`) op de hele map te doen gebruik je het `find` commando.
Dit omdat anders alle bestanden *executable* permissie krijgen, wat minder veilig is.
Sommige bestanden moeten wel *executable* zijn, zie het eerste `find` commando.
Voeg aan dit commando extra bestanden toe die *executable* moeten zijn.

Als je problemen ervaart kun je even testen door de mode van de gehele map wat losser te zetten:
```
sudo chmod -R 0775 /server/www/dgb
```

Nu moet de ddosspelbord database geïnitieerd worden. Dit doen we door de sql in het volgende bestand uit te voeren:

De sql om te seeden kun je via phpmyadmin of je database beheer tool naar keuze als sql laten uitvoeren, maar kan ook gedaan worden via de terminal.
```shell
mysql
mysql> use ddosspelbord;
mysql> source plugins/bld/ddosspelbord/updates/init_ddos_spelbord_database.sql;
```

Werk vervolgens de WinterCMS omgeving bij (theme instellen op DDoS spelbord):
```shell
sudo -u www-data composer --no-cache update
sudo -u www-data php artisan winter:up
sudo -u www-data php artisan theme:use ddos-gameboard
sudo -u www-data php artisan winter:mirror public
```

Installeer vervolgens de NPM/Vue/Node omgeving op basis van de (git clone) package.json file:
```shell
curl -fsSL https://rpm.nodesource.com/setup_16.x | sudo bash -
sudo apt install nodejs
node --version
```

Controleer of sass loader version 7.1.0 anders doe het volgende:
```shell
npm uninstall --save-dev sass-loader
npm install --save-dev sass-loader@7.1.0
```

Werkende versies op Ubuntu 20.04.5 LTS zijn:
Nodejs: v16.18.1
NPM: 8.5.1

Werkende versies op Ubuntu 22.04 LTS zijn:
Nodejs: v12.22.9
NPM: 8.5.1

in de terminal voer in
```shell
npm install
npm update
npm run prod
```

#### initialize language
Vergeet niet de language strings the initen
```shell
php artisan translate:scan --purge
php artisan cache:clear
```

Nu zijn alle package's gereed om te beginnen.
Zie development.md om als je wilt gaan ontwikkelen aan het spelbord.

Zet de nginx hoofdmap op de (hoofdmap)/public:
`/etc/nginx/sites-available/ddosgameboard`:
```
root /server/www/dgb
wordt:
root /server/www/dgb/public
```

Nu zou de omgeving up and running moeten zijn:
- via de /backend toegang tot het Admin gedeelte
- Via de / basis url toegang tot het DDoS spelbord
- npm run dev -> compileren vue naar

Om een game te starten is het belangrijk om op de volgende pagina alle instellingen in te stellen:
 /backend/system/settings/update/bld/ddosspelbord/settings


#### Database vulling

Leuke vulling actions:
- plugins/bld/ddosspelbord/updates/init_ddos_spelbord_actions.sql;
- plugins/bld/ddosspelbord/updates/init_ddos_spelbord_transactions.sql;
- plugins/bld/ddosspelbord/updates/seed_ddos_spelbord.sql;

Let op; de tijden in de instellingen (zie hieronder) moet overeenkomen met de testdata in de database.

#### Backend instelling DDoS spelbord

Vergeet niet de DDoS spelbord instelligen onder backend -> settings in te vullen:
- /backend/system/settings/update/bld/ddosspelbord/settings


#### Ten slotte

Let goed op tijdens het configuren om de owner en rechten van de bestanden goed te houden.

