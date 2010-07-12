<?php
// $Id:

/**
 * @file
 * maestro-task-start.tpl.php
 */

  $res = db_query("SELECT taskname FROM {maestro_template_data} WHERE task_id=:tdid", array('tdid'=>$tdid));
  foreach ($res as $rec) {
?>

    <?php print $rec->taskname; ?>

<?php
  }
?>
