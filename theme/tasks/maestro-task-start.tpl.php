<?php
// $Id:

/**
 * @file
 * maestro-task-start.tpl.php
 */

  $res = db_query("SELECT taskname FROM {maestro_template_data} WHERE id=:tdid", array('tdid'=>$tdid));
  foreach ($res as $rec) {
?>

<div class="t"><div class="b"><div class="r"><div class="l"><div class="bl"><div class="br"><div class="tl-grn"><div class="tr-grn">

<div class="maestro_task">
  <div class="maestro_task_title maestro_task_title_grn">
    <?php echo $rec->taskname; ?>
  </div>
  <div class="maestro_task_body">
    <?php echo t('Start of Workflow'); ?>
  </div>
</div>

</div></div></div></div></div></div></div></div>

<?php
  }
?>