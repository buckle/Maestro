<?php
// $Id$

/**
 * @file
 * maestro-trace.tpl.php
 */

?>

  <div>
    <?php print t('Related Workflows'); ?>:
<?php
    foreach ($wf_res as $rec) {
      print l(t($rec->template_name), "maestro/trace/{$rec->tracking_id}/{$rec->initiating_pid}/0") . '&nbsp;&nbsp;';
    }
?>
  </div>

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
    <form method="post" action="<?php url('maestro/trace'); ?>">
      <fieldset class="form-wrapper">
        <legend>
          <span class="fieldset-legend">Task History</span>
        </legend>
        <div class="fieldset-wrapper">
          <table class="sticky-enabled sticky-table">
            <thead class="tableheader-processed">
              <tr>
                <th></th>
                <th><?php print t('Task Name'); ?></th>
                <th><?php print t('Status'); ?></th>
                <th><?php print t('Archived'); ?></th>
              </tr>
            </thead>
            <tbody>
<?php
              $i = 0;
              foreach ($trace as $rec) {
                $classname = ($rec->process_id == $properties->process_id) ? 'focused_process':'blurred_process';
?>
                <tr class="process<?php print $rec->process_id; ?> <?php print $classname; ?> <?php print ($rec->process_id == $properties->process_id) ? 'odd':'even'; ?>">
                  <td>
                    <input type="hidden" id="batch_op<?php print $i; ?>" name="batch_op[]" value="0">
                    <input type="checkbox" onchange="set_batch_op(this, <?php print $i; ?>);" value="1">
                  </td>
                  <td>
                    <input type="hidden" name="queue_id[]" value="<?php print $rec->id; ?>">
                    <?php print $rec->id; ?>: <?php print $rec->taskname; ?>
                  </td>
                  <td>
                    <select name="status[]">
<?php
                      foreach ($statuses as $value => $label) {
?>
                        <option value="<?php print $value; ?>" <?php print ($value == $rec->status) ? 'selected="selected"':''; ?>><?php print $label; ?></option>
<?php
                      }
?>
                    </select>
                  </td>
                  <td>
                    <input type="hidden" id="archived<?php print $i; ?>" name="archived[]" value="<?php print $rec->archived; ?>">
                    <input type="checkbox" value="1" onchange="set_archived(this, <?php print $i; ?>);" <?php print ($rec->archived == 1) ? 'checked="checked"':''; ?>>
                  </td>
                </tr>
<?php
                $i++;
              }
?>
              <tr class="even">
                <td><img src="<?php print $maestro_url; ?>/images/taskconsole/arrow_ltr.png"></td>
                <td><?php print t('Batch Operation'); ?></td>
                <td>
                  <select name="status[]">
<?php
                    foreach ($statuses as $value => $label) {
?>
                      <option value="<?php print $value; ?>" <?php print ($value == $rec->status) ? 'selected="selected"':''; ?>><?php print $label; ?></option>
<?php
                    }
?>
                  </select>
                </td>
                <td></td>
              </tr>
              <tr>
                <td colspan="4" style="text-align: center;"><input type="submit" value="Save All Task Changes"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </fieldset>
    </form>
  </div>

  <div style="width: 40%; float: right;">
    <form method="post" action="<?php url('maestro/trace'); ?>">
      <fieldset class="form-wrapper">
        <legend>
          <span class="fieldset-legend"><?php print t('Process Variables (Current Regeneration Instance)'); ?></span>
        </legend>
        <div class="fieldset-wrapper">
<?php
          $i = 0;
          $prev_rec_pid = 0;
          foreach ($pv_res as $rec) {
            if ($prev_rec_pid != $rec->process_id) {
              $prev_rec_pid = $rec->process_id;
              if ($i++ > 0) {
?>
                  </tbody>
                </table>
<?php
              }
?>
              <table id="process_variables<?php print $rec->process_id; ?>" class="process_variables" style="display: <?php print ($rec->process_id == $properties->process_id) ? '':'none'; ?>;">
                <thead>
                  <tr>
                    <th><?php print t('Variable Name'); ?></th>
                    <th><?php print t('Value'); ?> </th>
                  </tr>
                </thead>
                <tbody>
<?php
            }
?>
            <tr class="even">
              <td>
                <input type="hidden" name="process_variable_id[]" value="<?php print $rec->id; ?>">
                <?php print $rec->variable_name; ?>
              </td>
              <td>
                <input type="text" name="process_variable_value[]" value="<?php print $rec->variable_value; ?>">
              </td>
            </tr>
<?php
          }
?>
            <tr>
              <td colspan="2" style="text-align: center;"><input type="submit" value="<?php print t('Save Process Variables'); ?>"></td>
            </tr>
          </tbody>
        </table>
      </fieldset>
    </form>
  </div>
