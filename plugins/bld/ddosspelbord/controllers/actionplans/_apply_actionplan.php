<div class="InputForm">
    <?= Form::open(['class' => 'layout', 'id' => 'applyActionsForm']) ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="popup">×</button>
    </div>
    <div class="modal-body">

        <?= $FormWidget->render(); ?>
        <input type="hidden" name="ActionPlanId" value="<?= $FormWidget->model->id; ?>"/>
        <div class="form-group  textarea-field span-full   " data-field-name="xxx"
             id="Form-ApplyActionPlanForm-field-xxx-group"><label for="Form-ApplyActionPlanForm-field-xxx">
                Actions Data </label>
            <text name="plandata" id="Form-ApplyActionPlanForm-field-xxx" autocomplete="off"
                      class="form-control field-text size-small" placeholder="" readonly="readonly">
                            <?= $FormWidget->model->makePlanDataSummary() ?>
            </text>
        </div>

    </div>

    <div class="modal-footer">
        <button
            type="button"
            id="submitApplyActions"
            class="btn btn-primary oc-icon-arrows-turn-right "
            data-dismiss="popup"
        >
            <?= e(trans('bld.ddosspelbord::lang.apply_actions')) ?>
        </button>
        <button type="button" class="btn btn-default" data-dismiss="popup">
            <?= e(trans('backend::lang.form.cancel')) ?>
        </button>
    </div>
    <?= Form::close(); ?>
</div>

<script type="text/javascript">
    document.getElementById('submitApplyActions').addEventListener("click", function (e) {
        Snowboard.request('#applyActionsForm', 'onApplyActionPlan', {
            form: '#applyActionsForm',
        });
    });
</script>
