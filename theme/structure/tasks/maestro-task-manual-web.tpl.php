<?php
// $Id$

/**
 * @file
 * maestro-task-manual-web.tpl.php
 */
?>

<table class="maestro_task">
  <tr>
    <td class="tl-bl"></td>
    <td id="task_title<?php print $tdid; ?>" class="tm-bl maestro_task_title">
      <?php print $taskname; ?>
    </td>
    <td class="tr-bl"></td>
  </tr>
  <tr>
    <td class="bl"></td>
    <td class="bm maestro_task_body">
      <?php print t('Manual Web Task'); ?>
    </td>
    <td class="br"></td>
  </tr>
</table>
