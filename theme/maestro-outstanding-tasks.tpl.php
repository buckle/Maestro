<?php
// $Id$

/**
 * @file
 * maestro-outstanding-tasks.tpl.php
 */

?>
<fieldset class="form-wrapper">
  <table class="sticky-enabled sticky-table">
    <thead class="tableheader-processed">
      <tr>
        <th><?php print t('Task Name'); ?></th>
        <th><?php print t('Assigned To'); ?></th>
        <th><?php print t('Operation'); ?></th>
      </tr>
    </thead>

    <tbody>

<?php
  $i = 0;
  foreach ($queue as $task) {
    $classname = ((++$i % 2) == 0) ? 'even':'odd';
?>
    <tr class="<?php print $classname; ?>">
      <td><?php print $task->taskname; ?></td>
      <td><?php print $task->username; ?></td>
      <td>
        <img class="valigncenter" src="<?php print $maestro_url; ?>/images/taskconsole/reassign.png">
        <img class="valigncenter" src="<?php print $maestro_url; ?>/images/taskconsole/email.png">
        <img class="valigncenter" src="<?php print $maestro_url; ?>/images/taskconsole/trace.png">
        <img class="valigncenter" src="<?php print $maestro_url; ?>/images/taskconsole/delete.png">
      </td>
    </tr>
<?php
  }
?>

    </tbody>
  </table>
</fieldset>
