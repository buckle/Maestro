<?php
// $Id$

/**
 * @file
 * maestro-task-interactive-function-edit.tpl.php
 */

?>

<table>
  <tr>
    <td><?php print t('Task Name:'); ?></td>
    <td><input type="text" name="taskname" value="<?php print $td_rec->taskname; ?>"></td>
  </tr>
  <tr>
    <td><?php print t('Handler:'); ?></td>
    <td><input type="text" name="handler" value="<?php print $td_rec->task_data['handler']; ?>"></td>
  </tr>
  <tr>
    <td><?php print t('Assigned by UID:'); ?></td>
    <td><input type="text" name="uid" value="<?php print $ta_rec->uid; ?>"></td>
  </tr>
  <tr>
    <td><?php print t('Assigned by Variable:'); ?></td>
    <td><input type="text" name="process_variable" value="<?php print $ta_rec->process_variable; ?>"></td>
  </tr>
  <tr>
    <td colspan="2" class="aligncenter"><input type="submit" value="<?php print t('Save'); ?>"></td>
  </tr>
</table>
