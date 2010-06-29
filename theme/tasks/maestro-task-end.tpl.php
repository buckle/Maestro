<?php
// $Id:

/**
 * @file
 * maestro-task-start.tpl.php
 */

  $res = db_query("SELECT taskname FROM {maestro_template_data} WHERE id=:tdid", array('tdid'=>$tdid));
  foreach ($res as $rec) {
?>

<div class="t"><div class="b"><div class="r"><div class="l"><div class="bl"><div class="br"><div class="tl-red"><div class="tr-red">

<div class="maestro_task">
  <div class="maestro_task_title maestro_task_title_red">
    <?php echo $rec->taskname; ?>
  </div>
  <div class="maestro_task_body">
    <?php echo t('End of Workflow'); ?>
  </div>
</div>

</div></div></div></div></div></div></div></div>

<?php
  }
?>