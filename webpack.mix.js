const mix = require('laravel-mix');
const postCssImport = require('postcss-import');
const tailwind = require('tailwindcss');
const autoprefixer = require('autoprefixer');
const Chart = require('chart.js');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .options({
        processCssUrls: false,
        postCss: [
            postCssImport(),
            tailwind('tailwind.config.js'),
            autoprefixer(),
        ],
        externals: {
            moment: 'moment'
        }
    })

    .vue()
    .js('themes/ddos-gameboard/resources/js/gameboard.js', 'public/themes/ddos-gameboard/assets/js/').vue()
    .js('themes/ddos-gameboard/resources/js/gameboard-lite.js', 'public/themes/ddos-gameboard/assets/js/').vue()
    .js('themes/ddos-gameboard/resources/js/gameboard-targets.js', 'public/themes/ddos-gameboard/assets/js/').vue()
    .js('themes/ddos-gameboard/resources/vendor/jquery.js', 'public/themes/ddos-gameboard/assets/vendor/').vue()
    .sass('themes/ddos-gameboard/resources/scss/theme.scss', 'public/themes/ddos-gameboard/assets/css/')
    .postCss('themes/ddos-gameboard/resources/css/gameboard.css', 'public/themes/ddos-gameboard/assets/css/')

    .styles([
        'themes/ddos-gameboard/resources/fonts/Open Sans/font.css',
        'themes/ddos-gameboard/resources/fonts/Quattrocento/font.css',
        'themes/ddos-gameboard/resources/fonts/Quattrocento_Sans/font.css',
        'themes/ddos-gameboard/resources/fonts/material-icons/font.css',
    ], 'public/themes/ddos-gameboard/assets/css/fonts.css')
    .copy('themes/ddos-gameboard/resources/fonts', 'public/themes/ddos-gameboard/assets/fonts')
    .copy('themes/ddos-gameboard/resources/json', 'public/json')
    .copy('themes/ddos-gameboard/resources/img', 'public/img')
    .copy('themes/ddos-gameboard/resources/favicon.ico', 'public/favicon.ico')
    .sourceMaps()
    .webpackConfig({devtool: 'source-map'});
