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
    <td><?php print t('Variable to Set:'); ?></td>
    <td>
      <select name="var_to_set">
<?php
        foreach ($pvars as $value=>$label) {
          if ($value == $td_rec->task_data['var_to_set']) {
?>
            <option value="<?php print $value;?>" selected="selected"><?php print $label;?></option>
<?php
          }
          else {
?>
            <option value="<?php print $value;?>"><?php print $label;?></option>
<?php
          }
        }
?>
      </select>
    </td>
  </tr>
  <tr>
    <td><?php print t('Hardcoded Value:'); ?></td>
    <td><input type="text" name="var_value" value="<?php print $td_rec->task_data['var_value']; ?>"></td>
  </tr>
  <tr>
    <td colspan="2" style="text-align: center;"><b>- <?php print t('OR'); ?> -</b></td>
  </tr>
  <tr>
    <td><?php print t('Add or Subtract a Value') . '<br>' . t('(negative number for subtraction):'); ?></td>
    <td><input type="text" name="inc_value" value="<?php print $td_rec->task_data['inc_value']; ?>"></td>
  </tr>
</table>
