<?php
namespace bld\ddosspelbord;

use Event;
use Route;
use BackendAuth;
use System\Classes\PluginBase;
use Backend\FormWidgets\FileUpload;

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
            'bld.ddosspelbord.startpage' => [
                'label' => 'Startpage',
                'tab' => 'DDOSSpelbord',
                'order' => 700,
            ],
            'bld.ddosspelbord.parties' => [
                'label' => 'Parties',
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
            'bld.ddosspelbord.logs' => [
                'label' => 'Logs',
                'tab' => 'DDOSSpelbord',
                'order' => 740,
            ],
            'bld.ddosspelbord.access_settings' => [
                'label' => 'Settings',
                'tab' => 'DDOSSpelbord',
                'order' => 750,
            ],
        ];
    }

    public function registerSchedule($schedule) {

        $schedule->command('ddosgameboard:measurementAPI measure')->withoutOverlapping()->everyMinute();

    }


    public function boot() {
        // Listen for menu extendItems
        Event::listen('backend.menu.extendItems', function($manager) {

            // remove menu items when not admin
            $user = BackendAuth::getUser();
            if ($user->is_superuser!==1) {
                // DYNAMIC; remove NO ABUSEIO.SCART menu items
                $menus = $manager->listMainMenuItems();
                foreach ($menus AS $menukey => $menu) {
                    if ($menu->owner!='bld.ddosspelbord') {
                        $manager->removeMainMenuItem($menu->owner, $menu->code);
                    }
                }
            }

        });

        FileUpload::extend(function ($widget) {
            $path = plugins_path().'/bld/ddosspelbord/widgets/fileupload/partials/';
            $widget->addViewPath($path);
            $widget->bindToController();
        });
    }
    public function registerMailTemplates()
    {
        return [
            'bld.ddosspelbord::mail.activateaccount',
        ];
    }
}
