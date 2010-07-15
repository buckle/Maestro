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

<div style="margin: 0px 0px 0px 10px; float: left;">&nbsp;</div>

<div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-wht-ex"><div class="br-wht-ex"><div class="tl-wht"><div class="tr-wht">
<?php print t('Main'); ?>
</div></div></div></div></div></div></div></div></div>

<div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-wht-ex"><div class="br-wht-ex"><div class="tl-wht"><div class="tr-wht">
<?php print t('Optional'); ?>
</div></div></div></div></div></div></div></div></div>

<div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-wht-ex"><div class="br-wht-ex"><div class="tl-wht"><div class="tr-wht">
<?php print t('Assignment'); ?>
</div></div></div></div></div></div></div></div></div>

<div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-wht-ex"><div class="br-wht-ex"><div class="tl-wht"><div class="tr-wht">
<?php print t('Notifications'); ?>
</div></div></div></div></div></div></div></div></div>

<div style="margin: 0px 10px 0px 0px; float: right;">&nbsp;</div>

<div class="maestro_task_edit_tab" style="float: right;"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-wht-ex"><div class="br-wht-ex"><div class="tl-wht"><div class="tr-wht">
x
</div></div></div></div></div></div></div></div></div>

<div style="clear: both;"></div>

<div class="maestro_task_edit_panel">
  <div class="t"><div class="b"><div class="r"><div class="l"><div class="bl-wht"><div class="br-wht"><div class="tl-wht"><div class="tr-wht">

    <form id="maestro_task_edit_form" method="post" action="" onsubmit="return save_task(this);">
      <input type="hidden" name="task_class" value="<?php print $task_class; ?>">
      <input type="hidden" name="template_data_id" value="<?php print $tdid; ?>">

      <?php print $form_content; ?>

    </form>

  </div></div></div></div></div></div></div></div>
</div>

