<?php

/**
 * @file
 * maestro-outstanding-tasks.tpl.php
 */

?>
<fieldset class="form-wrapper">
  <div style="float: right;">
    <form id="system_tasks_form" action="<?php print url('maestro/outstanding'); ?>" method="POST">
    <div style="float:right;padding-right:5px;padding-top:5px;"><label for="show_system_tasks"><?php print t('Show Hidden Tasks'); ?></label></div>
    <div style="float:right;padding-right:5px;padding-top:5px;"><input type="checkbox" id="show_system_tasks" name="show_system_tasks" value="1" onchange="document.getElementById('system_tasks_form').submit();" <?php print ($show_system_tasks == 1) ? 'checked="checked"':''; ?>></div>
    </form>
  </div>
  <table class="sticky-enabled sticky-table" style="margin-top:0px;width:100%;">
    <thead class="tableheader-processed">
      <tr>
        <th style="width:45%";><?php print t('Task Name'); ?></th>
        <th style="width:45%;"><?php print t('Assigned To'); ?></th>
        <th style="text-align:right;width:10%;"><?php print t('Operation'); ?></th>
      </tr>
    </thead>

    <tbody>

<?php
  if (is_array($queue)) {
    $i = 0;
    foreach ($queue as $task) {
      $classname = ((++$i % 2) == 0) ? 'even':'odd';
?>
      <tr id="ot_row<?php print $i; ?>" class="<?php print $classname; ?>">
        <td style="vertical-align:top;"><?php print $task->taskname; ?></td>
        <td style="vertical-align:top;"><?php print $task->username; ?></td>
        <td style="text-align:right;vertical-align:top;white-space:nowrap;">
          <?php print l("<img class=\"valigncenter\" src=\"{$maestro_url}/images/taskconsole/reassign.png\">", "maestro/outstanding/reassign/{$task->queue_id}", array('html' => TRUE, 'attributes' => array('title' => t('Reassign this Task'), 'onclick' => "show_reassign(this, '{$task->uid}', '{$assign_types[$task->assign_type]}'); return false;") )); ?>
          <?php print l("<img class=\"valigncenter\" src=\"{$maestro_url}/images/taskconsole/email.png\">", "maestro/outstanding/email/{$task->queue_id}/{$task->uid}", array('html' => TRUE, 'attributes' => array('title' => t('Send a Reminder to Task Owner')) )); ?>
          <?php print l("<img class=\"valigncenter\" src=\"{$maestro_url}/images/taskconsole/trace.png\">", "maestro/trace/0/{$task->process_id}/{$task->queue_id}", array('html' => TRUE, 'attributes' => array('title' => t('Trace this Process')) )); ?>
          <?php print l("<img class=\"valigncenter\" src=\"{$maestro_url}/images/taskconsole/delete.png\">", "maestro/outstanding/delete/{$task->queue_id}", array('html' => TRUE, 'attributes' => array('title' => t('Delete this Task'), 'onclick' => "return confirm('" . t('Are you sure you want to delete this task?') . "');") )); ?>
        </td>
      </tr>
<?php
    }
  }
  else {
?>
    <tr>
      <td colspan="3" style="text-align: center; font-style: italic;"><?php print t('There are no outstanding tasks'); ?></td>
    </tr>
<?php
  }
?>

    </tbody>
  </table>
</fieldset>

<div id="user_select" style="display: none;">

  <select name="reassign_id">
    <option value="0"><?php print t('Select User'); ?></option>
<?php
      foreach ($users as $user) {
?>
        <option value="<?php print $user->aid; ?>"><?php print $user->name; ?></option>
<?php
      }
?>
  </select>

</div>
<div id="role_select" style="display: none;">

  <select name="reassign_id">
    <option value="0"><?php print t('Select Role'); ?></option>
<?php
      foreach ($roles as $role) {
?>
        <option value="<?php print $role->aid; ?>"><?php print $role->name; ?></option>
<?php
      }
?>
  </select>

  </div>
<div id="group_select" style="display: none;">

  <select name="reassign_id">
    <option value="0"><?php print t('Select Organic Group'); ?></option>
<?php
      foreach ($groups as $group) {
?>
        <option value="<?php print $group->aid; ?>"><?php print $group->name; ?></option>
<?php
      }
?>
  </select>

</div>
