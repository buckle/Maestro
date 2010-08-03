<?php
// $Id$

/**
 * @file
 * maestro-workflow-edit-tasks-frame.tpl.php
 */

?>

<div>
  <div style="margin: 0px 0px 0px 10px; float: left;">&nbsp;</div>

  <div id="task_edit_tab_main" class="active"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
  <a href="#" onclick="switch_task_edit_section('main'); return false;"><?php print t('Main'); ?></a>
  </div></div></div></div></div></div></div></div></div></div>

<?php
  if (array_key_exists('optional', $task_edit_tabs) && $task_edit_tabs['optional'] == 1) {
?>
    <div id="task_edit_tab_optional" class="unactive"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
    <a href="#" onclick="switch_task_edit_section('optional'); return false;"><?php print t('Optional'); ?></a>
    </div></div></div></div></div></div></div></div></div></div>
<?php
  }
?>

<?php
  if (array_key_exists('assignment', $task_edit_tabs) && $task_edit_tabs['assignment'] == 1) {
?>
    <div id="task_edit_tab_assignment" class="unactive"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
    <a href="#" onclick="switch_task_edit_section('assignment'); return false;"><?php print t('Assignment'); ?></a>
    </div></div></div></div></div></div></div></div></div></div>
<?php
  }
?>

<?php
  if (array_key_exists('notification', $task_edit_tabs) && $task_edit_tabs['notification'] == 1) {
?>
    <div id="task_edit_tab_notification" class="unactive"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
    <a href="#" onclick="switch_task_edit_section('notification'); return false;"><?php print t('Notification'); ?></a>
    </div></div></div></div></div></div></div></div></div></div>
<?php
  }
?>

  <div style="margin: 0px 10px 0px 0px; float: right;">&nbsp;</div>

  <div class="active"><div class="maestro_task_edit_tab_close" style="float: right;"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-cl"><div class="br-cl"><div class="tl-cl"><div class="tr-cl">
  <a href="#" onclick="(function($) { $.modal.close(); })(jQuery); return false;"><img src="<?php print $maestro_url; ?>/images/admin/close.png"></a>
  </div></div></div></div></div></div></div></div></div></div>

  <div style="clear: both;"></div>

  <div class="maestro_task_edit_panel">
    <div class="t"><div class="b"><div class="r"><div class="l"><div class="bl-wht"><div class="br-wht"><div class="tl-wht"><div class="tr-wht">
      <form id="maestro_task_edit_form" method="post" action="" onsubmit="return save_task(this);">
        <input type="hidden" name="task_class" value="<?php print $task_class; ?>">
        <input type="hidden" name="template_data_id" value="<?php print $tdid; ?>">

        <div id="task_edit_main">
          <div style="float: none;" class="maestro_tool_tip maestro_taskname"><div class="t"><div class="b"><div class="r"><div class="l"><div class="bl-bge"><div class="br-bge"><div class="tl-bge"><div class="tr-bge">
            <?php print t('Task Name'); ?>: <input id="maestro_task_name" type="text" name="taskname" value="<?php print $vars->taskname; ?>"><br>
            <label for="regen"><input type="checkbox" id="regen" name="regen" value="1" <?php print ($vars->regenerate == 1) ? 'checked="checked"':''; ?>><?php print t('Regenerate This Task'); ?></label>&nbsp;&nbsp;&nbsp;
            <label for="regenall"><input type="checkbox" id="regenall" name="regenall" value="1" <?php print ($vars->regen_all_live_tasks == 1) ? 'checked="checked"':''; ?>><?php print t('Regenerate All In-Production Tasks'); ?></label>
          </div></div></div></div></div></div></div></div></div><br />

          <?php print $form_content; ?>
        </div>

