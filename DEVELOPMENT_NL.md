## Development in DDOS-Gameboard

### NPM watcher

Zonder watcher veranderd er niets aan de SCSS, Vue, Js, Assets enzovoort... wanneer je ze wijzigt in de hoofdmap. Het is niet de bedoeling om rechtstreeks assets te wijzigen in de "public" map. Ga naar een terminal en draai het volgende commando:

Bij elke save in een bestand zal deze gecompiled en overgezet worden naar de public map. Nu kun je beginnen met developen vanuit de hoofdmap.

```shell
npm run watch-poll
```

### Webpack

In webpack.mix.js staat beschreven wat de compiler moet compilen naar de public map. Let op dit kunnen ook meer bestanden zijn dan je ziet. Het is mogelijk voor om binnen een js bestand andere bestanden te importeren. Er is gekozen om dit bestand redelijk minimaal te houden. Importeer vanuit één bestand meerdere bestanden, en voeg dit ene bestand in de webpack in.

### Werken met PHPstorm

Bij het developen aan het spelbord is het erg belangrijk om een sterke IDE te gebruiken.
Een IDE helpt met begrijpen van de code. Het project bestaat uit veel kleine functies en variabelen worden veel van bestand naar bestand doorgegeven via parameters. PHPstorm houd de structuur en de data bij.

#### xdebug

Deze tool is erg belangrijk om te bekijken wat de API's ontvangen vanuit de Vue. Daarnaast helpt het enorm om fouten te achterhalen. De foutrapportage van het spelbord is soms vaag en niet specifiek. Foutmeldingen vanuit WinterCMS worden niet overgenomen door het spelbord in de front-end.
Door via Xdebug de code langs te lopen kun je soms deze foutmeldingen wel achterhalen. Zie hier documentatie: https://www.jetbrains.com/help/phpstorm/configuring-xdebug.html

#### VueJs Debug

Mogelijk nog belangrijker dan Xdebug is het kunnen debuggen van de Vue code. Zie hier alle informatie over het Vue Debuggen opzetten
https://www.jetbrains.com/help/phpstorm/vue-js.html#ws_vue_running_and_debugging

Ze kunnen ook in samen gebruikt worden, dus je zet breakpoint in een async functie die gekoppeld is aan een API.
Dan zal PHP storm eerst pauzeren in de VueJS en daar pauzeren in de PHP api code, om vervolgens te pauzeren in de VueJS.

Het werken met var_dump, print_r, console_log raad ik bij deze af, dit geeft niet de juiste mogelijkheden om echt te snappen wat er gebeurd met de data via API's.

## Javascript en Vue

*Voor het lezen van de documentatie is het handig om eerst het spelbord geopend te hebben in de browser*

In het thema: "ddos-spelbord" staan eigenlijk alle bestanden die via de vue compiler overgezet gaan worden naar de public map.

### Layouts: ddos-gameboard.htm

Hier staat het .htm bestand wat altijd ingeladen wordt. Heel belangrijk is de ``<head>``.

```html
<script type="text/javascript" defer> window.gameboard_logs = "{{ gameboard.data.logs|raw }}";</script>
```
Hierboven wordt er data vanuit de backend naar binnen gehaald. De variabel: "gameboard" is de link tussen het thema en de backend: plugins/bld/ddosspelbord.
Dit bevat alle informatie die het thema, en dus de client bijkan, de rest is afgeschermd.

### Home.htm

In Home vinden we de hoogste laag html zoals het spelbord in de browser weergegeven wordt. In feite is het eerder een .vue bestand, er is namelijk Vue syntax aanwezig.

Voor alle documentatie bettreffende Vue syntax zie: https://vuejs.org/guide/essentials/template-syntax.html#attribute-bindings

#### Variabelen

