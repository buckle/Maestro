<?php
// $Id$

/**
 * @file
 * maestro-workflow-edit-tasks-frame.tpl.php
 */

?>

  <div style="margin: 0px 0px 0px 10px; float: left;">&nbsp;</div>

  <div id="task_edit_tab_main" class="active"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
  <a href="#" onclick="switch_task_edit_section('main'); return false;"><?php print t('Main'); ?></a>
  </div></div></div></div></div></div></div></div></div></div></div>

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

  <div class="active"><div class="maestro_task_edit_tab" style="float: right;"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
  <a href="#" onclick="(function($) { $.modal.close(); })(jQuery); return false;"><?php print t('x'); ?></a>
  </div></div></div></div></div></div></div></div></div></div>

  <div style="clear: both;"></div>

  <div class="maestro_task_edit_panel">
    <div class="t"><div class="b"><div class="r"><div class="l"><div class="bl-wht"><div class="br-wht"><div class="tl-wht"><div class="tr-wht">
      <form id="maestro_task_edit_form" method="post" action="" onsubmit="return save_task(this);">
        <input type="hidden" name="task_class" value="<?php print $task_class; ?>">
        <input type="hidden" name="template_data_id" value="<?php print $tdid; ?>">

        <div id="task_edit_main">
            <?php print $form_content; ?>
        </div>

<?php
  if (array_key_exists('optional', $task_edit_tabs) && $task_edit_tabs['optional'] == 1) {
?>
        <div id="task_edit_optional" style="display: none;">
        </div>
<?php
  }

  if (array_key_exists('assignment', $task_edit_tabs) && $task_edit_tabs['assignment'] == 1) {
?>
        <div id="task_edit_assignment" style="display: none;">
          <table>
            <tr>
              <td colspan="3" style="text-align: center;"><?php print t('Assign User(s) by Hardcoding'); ?></td>
            </tr>
            <tr>
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
            <tr>
              <td colspan="3" style="text-align: center;"><?php print t('Assign User(s) by Process Variable'); ?></td>
            </tr>
            <tr>
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
                      print $value;
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
        <div class="maestro_task_edit_save_div"><input type="submit" value="<?php print t('Save'); ?>"></div>


      </form>
    </div></div></div></div></div></div></div></div>
  </div>