<?php
  if (array_key_exists('optional', $task_edit_tabs) && $task_edit_tabs['optional'] == 1) {
?>
        <div id="task_edit_optional" style="display: none;">
          <table style="display: none;">
            <tbody id="optional_parm_form">
              <tr>
                <td width="33%" style="vertical-align: top; white-space: nowrap;">
                  <input type="text" name="op_var_names[]" value="" style="width: 150px;">
                  <a href="#" onclick="remove_variable(this); return false;"><img class="valigncenter" src="<?php print $maestro_url; ?>/images/admin/remove.png" style="vertical-align: middle;"></a>
                </td>
                <td width="67%"><textarea name="op_var_values[]" rows="1" cols="32"></textarea></td>
              </tr>
            </tbody>
          </table>

          <fieldset class="form-wrapper">
            <legend><span class="fieldset-legend"><a href="#" onclick="add_variable(); return false;"><?php print t('Add Variable'); ?></a></span></legend>

            <div class="fieldset-wrapper">
            <table class="sticky-enabled sticky-table">
              <thead class="tableheader-processed">
                <tr>
                  <th><?php print t('Variable Name'); ?></th>
                  <th><?php print t('Variable Value'); ?></th>
                </tr>
              </thead>
              <tbody id="optional_parm_vars">
<?php
                $i = 0;
                foreach ($optional_parms as $var_name => $var_value) {
                  $classname = ((++$i % 2) == 0) ? 'even':'odd';
?>
                  <tr class="<?php print $classname; ?>">
                    <td width="33%" style="vertical-align: top; white-space: nowrap;">
                      <input type="text" name="op_var_names[]" value="<?php print $var_name; ?>" style="width: 150px;">
                      <a href="#" onclick="remove_variable(this); return false;"><img src="<?php print $maestro_url; ?>/images/admin/remove.png" style="vertical-align: middle;"></a>
                    </td>
                    <td width="67%"><textarea name="op_var_values[]" rows="1" cols="32"><?php print $var_value; ?></textarea></td>
                  </tr>
<?php
                }
?>
                </tbody>
              </table>
            </div>
          </fieldset>
        </div>
<?php
  }

  if (array_key_exists('assignment', $task_edit_tabs) && $task_edit_tabs['assignment'] == 1) {
?>
        <div id="task_edit_assignment" style="display: none;">
          <table>
            <tr>
              <td colspan="3" style="text-align: center;">
                <label for="assigned_by_variable_opt1"><input type="radio" id="assigned_by_variable_opt1" name="assigned_by_variable" value="0" onchange="toggle_assignment(0);" <?php print ($vars->assigned_by_variable == 0) ? 'checked="checked"':''; ?>><?php print t('Assign User(s) by Hardcoding'); ?></label>&nbsp;&nbsp;&nbsp;
                <label for="assigned_by_variable_opt2"><input type="radio" id="assigned_by_variable_opt2" name="assigned_by_variable" value="1" onchange="toggle_assignment(1);" <?php print ($vars->assigned_by_variable == 1) ? 'checked="checked"':''; ?>><?php print t('Assign User(s) by Process Variable'); ?></label>
              </td>
            </tr>
            <tr id="assign_by_uid_row">
              <td>
                <select size="4" multiple="multiple" style="width: 200px;" id="assign_by_uid_unselected">
<?php
                  foreach ($uid_options as $value => $rec) {
                    if ($rec['selected'] == 0) {
?>
                      <option value="<?php print $value; ?>"><?php print $rec['label']; ?></option>
<?php
                    }
                  }
?>
                </select>
              </td>
              <td>
                <a href="#" onclick="move_to_left('uid'); return false;"><img src="<?php print $maestro_url; ?>/images/admin/left-arrow.png"></a>
                &nbsp;&nbsp;&nbsp;
                <a href="#" onclick="move_to_right('uid'); return false;"><img src="<?php print $maestro_url; ?>/images/admin/right-arrow.png"></a>
              </td>
              <td>
                <select size="4" multiple="multiple" style="width: 200px;" id="assign_by_uid" name="assign_by_uid[]">
<?php
                  foreach ($uid_options as $value => $rec) {
                    if ($rec['selected'] == 1) {
?>
                      <option value="<?php print $value; ?>"><?php print $rec['label']; ?></option>
<?php
                    }
                  }
?>
                </select>
              </td>
            </tr>
            <tr id="assign_by_pv_row">
              <td>
                <select size="4" multiple="multiple" style="width: 200px;" id="assign_by_pv_unselected">
<?php
                  foreach ($pv_options as $value => $rec) {
                    if ($rec['selected'] == 0) {
?>
                      <option value="<?php print $value; ?>"><?php print $rec['label']; ?></option>
<?php
                    }
                  }
?>
                </select>
              </td>
              <td>
                <a href="#" onclick="move_to_left('pv'); return false;"><img src="<?php print $maestro_url; ?>/images/admin/left-arrow.png"></a>
                &nbsp;&nbsp;&nbsp;
                <a href="#" onclick="move_to_right('pv'); return false;"><img src="<?php print $maestro_url; ?>/images/admin/right-arrow.png"></a>
              </td>
              <td>
                <select size="4" multiple="multiple" style="width: 200px;" id="assign_by_pv" name="assign_by_pv[]">
<?php
                  foreach ($pv_options as $value => $rec) {
                    if ($rec['selected'] == 1) {
?>
                      <option value="<?php print $value; ?>"><?php print $rec['label']; ?></option>
<?php
                    }
                  }
?>
                </select>
              </td>
            </tr>
          </table>
        </div>
<?php
  }

  if (array_key_exists('notification', $task_edit_tabs) && $task_edit_tabs['notification'] == 1) {
?>
        <div id="task_edit_notification" style="display: none;">
        </div>
<?php
  }
?>
        <div class="maestro_task_edit_save_div"><input class="form-submit" type="submit" value="<?php print t('Save'); ?>"></div>

      </form>
    </div></div></div></div></div></div></div></div>
  </div>
</div>
