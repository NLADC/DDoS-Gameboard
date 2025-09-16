<?php use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;

$planId = (!empty($this->widget->form) && !empty($this->widget->form->model)) ? $this->widget->form->model->id : false;
?>
<div class="control-toolbar">
    <div class="toolbar-item">
        <div data-control="toolbar">
            <?php
            $user = \bld\ddosspelbord\classes\helpers\ddosspelbordUsers::getUser();
            if ($user->hasAccess('bld.ddosspelbord.apply_actionplans')):?>
            <button type="button" id="openApplyActionsModal" data-control="popup" data-handler="onApplyActionPlanModal" data-size="giant" data-disposable=""
                    data-load-indicator="Saving..."
                    class="btn btn-primary oc-icon-arrows-turn-right ert-analyze-disable">
                <?= e(trans('bld.ddosspelbord::lang.apply_actions')) ?>
            </button>
            <?php endif ?>

            <?php
            $user = \bld\ddosspelbord\classes\helpers\ddosspelbordUsers::getUser();
            if ($user->hasAccess('bld.ddosspelbord.import_actions')):?>
                <a href="<?= Backend::url('bld/ddosspelbord/actionplans/import/?actionplan=' . $this->widget->form->model->id)  ?>" class="btn btn-default oc-icon-upload">
                    <?= e(trans('bld.ddosspelbord::lang.import')) ?>
                </a>
            <?php endif ?>

            <?php
            $user = \bld\ddosspelbord\classes\helpers\ddosspelbordUsers::getUser();
            if ($user->hasAccess('bld.ddosspelbord.export_actions')):?>
                <a href="<?= Backend::url('bld/ddosspelbord/actionplans/export/?actionplan=' . $this->widget->form->model->id) ?>" class="btn btn-default oc-icon-download">
                    <?= e(trans('bld.ddosspelbord::lang.export')) ?>
                </a>
            <?php endif ?>


        </div>
    </div>
</div>

<script type="text/javascript">
    /**
     * Unfortunately the datatable from field Actions is not coupled correctly to the wintrercms backend Formcontroller
     * Therefore we must approach the pre saving of the Actions using
     */
    document.getElementById('openApplyActionsModal').addEventListener("click", function (e) {
        const formSaveBtn = document.querySelector('button[data-request="onSave"]');
        if (formSaveBtn) formSaveBtn.click();
    });

</script>
