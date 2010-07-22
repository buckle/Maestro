<?php
// $Id$

/**
 * @file
 * maestro-task-set-process-variable-edit.tpl.php
 */

?>

<table>
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
    <td>
      <input type="radio" name="set_type" value="0" onchange="toggle_set_type(0);" <?php print ($td_rec->task_data['set_type'] == 0) ? 'checked="checked"':''; ?>>
      <?php print t('Hardcoded Value:'); ?>
    </td>
    <td><input id="set_type0" type="text" name="var_value" value="<?php print $td_rec->task_data['var_value']; ?>"></td>
  </tr>
  <tr>
    <td>
      <input type="radio" name="set_type" value="1" onchange="toggle_set_type(1);" <?php print ($td_rec->task_data['set_type'] == 1) ? 'checked="checked"':''; ?>>
      <?php print t('Add or Subtract a Value') . '<br>' . t('(negative number for subtraction):'); ?>
    </td>
    <td><input id="set_type1" type="text" name="inc_value" value="<?php print $td_rec->task_data['inc_value']; ?>"></td>
  </tr>
</table>

<script type="text/javascript">

toggle_set_type(<?php print $td_rec->task_data['set_type']; ?>);

function toggle_set_type(type) {
  (function($) {
    var i;

    for (i = 0; i < 2; i++) {
      if (i == type) {
        $('#set_type' + i).show();
      }
      else {
        $('#set_type' + i).hide();
        $('#set_type' + i).attr('value', '');
      }
    }

  })(jQuery);
}

</script>
