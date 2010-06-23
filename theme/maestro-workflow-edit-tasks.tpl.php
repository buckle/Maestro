<?php
// $Id:

/**
 * @file
 * maestro-workflow-edit-tasks.tpl.php
 */

  $res = db_query('SELECT id, taskname, task_class_name, is_interactive FROM {maestro_template_data} WHERE template_id=:tid', array(':tid' => $tid));
  foreach ($res as $rec) {
    $task_type = str_replace('MaestroTaskType', '', $rec->task_class_name);
    switch ($rec->task_class_name) {
    case 'MaestroTaskTypeIf':
      $task_class = 'nf_if';
      break;
    case 'MaestroTaskTypeStart':
      $task_class = 'nf_start';
      break;
    case 'MaestroTaskTypeEnd':
      $task_class = 'nf_end';
      break;
    default:
      if ($rec->is_interactive == 1) {
        $task_class = 'nf_interactive';
      }
      else {
        $task_class = 'nf_noninteractive';
      }
      break;
    }

?>

    <div id="task<?php echo $rec->id;?>" class="nf_task_div" id="div_task<?php echo $rec->id;?>" onClick="header_clicked(this);">
      <table class="nf_task">
        <tr id="task<?php echo $rec->id;?>_handle" class="<?php echo $task_class; ?>">
          <td id="task<?php echo $rec->id;?>_title" class="nf_task_header" width="100%"><?php echo t($rec->taskname); ?></td>
        </tr>
        <tr>
          <td class="nf_task_body">
            <input type="hidden" id="task<?php echo $rec->id;?>_id" name="task_id[]" value="<?php echo $rec->id;?>">
            <input type="hidden" id="task<?php echo $rec->id;?>_left" name="task_left[]" value="{offsetLeft}">
            <input type="hidden" id="task<?php echo $rec->id;?>_top" name="task_top[]" value="{offsetTop}">
            <input type="hidden" id="task<?php echo $rec->id;?>_type" name="task_type[]" value="{task_type_underscored}">
            <?php echo t('Task Type'); ?>: <?php echo t($task_type); ?>
            <div id="task<?php echo $rec->id;?>_assignment">
              {task_assignment}
            </div>
          </td>
        </tr>
      </table>
    </div>

<?php
  }
?>
