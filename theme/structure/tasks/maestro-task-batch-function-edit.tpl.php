<?php
// $Id:

/**
 * @file
 * maestro-task-batch-function-edit.tpl.php
 */

?>

<table>
  <tr>
    <td><?php print t('Task Name:'); ?></td>
    <td><input type="text" name="taskname" value="<?php print $td_rec->taskname; ?>"></td>
  </tr>
  <tr>
   <td colspan="2">
      <?php print t('Handler must go in the file:'); ?>
      <?php print $td_rec->task_data['handler_location'];?>
    </td>
  </tr>
  <tr>
    <td style="vertical-align: top"><?php print t('Handler function:'); ?></td>
    <td>
      <input type="text" name="handler" value="<?php print $td_rec->task_data['handler']; ?>"><br></br>
      <span style="font-size:x-small">*Enter only the function name such as: maestro_batchFunctionExample</span>
    </td>
  </tr>

  <tr>
    <td colspan="2" class="aligncenter"><input type="submit" value="<?php print t('Save'); ?>"></td>
  </tr>
</table>
