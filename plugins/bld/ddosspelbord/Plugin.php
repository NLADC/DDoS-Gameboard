<?php
/*
 * Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
 *
 * This file is part of the DDoS gameboard.
 *
 * DDoS gameboard is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * DDoS gameboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see @link https://www.gnu.org/licenses/.
 *
 */

namespace bld\ddosspelbord;

use Event;
use Route;
use BackendAuth;
use System\Classes\PluginBase;
use Backend\FormWidgets\FileUpload;
use App;
use Config;
use Laravel\Passport\Passport;
use Illuminate\Foundation\AliasLoader;

include_once (__DIR__ . '/includes/constants.php');

class Plugin extends PluginBase {

    public $elevated = true;// important

    public function pluginDetails()
    {
        return [
            'name'        => 'DDoS gameboard',
            'description' => 'DDoS gameboard',
            'author'      => 'Anti-DDoS Coalitie',
            'icon'        => 'icon-database',
            'homepage'    => 'https://antiddoscoalitie.nl'
        ];
    }


    public function boot() {

        Passport::tokensExpireIn(now()->addHours(4));
        Passport::refreshTokensExpireIn(now()->addDays(2));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        Passport::enablePasswordGrant();

        // Setup required packages for passport
        $this->bootPackages();


        // Listen for menu extendItems
        Event::listen('backend.menu.extendItems', function ( $manager ) {
            $menus = $manager->listMainMenuItems();
            $user = BackendAuth::getUser();

            foreach ( $menus as $menukey => $menu ) {
                // remove menu items when not admin
                if ( $user->is_superuser !== 1 ) {
                    if ( $menu->owner != 'bld.ddosspelbord' ) {
                        $manager->removeMainMenuItem($menu->owner, $menu->code);
                    }
                }
                // Remove Winter.User menu item, is overriden with spelborduser
                if ($menu->code == 'user' && $menu->owner == "Winter.User") {
                    $manager->removeMainMenuItem($menu->owner, $menu->code);
                }
            }


        });

        FileUpload::extend(function ($widget) {
            $path = plugins_path().'/bld/ddosspelbord/widgets/fileupload/partials/';
            $widget->addViewPath($path);
            $widget->bindToController();
        });
    }

    public function register() {

        $this->registerConsoleCommand('ddosspelbord.readFeed', 'bld\ddosspelbord\console\readFeed');
        $this->registerConsoleCommand('ddosspelbord.convert2seeder', 'bld\ddosspelbord\console\convert2seeder');
        $this->registerConsoleCommand('ddosspelbord.measurementAPI', 'bld\ddosspelbord\console\measurementAPI');
        $this->registerConsoleCommand('ddosspelbord.testMeasurements', 'bld\ddosspelbord\console\testMeasurements');
        $this->registerConsoleCommand('ddosspelbord.analyzeAPI', 'bld\ddosspelbord\console\analyzeAPI');

    }

