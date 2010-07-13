<?php
// $Id:

/**
 * @file
 * maestro-workflow-edit-tasks-frame.tpl.php
 */

  $res = db_select('maestro_template_data', 'a');
  $res->fields('a', array('task_class_name'));
  $res->condition('a.id', $tdid,'=');
  $rec = current($res->execute()->fetchAll());

  $task_type = substr($rec->task_class_name, 15);
  $task_class = 'MaestroTaskInterface' . $task_type;
?>

<div class="maestro_task_edit_panel">
  <form id="maestro_task_edit_form" method="post" action="" onsubmit="return save_task(this);">
    <input type="hidden" name="task_class" value="<?php print $task_class; ?>">
    <input type="hidden" name="template_task_id" value="<?php print $tdid; ?>">

    <?php print $form_content; ?>

  </form>
</div>

