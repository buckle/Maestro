<?php
// $Id:

/**
 * @file
 * maestro-workflow-edit.tpl.php
 */

?>

  <div id="maestro_ajax_indicator" class="maestro_ajax_indicator" style="display: none;"><img src="<?php print $maestro_url; ?>/images/admin/status-active.gif"></div>
  <form name="frm_animate" action="#" method="post">
      <?php print t('Enable Animation'); ?>: <input type="checkbox" name="animateFlag" value="1" checked="checked">&nbsp;&nbsp;&nbsp;
      <?php print t('Snap to Grid'); ?>: <input type="checkbox" name="snapToGrid" value="1" onclick="update_snap_to_grid();">&nbsp;&nbsp;&nbsp;
      <?php print t('Snap to Objects'); ?>: <input type="checkbox" name="snapToObjects" value="1" onclick="update_snap_to_objects();" checked="checked">
  </form>

  <div id="maestro_tool_tip" class="maestro_tool_tip"></div>

  <div id="maestro_workflow_container" class="maestro_workflow_container" style="position: abosolute; height: 500px;">

<?php
  $res = db_query('SELECT id, taskname, task_class_name, is_interactive, offset_left, offset_top FROM {maestro_template_data} WHERE template_id=:tid', array(':tid' => $tid));
  $task_js = '';
  foreach ($res as $rec) {
    $task_type = substr($rec->task_class_name, 15);
    $task_class = 'MaestroTaskInterface' . $task_type;
    $ti = new $task_class($rec->id);

    $task_js .= $ti->getContextMenuJS();
?>
    <div id="task<?php print $rec->id; ?>" class="<?php print $task_class; ?> maestro_task_container" onclick="draw_line_to(this);" style="position: absolute; left: <?php print $rec->offset_left; ?>px; top: <?php print $rec->offset_top; ?>px;">
<?php
      $ti->display();
?>
    </div>
    <?php print $ti->getContextMenuHTML(); ?>
<?php
  }

  print $mi->getContextMenuHTML();
?>

  <script type="text/javascript">
    var ajax_url = '<?php print $ajax_url; ?>';
    <?php print $additional_js; ?>
    <?php print $mi->getContextMenuJS(); ?>
    <?php print $task_js; ?>
  </script>
