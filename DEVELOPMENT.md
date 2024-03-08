## Development in DDOS Gameboard

### NPM watcher

Without a watcher, the SCSS, Vue, Js, Assets, etc... won't change when you change them in the root directory. It is not the intention to directly change assets in the "public" folder. Go to a terminal and run the following command:

With every save in a file it will be compiled and transferred to the public folder. Now you can start developing from the main folder.

```shell
 npm run watch-poll
```

### Web pack

Webpack.mix.js describes what the compiler should compile to the public folder. Please note this can also be more files than you see. It is possible to import other files within a js file. We have chosen to keep this file fairly minimal. Import multiple files from one file, and include this one file in the webpack.

### Working with PHPstorm

When developing the game board it is very important to use a strong IDE.
An IDE helps to understand the code. The project consists of many small functions and variables are passed from file to file through parameters a lot. PHPstorm keeps track of the structure and data.

#### xdebug

This tool is very important to view what the APIs receive from the Vue. In addition, it helps enormously to identify errors. The game board's error reporting is sometimes vague and unspecific. Error messages from WinterCMS are not adopted by the game board in the front-end.
By going through the code via Xdebug you can sometimes find out these error messages. See documentation here: https://www.jetbrains.com/help/phpstorm/configuring-xdebug.html

#### VueJs Debug

Possibly even more important than Xdebug is being able to debug the Vue code. See all information about setting up Vue Debugging here
https://www.jetbrains.com/help/phpstorm/vue-js.html#ws_vue_running_and_debugging

They can also be used together, so you put breakpoint in an async function that is linked to an API.
Then PHP storm will first pause in the VueJS and then pause in the PHP api code, then pause in the VueJS.

I do not recommend working with var_dump, print_r, console_log, this does not provide the right options to really understand what happens to the data via APIs.

## Javascript and Vue

*To read the documentation, it is useful to first have the game board open in the browser*

The theme: "ddos game board" actually contains all files that will be transferred to the public folder via the vue compiler.

### Layouts: ddos-gameboard.htm

Here is the .htm file that is always loaded. Very important is the ``<head>``.

```html
<script type="text/javascript" defer> window.gameboard_logs = "{{ gameboard.data.logs|raw }}";</script>
```
Above, data is imported from the backend. The variable: "gameboard" is the link between the theme and the backend: plugins/bld/ddosgameboard.
This contains all the information that the theme, and therefore the client, can access, the rest is shielded.

### Home.htm

In Home we find the highest layer of html as the game board is displayed in the browser. In fact, it is rather a .vue file, because there is Vue syntax present.

For all documentation regarding Vue syntax see: https://vuejs.org/guide/essentials/template-syntax.html#attribute-bindings

#### Variables

