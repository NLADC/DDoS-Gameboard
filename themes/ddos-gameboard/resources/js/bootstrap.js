window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {
}


/**
 * Load the language string in from the lang.json for the .vue files.
 * These are required for the lang.js to parse through.
 */
$(function () {
    $.ajax({
        type: 'GET',
        url: '/json/lang.json',
        data: {get_param: 'value'},
        dataType: 'json',
        success: function (data) {
            if (data && typeof data === 'object' && data.constructor === Object) {
                window.lang = data;
                if (Object.keys(window.lang).length < 1) {
                    console.error("WARNING themes/ddos-gameboard: Language strings are empty!");
                }
            }
            else {
                console.error("WARNING themes/ddos-gameboard: Due to unknown error lang string are not loaded");
            }

        }
    });
});


/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });


