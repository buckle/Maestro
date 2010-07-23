<?php
// $Id$

/**
 * @file
 * maestro-task-end.tpl.php
 */
?>

<table class="maestro_task">
  <tr>
    <td class="tl-red"></td>
    <td id="task_title<?php print $tdid; ?>" class="tm-red maestro_task_title">
      <?php print $taskname; ?>
    </td>
    <td class="tr-red"></td>
  </tr>
  <tr>
    <td class="bl"></td>
    <td class="bm maestro_task_body">
      <?php print t('End of Workflow'); ?>
    </td>
    <td class="br"></td>
  </tr>
</table>
