<?php namespace Bld\Ddosspelbord\Controllers;

use bld\ddosspelbord\helpers\hLog;
use bld\ddosspelbord\helpers\hMail;
use Lang;
use Mail;
use Url;
use App\SpatieModelHasPermission;
use Backend\Classes\Controller;
use BackendMenu;
use League\Csv\Exception;
use October\Rain\Support\Facades\Flash;
use Winter\User\Components\ResetPassword;
use Winter\User\Models\User;

class Spelbordusers extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ImportExportController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public function __construct()
    {
        parent::__construct();
        // neede to set menu highlighted
        BackendMenu::setContext('bld.ddosspelbord', 'Spelbordusers');
    }

    public function index() {

        $this->addJs('/plugins/winter/user/assets/js/bulk-actions.js');
        //$this->addJs('/plugins/bld/ddosspelbord/assets/js/bulk-actions-email.js');
        $this->asExtension('ListController')->index();
    }

    public function listInjectRowClass($record, $definition = null)
    {
        $classes = [];
        $user = User::withTrashed()->find($record->user_id);
        if ($user && $user->deleted_at!=null) {
            $classes[] = 'strike';
        }
        if (count($classes) > 0) {
            return join(' ', $classes);
        }
    }

    /**
     * Perform bulk action on selected users
     */
    public function index_onBulkAction() {
        if (
            ($bulkAction = post('action')) &&
            ($checkedIds = post('checked')) &&
            is_array($checkedIds) &&
            count($checkedIds)
        ) {

            try {

                foreach ($checkedIds as $userId) {
                    if (!$spelborduser = \Bld\Ddosspelbord\Models\Spelbordusers::find($userId)) {
                        continue;
                    }

                    switch ($bulkAction) {
                        case 'delete':
                            $spelborduser->delete();
                            break;

                        case 'activate':
                            $user = User::withTrashed()->find($spelborduser->user_id);
                            if ($user) $user->attemptActivation($user->activation_code);
                            break;

                        case 'deactivate':
                            $user = User::withTrashed()->find($spelborduser->user_id);
                            if ($user) $user->delete();
                            break;
                        case 'sendreset':
                            $user = User::withTrashed()->find($spelborduser->user_id);
                            if ($user) $this->SendResetPasswordEmail($user);
                            break;
                    }

                }

                Flash::success(Lang::get('winter.user::lang.users.'.$bulkAction.'_selected_success'));

            } catch (\Exception $err) {

                Flash::error("Error action '$bulkAction': ".$err->getMessage());

            }

        }
        else {
            Flash::error(Lang::get('winter.user::lang.users.'.$bulkAction.'_selected_empty'));
        }

        return $this->listRefresh();
    }
    protected function SendResetPasswordEmail($user) {
        if (!$user || $user->is_guest) {
            throw new ApplicationException(Lang::get(/*A user was not found with the given credentials.*/'winter.user::lang.account.invalid_user'));
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);

        $baseUrl = Url::to('/');
        $link = $baseUrl . "restore-password/" . $code;

        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];

        hMail::sendMail($user->email,'bld.ddosspelbord::mail.activateaccount',$data );
    }

    /**
     * Returns a link used to reset the user account.
     * @return string
     */
    protected function makeResetUrl($code)
    {
        $params = [
            $this->property('paramCode') => $code
        ];

        if ($pageName = $this->property('resetPage')) {
            $url = $this->pageUrl($pageName, $params);
        }
        else {
            $url = $this->currentPageUrl($params);
        }

        if (strpos($url, $code) === false) {
            $url .= '?reset=' . $code;
        }

        return $url;
    }
}