All variables like "user", "logs" or "parties" come from themes/ddos-gameboard/resources/js/gameboard.js under data() {....

#### Vue components

Not all html is in home.htm, for example the GameCountdown. These are the large countdown counter at the top of the game board. This is loaded with:

```html

<game-countdown starttime="{{ gameboard.starttime }}"
                endtime="{{ gameboard.endtime }}"></game-countdown>
```

The html can be found in themes/ddos-gameboard/resources/js/components/GameCountdown.vue

This is linked together via gameboard.js with

```vue
Vue.component('game-countdown', require('./components/GameCountdown.vue').default);
```

gameboard.starttime are variables coming from the backend.

*See documentation at vuejs.org/guide for more info*

### pages/api

This contains the htm files that make it possible to communicate with the backend via the VueJS.
Called via inline PHP in the htm file via onStart() to which function the data should be sent from the client.
These are of course functions from the file indicated above, such as:
```php
use bld\ddosgameboard\components\ddosgameboard_attachments;
```
All api files with the functions can be found in ``plugins/bld/ddosspelboard/components/ddosspelbord_<api>.php``

### gameboard.js

This Js file is the top level of your VueJS. From here all .vue files are merged into one working javascript.
For the targets dashboard this is gameboard-targets.js

From "layous\ddos-gameboard.htm" all Vue is loaded via gameboard.js with the piece of HTML below.

```html

<script src="{{ 'assets/js/gameboard.js'|theme }}" defer></script>
```

#### Vue Components

Vue components are located in /resources/js/components/ and are loaded into the gameboard.js like this:

```js
Vue.component('timeline', require('./scheduler/Timeline.vue').default);
Vue.component('party', require('./scheduler/Party.vue').default);
```

As mentioned before, you can load variables via the home.htm by passing them as an attribute:

```html

<game-countdown starttime="{{ gameboard.starttime }}"
                 endtime="{{ gameboard.endtime }}"></game-countdown>
```

in the component starttime and endtime will come back as props

```vue
props: {
starttime: String,
endtime: String
},
```

Props are initial values, you can't mutate them, so the start time can never be changed from within GameCountdown.vue. \
Well if the prop changes from above, in this case gameboard.js possible via php then this will be passed to the child component
More about props: https://vuejs.org/guide/components/props.html

#### Global vue variables

There are settings from the backend that are used from the code. These are simply addressed in the code by entering ``this.logmaxfilesize``.
This is done by adding things to the prototype, normally you use the data(), only this can be useful to enter global settings of the backend for example. Like the limit of files you can log or how big they are.

```js
Vue.prototype.logmaxfilesize = window.gameboard_logmaxfilesize;
Vue.prototype.logmaxfiles = window.gameboard_logmaxfiles;
Vue.prototype.acceptedFileTypes = window.gameboard_acceptedfiletypes.split(',');
```

*Note here gameboard is the variable that is passed from the backend

#### Event $on and $emit

Communication of data from gameboard.js to components is the normal data flow. Mutation from Vue components to gameboard.js is not allowed. There is a way to request an event, this is possible by $on and $emit. Below is a simple example

in AttachmentModal.vue

```vue
methods: {
     close() {
         Event.$emit('emptyAttachmentsmodal');
     }
}
```

in gameboard.js

```js
Event.$on('emptyAttachmentsmodal', () => {
         // code to be excecuted
     }
);
```

This will be called when in a AttachmentModal the close() function is executed it makes a call via `Event` to the function ``emptyAttachmentsmodal``inside gameboard.js. 
`Event` can receive and execute this call. This causes gameboard.js to mutate ``this.attachmentmodal``.
This variable ``this.attachmentmodel`` is then passed to the AttachmentModal.vue component. As a result, gameboard.js mutates its components and not the other way around.

### transaction.js

From the client side, this script is the mechanism that arranges transactions with the backend (php) on the server.

Below is an example of a log that is written via a transaction from the log API plugins/gameboard/
/components/ddospelbord_log.php.

```php
  // get vue code values & create transaction
  $alog = ddospelbord_data::getGameboardLog($log, $hasattachments);
  (new Feeds())->createTransaction(TRANSACTION_TYPE_LOG, $alog);
```

``$alog`` is an object containing the log data coming from the client (VueJs); The log data is therefore synchronized back via the ``createtransaction()`` function and eventually sent to the game board via transaction.js.

Transaction.js is primary important when party members want to have updates and latest data from the gameboard without having to refresh the browser.

The precise operation behind transaction.js can be documented much better....

## Theme

How the DDOS game board looks is largely determined by the theming.
This works with a combination of SCSS, JS, CSS and the final HTML skeleton.

### CSS

CSS is the styling code that the browser can execute directly.
In themes/ddos-gameboard/resoucres/css, gameboard.css in particular is important for further developing the theming.
For information on how CSS works, W3Schools is a very strong learning platform.

https://www.w3schools.com/css/

### Tailwind

Tailwind was a choice by the original creator of the ddos game board to be able to theme without having to write much or even any css.
The original CSS and source code can be found in /node_modules/tailwindcss
So you can write in the html:

Tailwind HTML:

```html
<div class="h-16 text-2xl p-6 text-red-400">DDoS gameboard</div>
```

This then results in the following css being loaded on this div:

CSS that browser receives:

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

#### Advice

The use of html DOM elements without clear names or structures can be confusing for frontend-developers who want to build on the theming.
The div from the example mentioned above is a header of the login screen.
This is not immediately obvious to an external developer.
In addition, the classnames ``h-16 text-2xl p-6 text-red-400`` are messy. 
Styling belongs in css that is linked to a class name. 
Below is an example of the same HTML and CSS but in the correct way.

Correct HTML:

```html

<div id="loginheading">DDoS gameboard</div>
```

CSS from the browser:

```css
#loginheading {
    color: rgb(248, 113, 113);
    font-size: 1.5rem;
    line-height: 2rem;
    padding: 1.5rem;
    height: 4rem;
}
```

The CSS is identical to the CSS that can be found in your resources/css.
The disadvantage is that you then have to write that CSS instead of quickly putting the tailwind classes in the html.

#### Tailwind within CSS

Another way works with ``@apply`` so you can do the following in gameboard.css for example:

```css
.action .sticky-top {
     @apply sticky top-36 z-40;
}
```

this resulted in the client's browser as follows:

```css
.action .sticky-top {
    position: sticky;
    top: 9rem;
    z-index: 40;
}
```
This is pretty close to the CSS, and can help save time.

### SCSS

SCSS, also known as SASS, is a way to neatly write down your CSS precompiled.
The advantage of this is that it becomes a lot cleaner, you can also use functions.
The SCSS is automatically compiled to CSS by WinterCMS and put in the public map as CSS by the NPM watcher (webpack).
So as a developer you only see the pre-compiled SCSS, while in the client with inspector tools you see the compiled CSS.

See below 2 examples that contain exactly the same styling, you work in the SCSS as a developer, you see the CSS in the browser.

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
     align items: center;
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

In addition, SCSS offers mixins and functions. For more information about all possibilities with SCSS, see:
https://sass-lang.com/documentation/style-rules

### CSS variables

CSS variables are variables that your browser interprets and applies under the hood.
By default you declare them inside ``:root{}`` but they can also be written on elements or overwritten again.
In the game board are all kinds of brand colors css variables.
In /resources/scss/variables.scss they are declared:

#### Example css variables

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

See for more info
https://www.w3schools.com/css/css3_variables.asp

### responsive.js

To make the ddos game board responsive, a lot of use has been made of CSS SCSS and so on ...
However, there was a fundamental problem with smaller screens, and this was that the top navigation bar ``<div id="game-header">`` got taller when the screen was shrunk.
This resulted in it overlapping with the ``<div class="party-header">``. This is floating sticky at the top of the page.

For more info about sticky css see https://www.w3schools.com/howto/howto_css_sticky_element.asp

Below the party header is the ``<div class="action-header">`` which is also sticky within an action of the game board.
So if the ``<div id="game-header">`` grew by shrinking the screen, they disappeared below it.
To fix this, the browser needs to know how far the ``<div class="party-header">`` and the ``<div class="action-header">`` should be below the ``<div id="game header">``.

As a result, the following variables have been created and will be used in the further SCSS

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

/* and so on.... */
```

This means that the action header will always check how far it should be from the top by adding the game header + the party header height.
These 2 heights are therefore fixed in the CSS variables, but they can be mutated by Javascript.

This is where responsive.js comes into effect, through Js this script requests the heights of the ``<div id="game-header">``
and the ``<div id="party-header">`` and then overrides it in the client.
The above mutating of the css variables works when initiating the game board, refreshing, scrolling and when the game countdown goes from mode-1 to mode-2 with the hazard lights.

#### Example from responsive.js

```js
root.style.setProperty('--partyheader-height', highestheight + "px");
```

As a result, the info from the board will always be under the game header.

## Plugin: ddosgameboard

The plugin is basically a standard WinterCMS plugin based on laravel, see the already extensive documentation for this
https://wintercms.com/docs/setup/installation

https://laravel.com/docs/9.x/readme

However, there are some differences:

### API components

As mentioned before, the /components/ directory contains the APIs that are loaded from the theme. Because that PHP is in wintercms, you can easily write data, for example:

```php
$log = new Logs();
$rawdata = base64_decode($raw64data);
$filename = $logattachments[$i]['filename'];
$file = (new File)->fromData($rawdata, $filename);
$file->is_public = true;
$file->save();
$log->attachments()->add($file);
```

It is written back to the client via the before mentioned transaction.js without refreshing the page.

```php
(new Feeds())->createTransaction(TRANSACTION_TYPE_LOG, $alog);
```

The api also returns certain data to the client with the return

```php
return Response::json([
       'result' => $result,
       'message' => $message,
   ]);
```

This then feeds back into the ``async`` function from the Vue js file that initially called the upon the function in the plugin.

### Game board data

The theme and thus the client can acces the gameboard data from anywhere via the `gamedata` variable.
This is served in components/ddospelbord_data.php

```php
public function onRun() {
    $this->page['gameboard'] = $this->getGameboardData();
}
```

the function ``getGameboardData()`` pulls everything from wintercms and turns it into an object which is used in /theme/layouts/ddosgameboard.htm as follows

#### Example of backend information about parties

```html
<script type="text/javascript" defer>
    window.gameboard_parties = "{{ gameboard.data.parties|raw }}";
</script>
```

## Language string in theme
### Theme WN - Language plugin
The theme.yaml in the theme contains language strings for all htm files.
```yaml
translate:
  en:
    site.newpass: 'New Password'
```

For example, they are called like this:
```php
{{ 'site.newpass'|_ }}
```

When adding, changing or removing these lang strings it is important that you run the following commands every time:
```shell
php artisan translate:scan --purge
php artisan cache:clear
```

### Custom lang strings for VUE
Strings in Vue reference to the same /lang/lang.yaml used by the translate plugin.
The plugin will make the yaml avaiable in the `window.lang` variable. 
The window also contains the globally reachable function `l()`. This means that in Vue you can do the following.

```vue
<h3 v-html="l('theme.help')"> </h3>
```

Vue will call function `l` with the name of a language string, then lang.js will check which language is selected and then serve the string from the correct lang.

#### Warning
Editing the strings through the backend `/backend/winter/translate/messages` has no effect on the Vue strings, you must use the `/themes/ddos-gameboard/lang/lang.yaml`

## Security

The backend and associated APIs provide security that the end user must be logged in and can only see what he is allowed to be according to functional rules of the game board.

So never put restrictions on what end users can and can't do in VueJS only. A client can extract everything Vue JS related, always exchange the Data via APIs. 
Where the PHP then determines what is and what is not allowed.
VueJS is completely mutable and modifiable with simple inspector tools or more advanced XSS hacking tools

Always think via the "security by design" method.
    

## Target dashboards

#### via browser uitlezen

```
https://atlas.ripe.net/api/v2/measurements/MEASUREMENT_ID
```