    public function registerComponents() {
        return [
            'bld\ddosspelbord\components\ddosspelbord_attachments' => 'ddosspelbord_attachments',
            'bld\ddosspelbord\components\ddosspelbord_attack' => 'ddosspelbord_attack',
            'bld\ddosspelbord\components\ddosspelbord_data' => 'ddosspelbord_data',
            'bld\ddosspelbord\components\ddosspelbord_targets' => 'ddosspelbord_targets',
            'bld\ddosspelbord\components\ddosspelbord_login' => 'ddosspelbord_login',
            'bld\ddosspelbord\components\ddosspelbord_feed' => 'ddosspelbord_feed',
            'bld\ddosspelbord\components\ddosspelbord_log' => 'ddosspelbord_log',
            'bld\ddosspelbord\components\ddosspelbord_action' => 'ddosspelbord_action',
            'bld\ddosspelbord\components\ddosspelbord_setting' => 'ddosspelbord_setting',
        ];
    }
    public function registerSettings() {
        return [
            'settings' => [
                'label'       => 'DDoS gameboard Settings',
                'description' => 'Manage the DDoS gameboard settings.',
                'category'    => 'DDoS gameboard',
                'icon'        => 'icon-cog',
                'class'       => 'Bld\Ddosspelbord\Models\Settings',
                'order'       => 300,
                'permissions' => ['bld.ddosspelbord.access_settings']
            ],
        ];
    }
    public function registerPermissions() {
        return [
            'bld.ddosspelbord.parties' => [
                'label' => 'Manage Parties (Multi Tenant)',
                'tab' => 'DDOSSpelbord',
                'order' => 700,
            ],
            'bld.ddosspelbord.backendusers' => [
                'label' => 'Manage BackendUsers',
                'tab' => 'DDOSSpelbord',
                'order' => 705,
            ],
            'bld.ddosspelbord.startpage' => [
                'label' => 'Startpage',
                'tab' => 'DDOSSpelbord',
                'order' => 710,
            ],
            'bld.ddosspelbord.spelbordusers' => [
                'label' => 'Spelbordusers',
                'tab' => 'DDOSSpelbord',
                'order' => 720,
            ],
            'bld.ddosspelbord.actions' => [
                'label' => 'Actions',
                'tab' => 'DDOSSpelbord',
                'order' => 730,
            ],
            'bld.ddosspelbord.actionplans' => [
                'label' => 'Action Plans',
                'tab' => 'DDOSSpelbord',
                'order' => 732,
            ],
            'bld.ddosspelbord.apply_actionplans' => [
                'label' => 'Action Plans',
                'tab' => 'DDOSSpelbord',
                'order' => 734,
            ],
            'bld.ddosspelbord.import_actions' => [
                'label' => 'Actions',
                'tab' => 'DDOSSpelbord',
                'order' => 736,
            ],
            'bld.ddosspelbord.export_actions' => [
                'label' => 'Actions',
                'tab' => 'DDOSSpelbord',
                'order' => 738,
            ],
            'bld.ddosspelbord.logs' => [
                'label' => 'Logs',
                'tab' => 'DDOSSpelbord',
                'order' => 740,
            ],
            'bld.ddosspelbord.attacks' => [
                'label' => 'Attacks',
                'tab' => 'DDOSSpelbord',
                'order' => 745,
            ],
            'bld.ddosspelbord.access_settings' => [
                'label' => 'Settings',
                'tab' => 'DDOSSpelbord',
                'order' => 750,
            ],
            'bld.ddosspelbord.monitor' => [
                'label' => 'Access All Monitor or measurements settings',
                'tab' => 'DDOSSpelbord',
                'order' => 770,
            ],
            'bld.ddosspelbord.access_api' => [
                'label' => 'Access API calls',
                'tab' => 'DDOSSpelbord',
                'order' => 780,
            ],
        ];
    }

    public function registerSchedule($schedule) {

        // @TODO; remove RIPE measures cronjob; since okt-2024 the measurements are done with CAIDA ARK
        //$schedule->command('ddosgameboard:measurementAPI measure')->withoutOverlapping()->everyMinute();

    }



    /**
     * Boots (configures and registers) any packages found within this plugin's packages.load configuration value
     *
     * @see https://luketowers.ca/blog/how-to-use-laravel-packages-in-october-plugins
     * @author Luke Towers <info@luketowers.ca>
     */
    public function bootPackages()
    {
        // Get the namespace of the current plugin to use in accessing the Config of the plugin
        $pluginNamespace = str_replace('\\', '.', strtolower(__NAMESPACE__));

        // Instantiate the AliasLoader for any aliases that will be loaded
        $aliasLoader = AliasLoader::getInstance();

        // Get the packages to boot
        $packages = Config::get($pluginNamespace . '::packages');

        // Boot each package
        foreach ($packages as $name => $options) {
            // Setup the configuration for the package, pulling from this plugin's config
            if (!empty($options['config']) && !empty($options['config_namespace'])) {
                Config::set($options['config_namespace'], $options['config']);
            }

            // Register any Service Providers for the package
            if (!empty($options['providers'])) {
                foreach ($options['providers'] as $provider) {
                    App::register($provider);
                }
            }

            // Register any Aliases for the package
            if (!empty($options['aliases'])) {
                foreach ($options['aliases'] as $alias => $path) {
                    $aliasLoader->alias($alias, $path);
                }
            }
        }
    }

    public function registerMailTemplates()
    {
        return [
            'bld.ddosspelbord::mail.activateaccount',
        ];
    }
}
