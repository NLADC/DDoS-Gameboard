<?php

use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;

?>
<!--
  ~ Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
  ~
  ~ This file is part of the DDoS gameboard.
  ~
  ~ DDoS gameboard is free software; you can redistribute it and/or modify
  ~ it under the terms of the GNU General Public License as published by
  ~ the Free Software Foundation; either version 3 of the License, or
  ~ (at your option) any later version.
  ~
  ~ DDoS gameboard is distributed in the hope that it will be useful,
  ~ but WITHOUT ANY WARRANTY; without even the implied warranty of
  ~ MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  ~ GNU General Public License for more details.
  ~
  ~ You should have received a copy of the GNU General Public License
  ~ along with this program; If not, see @link https://www.gnu.org/licenses/.
  ~
  -->

<div data-control="toolbar">
    <?php $user = ddosspelbordUsers::getUser();
    if ($user->hasAccess('bld.ddosspelbord.apply_actionplans')) { ?>
        <a href="<?= Backend::url('bld/ddosspelbord/actions/create') ?>" class="btn btn-primary oc-icon-plus"><?= e(trans('backend::lang.form.create')) ?></a>
    <?php } ?>

    <button
        class="btn btn-default oc-icon-trash-o"
        disabled="disabled"
        onclick="$(this).data('request-data', {
            checked: $('.control-list').listWidget('getChecked')
        })"
        data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>"
        data-trigger-action="enable"
        data-trigger=".control-list input[type=checkbox]"
        data-trigger-condition="checked"
        data-request-success="$(this).prop('disabled', true)"
        data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>

    <?php if ($user->hasAccess('bld.ddosspelbord.apply_actionplans')) { ?>
    <button type="button" data-control="popup" data-handler="onEditAsPlanForm" data-size="giant" data-disposable=""
            data-request-loading="#loading_input"
            data-load-indicator="Saving..."
            class="btn btn-primary oc-icon-pen">
        <?= e(trans('bld.ddosspelbord::lang.edit_as_plan')) ?>
    </button>
    <?php } ?>

</div>
