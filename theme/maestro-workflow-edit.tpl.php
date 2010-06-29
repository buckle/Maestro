<?php
// $Id:

/**
 * @file
 * maestro-workflow-edit.tpl.php
 */

?>

  <div id="workflow_container" style="position: abosolute; height: 500px;">

<?php
  $res = db_query('SELECT id, taskname, task_class_name, is_interactive, offset_left, offset_top FROM {maestro_template_data} WHERE template_id=:tid', array(':tid' => $tid));
  foreach ($res as $rec) {
    $task_type = substr($rec->task_class_name, 15);
    $task_class = 'MaestroTaskInterface' . $task_type;
    $ti = new $task_class($rec->id);
?>
    <div id="task<?php echo $rec->id; ?>" class="maestro_task_container" style="position: absolute; left: <?php echo $rec->offset_left; ?>px; top: <?php echo $rec->offset_top; ?>px;">
<?php
      $ti->display();
?>
    </div>
<?php
  }
?>
  </div>

  <script type="text/javascript">
<?php
    $res = db_query('SELECT id, offset_left, offset_top FROM {maestro_template_data} WHERE template_id=:tid', array(':tid' => $tid));
    $i = 0;
    $j = 0;
    foreach ($res as $rec) {
?>
      existing_tasks[<?php echo $i++; ?>] = ['task<?php echo $rec->id; ?>', <?php echo $rec->offset_left; ?>, <?php echo $rec->offset_top; ?>];
<?php
      $res2 = DB_query("SELECT template_data_to, template_data_to_false FROM {maestro_template_data_next_step} WHERE template_data_from=:tid", array(':tid'=>$rec->id));
      foreach ($res2 as $rec2) {
        $to = intval ($rec2->template_data_to);
        $to_false = intval ($rec2->template_data_to_false);
        if ($to != 0) {
?>
          line_ids[<?php echo $j; ?>] = ['task<?php echo $rec->id; ?>', 'task<?php echo $to; ?>', true];
<?php
          $j++;
        }
        if ($to_false != 0) {
?>
          line_ids[<?php echo $j; ?>] = ['task<?php echo $rec->id; ?>', 'task<?php echo $to_false; ?>', false];
<?php
          $j++;
        }
      }
    }


?>
  </script>
