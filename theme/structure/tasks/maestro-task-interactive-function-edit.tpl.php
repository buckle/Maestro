<?php
// $Id:

/**
 * @file
 * maestro-task-interactive-function-edit.tpl.php
 */

  $res = db_select('maestro_template_data', 'a');
  $res->fields('a', array('taskname'));
  $res->condition('a.id', $tdid, '=');
  $rec = current($res->execute()->fetchAll());

  $res2 = db_select('maestro_template_assignment', 'a');
  $res2->fields('a', array('uid', 'process_variable'));
  $res2->condition('a.template_data_id', $tdid, '=');
  $rec2 = current($res2->execute()->fetchAll());

  if ($rec2 == '') {
    $rec2 = new stdClass();
    $rec2->uid = 0;
    $rec2->process_variable = 0;
  }
?>

<table>
  <tr>
    <td><?php print t('Task Name:'); ?></td>
    <td><input type="text" name="taskname" value="<?php print $rec->taskname; ?>"></td>
  </tr>
  <tr>
    <td><?php print t('Assigned by UID:'); ?></td>
    <td><input type="text" name="uid" value="<?php print $rec2->uid; ?>"></td>
  </tr>
  <tr>
    <td><?php print t('Assigned by Variable:'); ?></td>
    <td><input type="text" name="process_variable" value="<?php print $rec2->process_variable; ?>"></td>
  </tr>
  <tr>
    <td colspan="2" class="aligncenter"><input type="submit" value="<?php print t('Save'); ?>"></td>
  </tr>
</table>
