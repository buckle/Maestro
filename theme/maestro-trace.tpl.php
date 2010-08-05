<?php
// $Id$

/**
 * @file
 * maestro-trace.tpl.php
 */

?>

  <fieldset class="form-wrapper">
    <legend>
      <span class="fieldset-legend">Task History for Process <?php print $properties->process_id; ?></span>
    </legend>
    <div class="fieldset-wrapper">
<?php
      foreach ($trace as $qid => $rec) {
        if ($qid == $properties->queue_id) {
?>
          <b><?php print $qid; ?>: <?php print $rec['taskname']; ?></b><br>
<?php
        }
        else {
?>
          <?php print $qid; ?>: <?php print $rec['taskname']; ?><br>
<?php
        }
      }
?>
    </div>
  </fieldset>

  <fieldset class="form-wrapper">
    <legend>
      <span class="fieldset-legend">Process Variables for Process <?php print $properties->process_id; ?></span>
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
