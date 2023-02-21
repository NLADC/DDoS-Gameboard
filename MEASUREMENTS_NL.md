## Measurement-API

Het functionele ontwerp is als volgt
- je hebt een oefening met een naam en een start(eerste) en stoptijd
- je hebt een party
- het heeft 1 of meer targets (IP of domein)
- het type is ook gekoppeld aan een doel (webserver, dns of smtp)
- Voor een doel kunnen 1 of meer metingen worden toegevoegd
- dit wordt gedaan door een measurement_api toe te voegen aan een doelmeting
- een measurement_api kent ook het type (webserver, dns of smtp)
- een configjson-definitie wordt herkend per measure_api en type
- voor RIPE bevat dit de (json-code voor) definities, probes en zaken als start/stop/oneoff

Voor een oefening

Als de oefening met de partijen en targets op deze manier is gedefinieerd, moeten de metingen in RIPE worden aangemaakt. Dit kan worden gedaan via een plug-in console-commando:

### Measurement-API met CLI-commando's

#### Controleren of measurmentapi werkt
```shell
php artisan ddosgameboard:measurementAPI -h
```
Als u de optie -h geeft, wordt (samengevatte) helpinformatie weergegeven.

```shell
php artisan ddosgameboard:measurementAPI
```

Wanneer je de Measurements API definities correct hebt ingesteld (zie appendix voor voorbeeld configjson code), kunnen de metingen in RIPE ALTAS worden aangemaakt met:
```shell
php artisan ddosgameboard:measurementAPI create
```
Dit commando zal:
Kijk of de oefening in de toekomst is
De measurements in RIPE ATLAS aanmaken per partijen, per target, per measurement definitie
(als de measurement al bestaat voor de tijdsperiode, wordt deze NIET een tweede keer gemaakt)

U kunt gemaakte metingen vinden op de RIPE ATLAS-website. En eventueel weer verwijderen (DELETE) wanneer alleen voor testen.

Een commando waarmee je een Cronjob kunt emuleren
```schelp
php artisan ddosgameboard:measurementAPI measure
```

Dit commando kijkt naar oefening, partijen, targets en measurement definities en zet de metingen in RIPE ATLAS om in waarden in de measurements table.

De responstijd van alle probes wordt cumulatief verzameld per minuut (gemiddeld).

### Bekijk RIPE ATLAS-metingen

Met het volgende commando kun je de vertaling van RIPE-metingen naar spelbordwaarden per minuut volgen:

```shell
php artisan ddosgameboard:measurementAPI show -m <mid>
```

(optie) Met <mid> is de meting-ID nummer in RIPE ATLAS, bijvoorbeeld 49131119.
