<div class="clearAllDataBox border-gray-600">
    <h4 class=" header">
        Clear all data
    </h4>
    <div class="description">
        BE CAREFUL! these buttons permanently delete data from the database.
    </div>
    <br>
    <button
        type="button"
        data-request="onDeleteAllGameboardData"
        data-request-data="mode: 'logData'"
        data-request-confirm="This will permanently delete all Logs and there associated Attachments"
        data-load-indicator="<?= e(trans('backend::lang.form.deleting')) ?>"
        data-request-url="<?= Backend::url('bld/ddosspelbord/settings') ?>"
        data-request-loading="#loading_input"
        class="btn btn-danger">
        Delete Gameboard Logging Data with Attachments
    </button>

    <button
        type="button"
        data-request="onDeleteAllGameboardData"
        data-request-data="mode: 'userData'"
        data-request-confirm="This will permanently delete all Gameboard Users, there Logging, Attacks and Transactions"
        data-load-indicator="<?= e(trans('backend::lang.form.deleting')) ?>"
        data-request-url="<?= Backend::url('bld/ddosspelbord/settings') ?>"
        data-request-loading="#loading_input"
        class="btn btn-danger">
    Delete Gameboard Users, Attacks and Transactions data
    </button>

    <button
        type="button"
        data-request="onDeleteAllGameboardData"
        data-request-data="mode: 'measurementsData'"
        data-request-confirm="This will permanently delete all Measurements Data, MesurementsConfig, Targets and TargetGroups and Nodelists"
        data-load-indicator="<?= e(trans('backend::lang.form.deleting')) ?>"
        data-request-url="<?= Backend::url('bld/ddosspelbord/settings') ?>"
        data-request-loading="#loading_input"
        class="btn btn-danger">
    Delete Gameboard Measurements Data
    </button>

    <button
        type="button"
        data-request="onDeleteAllGameboardData"
        data-request-data="mode: 'setupData'"
        data-request-confirm="This will permanently delete all Actions and Parties"
        data-load-indicator="<?= e(trans('backend::lang.form.deleting')) ?>"
        data-request-url="<?= Backend::url('bld/ddosspelbord/settings') ?>"
        data-request-loading="#loading_input"
        class="btn btn-danger">
    Delete Actions & Parties
    </button>

    <div id="loading_input" style="display: none;" class="popup-backdrop fade in loading">
        <div class="modal-content popup-loading-indicator indicator-center">
            <span> </span>
        </div>
    </div>

</div>
`
