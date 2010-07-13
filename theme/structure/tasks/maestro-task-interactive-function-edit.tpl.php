<?php
// $Id:

/**
 * @file
 * maestro-task-start.tpl.php
 */

?>

<div class="maestro_task_edit_panel">
  <table>
    <tr>
      <td><?php print t('Task Name:'); ?></td>
      <td><?php print drupal_render($form['taskname']); ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php print drupal_render($form['submit']); ?></td>
    </tr>
  </table>

  <?php print drupal_render($form); ?>
</div>

