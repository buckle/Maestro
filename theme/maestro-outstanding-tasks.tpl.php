<?php
// $Id$

/**
 * @file
 * maestro-outstanding-tasks.tpl.php
 */

?>
<fieldset class="form-wrapper">
  <table class="sticky-enabled sticky-table">
    <thead class="tableheader-processed">
      <tr>
        <th><?php print t('Task Name'); ?></th>
        <th><?php print t('Assigned To'); ?></th>
        <th><?php print t('Operation'); ?></th>
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
        <td style="vertical-align: top;"><?php print $task->taskname; ?></td>
        <td style="vertical-align: top;"><?php print $task->username; ?></td>
        <td style="vertical-align: top; width: 200px;">
          <?php print l("<img class=\"valigncenter\" src=\"{$maestro_url}/images/taskconsole/reassign.png\">", "maestro/outstanding/reassign/{$task->queue_id}", array('html' => TRUE, 'attributes' => array('title' => t('Reassign this Task'), 'onclick' => "show_reassign(this, '{$task->uid}'); return false;") )); ?>
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
  <select name="reassign_uid">
    <option value="0"><?php print t('Select User'); ?></option>
<?php
      foreach ($users as $user) {
?>
        <option value="<?php print $user->uid; ?>"><?php print $user->name; ?></option>
<?php
      }
?>
  </select>
</div>
