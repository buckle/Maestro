<?php
// $Id:

/**
 * @file
 * maestro-task-interactive-function-edit.tpl.php
 */

  $res = db_select('maestro_template_data', 'a');
  $res->fields('a', array('taskname'));
  $res->condition('a.id', $tdid,'=');
  $rec = current($res->execute()->fetchAll());
?>

<table>
  <tr>
    <td><?php print t('Task Name:'); ?></td>
    <td><input type="text" name="taskname" value="<?php print $rec->taskname; ?>"></td>
  </tr>
  <tr>
    <td colspan="2" class="aligncenter"><input type="submit" value="<?php print t('Save'); ?>"></td>
  </tr>
</table>
