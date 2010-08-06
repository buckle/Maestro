<?php
// $Id$

/**
 * @file
 * maestro-trace.tpl.php
 */

?>

  <div>
    <?php print t('Regeneration Instance'); ?>:
<?php
    foreach ($proc_res as $rec) {
      $checked = ($rec->id == $properties->process_id) ? 'checked="checked"':'';
?>
      <label for="proc_radio<?php print $rec->id; ?>"><input id="proc_radio<?php print $rec->id; ?>" type="radio" name="regen_instance" <?php print $checked; ?> onclick="switch_process_focus(<?php print $rec->id; ?>);"><?php print $rec->id; ?></label>&nbsp;&nbsp;&nbsp;
<?php
    }
?>
  </div>

  <div style="width: 58%; float: left;">
    <fieldset class="form-wrapper">
      <legend>
        <span class="fieldset-legend">Task History</span>
      </legend>
      <div class="fieldset-wrapper">
        <table>
<?php
          foreach ($trace as $rec) {
            $classname = ($rec->process_id == $properties->process_id) ? 'focused_process':'blurred_process';
?>
              <tr class="process<?php print $rec->process_id; ?> <?php print $classname; ?>">
                <td><?php print $rec->id; ?>: <?php print $rec->taskname; ?></td>
                <td><?php print $rec->status; ?></td>
              </tr>
<?php
          }
?>
        </table>
      </div>
    </fieldset>
  </div>
  <div style="width: 40%; float: right;">
    <fieldset class="form-wrapper">
      <legend>
        <span class="fieldset-legend">Process Variables</span>
      </legend>
      <div class="fieldset-wrapper">
<?php
        foreach ($pv_res as $rec) {
?>
          <?php print $rec->variable_name; ?> = <?php print $rec->variable_value; ?><br>
<?php
        }
?>
      </div>
    </fieldset>
  </div>