Alle variabelen zoals "user", "logs" of "parties" komen uit themes/ddos-gameboard/resources/js/gameboard.js onder data() {....

#### Vue componenten

Niet alle html staat in home.htm, Bijvoorbeeld de GameCountdown. Dit zijn is de grote afteller bovenin het spelbord. Deze wordt ingeladen met:

```html

<game-countdown starttime="{{ gameboard.starttime }}"
                endtime="{{ gameboard.endtime }}"></game-countdown>
```

De html kun je vinden in themes/ddos-gameboard/resources/js/components/GameCountdown.vue

Dit is aan elkaar gekoppeld via gameboard.js met

```vue
Vue.component('game-countdown', require('./components/GameCountdown.vue').default);
```

gameboard.starttime zijn variabelen die uit de backend komen.

*Zie documentatie op vuejs.org/guide voor meer info*

### pages/api

Hierin staan de htm bestanden die het mogelijk maken om via de VueJS te communiceren met de backend.
Via inline PHP in het htm bestand via onStart() aangeroepen naar welke functie de data vanuit de client heen gestuurd moet worden.
Dit zijn uiteraard functies uit het bestand aangegeven boven in zoals:
```php
use bld\ddosspelbord\components\ddosspelbord_attachments;
```
Alle api bestanden met de functies zijn te vinden in ``plugins/bld/ddosspelbord/components/ddosspelbord_<api>.php``

### gameboard.js

Dit Js bestand is het hoogste niveau van je VueJS. Vanuit hier worden alle .vue bestanden samengevoegd tot één werkende javascript.

Vanuit "layous\ddos-gameboard.htm" wordt met het onderstaande stukje HTML alle via gameboard.js dus alle Vue ingeladen.

```html

<script src="{{ 'assets/js/gameboard.js'|theme }}" defer></script>
```

#### Vue Componenten

Vue componenten bevinden zich in /resources/js/components/ en worden ingeladen in het gameboard.js op deze manier:

```js
Vue.component('timeline', require('./components/Timeline.vue').default);
Vue.component('party', require('./components/Party.vue').default);
```

Zoals al eerder genoemd, kun je via de home.htm variabelen inladen door ze mee te geven als attribute:

```html

<game-countdown starttime="{{ gameboard.starttime }}"
                endtime="{{ gameboard.endtime }}"></game-countdown>
```

in het component zullen starttime en endtime terugkomen als props

```vue
props: {
starttime: String,
endtime: String
},
```

Props zijn initiele waardes, je kunt ze niet muteren, dus de starttime kan nooit aangepast worden vanuit GameCountdown.vue. \
Wel als de prop veranderd vanuit bovenaf, in dit geval gameboard.js mogelijk via php dan wordt dit wel doorgegeven aan de child component
Meer over props: https://vuejs.org/guide/components/props.html

#### Global vue variabelen

Er zijn settings uit de backend die gebruikt worden uit de code. Deze worden simpelweg in de code aangesproken door ``this.logmaxfilesize`` in te voeren.
Dit wordt gedaan door zaken toe te voegen aan het prototype, normaal gebruik je hiervoor de data(), alleen dit kan nuttig zijn om bijvoorbeeld global settings van de backend in te voeren. Zoals dus het limiet aan bestanden die je mag loggen of hoe groot ze zijn.

```js
Vue.prototype.logmaxfilesize = window.gameboard_logmaxfilesize;
Vue.prototype.logmaxfiles = window.gameboard_logmaxfiles;
```

*Let op hier is gameboard de variabel die meegegeven wordt vanuit de backend

#### Event $on en $emit

Communicatie van data van gameboard.js naar components is de normale data flow. Mutatie van Vue components naar gameboard.js is niet toegestaan Er is wel een manier om een event aan te vragen, dit is mogelijk door te $on en $emit. Hieronder een simpel voorbeeld

in AttachmentModal.vue

```vue
methods: {
    close() {
        this.$emit('close')
        Event.$emit('emptyAttachmentsmodal');
    }
}
```

in gameboard.js

```js
Event.$on('emptyAttachmentsmodal', () => {
        for (var key in this.attachmentmodal) {
            this.attachmentmodal[key] = null;
        }
    }
);
```

Wat hier gebeurd is dat als in AttachmentModal de functie close uitgevoerd wordt die een oproep doet via Event naar de functie ``emptyAttachmentsmodal``.
Vanuit gameboard.js kan Event deze oproep ontvangen en dit uitvoeren. Hierdoor muteerd gameboard.js ``this.attachmentmodal``. 
Deze variabel ``this.attachmentmodel`` wordt dan weer meegegeven aan het component AttachmentModal.vue. Hierdoor muteerd gameboard.js haar componenten en niet andersom.

### transaction.js

Dit script is vanuit de client het mechanisme wat transacties regelt met de backend (php) op de server.

Hieronder een voorbeeld van een log die weggeschreven wordt via een transaction uit de log API plugins/gameboard/
/components/ddosspelbord_log.php.

```php
 // get vue code values & create transaction
 $alog = ddosspelbord_data::getSpelbordLog($log, $hasattachments);
 (new Feeds())->createTransaction(TRANSACTION_TYPE_LOG, $alog);
```

``$alog`` is een object met de logdata afkomstig uit de client (VueJs); Via de functie ``createtransaction()`` wordt en de logdata dus weer teruggesynchroniseerd en uiteindelijk via transaction.js naar het spelbord verzonden wordt.

Transacties zijn vooral handig wanneer partij genoten nieuwste gegevens uit het bord willen hebben zonder de browser te refreshen.

De precise werking achter transaction.js kan veel beter gedocumenteerd worden....

## Theming

Hoe het DDOS spelbord eruitziet wordt grotendeels bepaald door de theming. 
Dit werkt met een combinatie van SCSS, Js, CSS en het uiteindelijke skelet van html.

### CSS

CSS is de stylings code die de browser rechtstreeks kan uitvoeren.
In themes/ddos-gameboard/resoucres/css staat is met name gameboard.css van belang voor het verder ontwikkelen van de theming.
Voor informatie over hoe CSS werkt is W3Schools een erg sterk platform om te leren.

https://www.w3schools.com/css/

### Tailwind

Tailwind is een keuze geweest van de orginele maker van het ddosspelbord om te kunnen themen zonder echt veel of uberhaupt css te hoeven schrijven.
De orginele CSS en source code is the vinden in /node_modules/tailwindcss
Je kan dan dus in de html schrijven:

Tailwind HTML:

```html

<div class="h-16 text-2xl p-6 text-red-400">DDoS gameboard</div>
```

Dit resulteert dan in de volgende css die ingeladen wordt op deze div:

CSS die browser ontvangt:

```css
.text-red-400 {
    --tw-text-opacity: 1;
    color: rgba(248, 113, 113, var(--tw-text-opacity));
}

.text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
}

.p-6 {
    padding: 1.5rem;
}

.h-16 {
    height: 4rem;
}
```

#### Advies

Het gebruik van html DOM elementen zonder heldere namen of structuren werkt verwarrend voor front-end developers die verder willen bouwen op de theming. De div uit het hierboven genoemde voorbeeld is een header van het loginscherm. Dit is niet meteen duidelijk voor een externe developer.
Daarnaast staan de classnames ``h-16 text-2xl p-6 text-red-400`` rommelig. Styling hoort in css die gekoppeld is aan een classname. Hieronder een voorbeeld van dezelfde HTML en CSS maar dan op de correcte manier.

Correcte HTML:

```html

<div id="loginheading">DDoS gameboard</div>
```

CSS uit de browser:

```css
#loginheading {
    color: rgb(248, 113, 113);
    font-size: 1.5rem;
    line-height: 2rem;
    padding: 1.5rem;
    height: 4rem;
}
```

De CSS is identiek aan de CSS die in je resources/css te vinden is. 
Nadeel is dat je die CSS dan moet schrijven ipv de tailwind classes snel in de html te stoppen.

#### Tailwind binnen CSS

Andere manier werkt met ``@apply`` hierdoor kun je in bijvoorbeeld gameboard.css het volgende doen:

```css
.action .sticky-top {
    @apply sticky top-36 z-40;
}
```

dit resulteerde in de browser van de client als volgt:

```css
.action .sticky-top {
    position: sticky;
    top: 9rem;
    z-index: 40;
}
```
Dit zit vrij dicht op de css, en kan helpen met tijd besparen.

### SCSS

SCSS, ook wel bekend als SASS, is een manier om je CSS precompiled netjes te noteren.
Voordeel hiervan is dat het een stuk overzichtelijker wordt en je gebruik kan maken van functies.
De SCSS wordt automatisch door WinterCMS gecompiled naar CSS en door de NPM watcher in de public map als CSS gezet.
Als developer zie je dus alleen de pre-compiled SCSS terwijl je in de client met inspector tools de gecompilde CSS ziet.

Zie hieronder 2 voorbeelden die exact dezelfde styling bevatten, de SCSS werk jij als developer in, de CSS zie je in de browser.

#### CSS

```css
#gameboard .game-header-inner .game-countdown .wrapper {
    display: flex;
    flex-basis: 300px;
}

#gameboard .game-header-inner .game-countdown .wrapper a {
    color: blue;
}

#gameboard .game-header-inner .game-countdown .wrapper a:hover {
    color: orange;
}

#gameboard .game-header-inner #timer {
    flex: 1;
    display: flex;
}

#gameboard .game-header-inner #timer div, #gameboard .game-header-inner #timer span, #gameboard .game-header-inner #timer p, #gameboard .game-header-inner #timer a {
    align-items: center;
}
```

#### SCSS

```scss
#gameboard .game-header-inner {
  .game-countdown {
    .wrapper {
      display: flex;
      flex-basis: 300px;

      a {
        color: blue;
        &:hover {
          color: orange;
        }
      }
    }
  }

  #timer {
    flex: 1;
    display: flex;

    div, span, p, a {
      align-items: center;
    }
  }
}
```

Daarnaast bied SCSS mixins en functions. Zie voor meer informatie over alle mogelijkheden met SCSS:
https://sass-lang.com/documentation/style-rules

### CSS variabelen

CSS variabelen zijn variabelen die je browser interpreteert en toepast onder de motorkap.
Je declareert ze standaard binnen ``:root{}`` maar ze kunnen ook op elementen worden of weer overschreven.
In het spelbord zijn zijn allerlei huisstijl kleuren css variabelen.
In /resources/scss/variables.scss staan ze gedeclareerd:

#### Voorbeeld css variabelen

```scss
//theming
:root {
  --bg-prim: #ffa144;
  --text-black: #22292f;
}

.log-bubble-edit:hover {
  background-color: var(--bg-prim);
  cursor: pointer;
  color: var(--text-black);
}
```

Zie voor meer info
https://www.w3schools.com/css/css3_variables.asp

### responsive.js

Om het ddosspelbord responsief te krijgen is veel gebruik gemaakt van CSS SCSS enzovoort ... 
Echter was er een fundamenteel probleem met kleinere schermen, en dit was dat de bovenste navigatiebalk ``<div id="game-header">`` hoger werdt bij het kleiner maken van het scherm. 
Dit resulteerde dat deze overlapte met de ``<div class="party-header">``. Deze zweeft sticky bovenaan de pagina.

Voor meer info over sticky css zie https://www.w3schools.com/howto/howto_css_sticky_element.asp

Onder de party-header zit de ``<div class="action-header">`` deze is ook sticky binnen een action van het spelbord. 
Als de ``<div id="game-header">`` dus groeide door het verkleinen van het scherm, verdwenen ze eronder. 
Om dit op te lossen moet de browser weten hoe ver de ``<div class="party-header">`` en de ``<div class="action-header">`` moeten zitten onder de ``<div id="game-header">``.

Hierdoor zijn de volgende variabelen in het leven geroepen en worden toegepast in de verdere SCSS

```css
:root {
    --gameheader-height: 104px;
    --partyheader-height: 64px;
}

#game-header {
    min-height: var(--gameheader-height);
}

.action-header {
    position: sticky;
    top: calc(var(--gameheader-height) + var(--partyheader-height));
}

/* enzovoort.... */
```

Dit betekent dat de action header altijd zal kijken hoe ver die van de top af moet zitten door de gameheader + de partyheader hoogte bij elkaar op te tellen.
Deze 2 hoogtes staan dus vast in de CSS variabelen, echter kunnen deze gemuteerd worden door Javascript.

Hier komt dus responsive.js in werking, door Js vraagt dit script de hoogtes op van de ``<div id="game-header">``
en de ``<div id="party-header">`` en overschrijft deze dan in de client. 
Het bovenstaande muteren van de css variabelen doet het bij initeren van het spelbord, refreshen, scrollen en als de countdown van de game van mode-1 naar mode-2 gaat met de alarmlichten.

#### Voorbeeld uit responsive.js

```js
root.style.setProperty('--partyheader-height', highestheight + "px");
```

Hierdoor zal altijd de info uit het board onder de gameheader zitten.

## Plugin: ddosspelbord

De plugin is in principe een standaard WinterCMS plugin gebaseerd op laravel, zie hiervoor de al uitgebreide documentatie
https://wintercms.com/docs/setup/installation

https://laravel.com/docs/9.x/readme

Er zijn echter wel wat afwijkende zaken:

### API components

Zoals al eerder genoemd bevat de map /components/ de api's die ingeladen worden vanuit het thema. Omdat die PHP in wintercms is, kun je dus gemakkelijk gegevens wegschrijven, bijvoorbeeld:

```php
$log = new Logs();
$rawdata = base64_decode($raw64data);
$filename = $logattachments[$i]['filename'];
$file = (new File)->fromData($rawdata, $filename);
$file->is_public = true;
$file->save();
$log->attachments()->add($file);
```

Via de eerder genoemde transaction.js wordt het weer teruggeschreven naar de client zonder de pagina te veversen.

```php
(new Feeds())->createTransaction(TRANSACTION_TYPE_LOG, $alog);
```

De api geeft bepaalde data ook weer terug aan de client met de return

```php
return Response::json([
       'result' => $result,
       'message' => $message,
   ]);
```

Dit komt dan weer terug uit in de ``async`` function uit de Vue die de api in eerste instantie aanroepte.

### Gameboard data

Het thema en dus de client kan via het variabel gamedata overal bijkomen. 
Deze wordt geserveerd in components/ddosspelbord_data.php

```php
public function onRun() {
   $this->page['gameboard'] = $this->getGameboardData();
}
```

de functie ``getGameboardData()`` trekt uit wintercms alles en maakt er een object van die dus als het volgt gebruikt wordt in /theme/layouts/ddosgameboard.htm

#### Voorbeeld van backend informatie over parties

```html

<script type="text/javascript" defer>
    window.gameboard_parties = "{{ gameboard.data.parties|raw }}";
</script>
```

## Taal strings in thema
### Theme WN - Language plugin
In theme.yaml in het thema staan language strings voor alle htm bestanden.
```yaml
translate:
  en:
    site.newpass: 'New Password'
```

Deze worden bijvoorbeeld zo aangeroepen:
```php
{{ 'site.newpass'|_ }}
```

### Op maat taal strings voor VUE
Voor strings in Vue wordt gebruik gemaakt van een json die webpack verplaatst naar de public map. 
Vanuit het layout wordt in de header de json globaal ingeladen onder het variabel window.lang. Hierin staat de globaal bereikbare functie 
l". Dit betekent dat je in Vue het volgende kan doen.

```vue
<h3 v-html="l('theme.help')"> </h3>
```

Vue zal de functie 'l' aanroepen met de naam van een language string, vervolgens zal lang.js kijken welke taal er geslecteeerd staat en dan de string vanuit de correcte lang serveren.

Bij het toevoegen, wijzigen of verwijderen van deze lang strings is het belangrijk dat je de volgende commando's uitvoert elke keer:
```shell
php artisan translate:scan --purge
php artisan cache:clear
```

## Security

De backend en bijbehorende api's zorgen voor de beveiliging dat de eindgebruiker ingelogd moet zijn en alleen kan zien wat hij mag zijn volgens functionele regels van het spelbord.

Zet dus nooit (alleen) beperkingen over wat eindgebruiker wel en niet mag in VueJS. Dit is uit te lezen voor een client, altijd de Data via API's uitwisselen. Waar de PHP dan bepaald wat wel en niet mag. 
VueJS is compleet te muteren en modificeren met simpelweg chrome inspector tools.

Denk altijd via de methode "security by design".

