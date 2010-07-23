<?php
// $Id:

/**
 * @file
 * maestro-task-content-type.tpl.php
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
      <?php print t('Content Type Task'); ?>
    </td>
    <td class="br"></td>
  </tr>
</table>
