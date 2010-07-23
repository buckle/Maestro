<?php
// $Id$

/**
 * @file
 * maestro-task-if.tpl.php
 */
?>

<table class="maestro_task">
  <tr>
    <td class="tl-yel"></td>
    <td class="tm-yel maestro_task_title">
      <?php print t('If Condition'); ?>
    </td>
    <td class="tr-yel"></td>
  </tr>
  <tr>
    <td class="bl"></td>
    <td id="task_title<?php print $tdid; ?>" class="bm maestro_task_body">
      <?php print $taskname; ?>
    </td>
    <td class="br"></td>
  </tr>
</table>
