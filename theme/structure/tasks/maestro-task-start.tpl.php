<?php
// $Id$

/**
 * @file
 * maestro-task-start.tpl.php
 */
?>

<table class="maestro_task">
  <tr>
    <td class="tl-grn"></td>
    <td id="task_title<?php print $tdid; ?>" class="tm-grn maestro_task_title">
      <?php print $taskname; ?>
    </td>
    <td class="tr-grn"></td>
  </tr>
  <tr>
    <td class="bl"></td>
    <td class="bm maestro_task_body">
      <?php print t('Start of Workflow'); ?>
    </td>
    <td class="br"></td>
  </tr>
</table>
