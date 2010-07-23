<?php
// $Id$

/**
 * @file
 * maestro-task-batch.tpl.php
 */
?>

<table class="maestro_task">
  <tr>
    <td class="tl-gry"></td>
    <td id="task_title<?php print $tdid; ?>" class="tm-gry maestro_task_title">
      <?php print $taskname; ?>
    </td>
    <td class="tr-gry"></td>
  </tr>
  <tr>
    <td class="bl"></td>
    <td class="bm maestro_task_body">
      <?php print t('Batch Task'); ?>
    </td>
    <td class="br"></td>
  </tr>
</table>
