<div class="InputForm">
    <?= Form::open([ 'class' => 'layout' ]) ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="popup">×</button>
    </div>
    <div class="modal-body">
        <?= $FormWidget->render(); ?>
    </div>


    <div class="modal-footer">
        <button
            type="submit"
            data-request="onCreatePlan"
            data-hotkey="ctrl+s, cmd+s"
            data-load-indicator="<?= e(trans('backend::lang.form.saving')) ?>"
            class="btn btn-primary oc-icon-file-export "
            data-disposable=""
            data-dismiss="popup"
        >
            <?= e(trans('bld.ddosspelbord::lang.create_plan')) ?>
        </button>
        <button type="button" class="btn btn-default" data-dismiss="popup">
            <?= e(trans('backend::lang.form.cancel')) ?>
        </button>
    </div>
    <?= Form::close(); ?>
</div>
</div>
