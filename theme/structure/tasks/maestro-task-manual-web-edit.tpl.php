<?php
// $Id:

/**
 * @file
 * maestro-task-manual-web-edit.tpl.php
 */

?>

<table>
  <tr>
    <td><?php print t('Task Name:'); ?></td>
    <td><input type="text" name="taskname" value="<?php print $td_rec->taskname; ?>"></td>
  </tr>
  <tr>
    <td><?php print t('Handler URL:'); ?></td>
    <td><input type="text" name="handler" value="<?php print $td_rec->task_data['handler']; ?>"></td>
  </tr>
</table>
