<?php
// $Id:

/**
 * @file
 * maestro-task-batch-edit.tpl.php
 */

?>

<table>
  <tr>
    <td><?php print t('Task Name:'); ?></td>
    <td><input type="text" name="taskname" value="<?php print $td_rec->taskname; ?>"></td>
  </tr>
  <tr>
   <td colspan="2">
      <?php print t('Handler base path:'); ?>
      <?php print $td_rec->task_data['handler_location'];?>
    </td>
  </tr>
  <tr>
    <td><?php print t('Handler:'); ?></td>
    <td><input type="text" name="handler" value="<?php print $td_rec->task_data['handler']; ?>"></td>
  </tr>

  <tr>
    <td colspan="2" class="aligncenter"><input type="submit" value="<?php print t('Save'); ?>"></td>
  </tr>
</table>
