<?php
// $Id$

/**
 * @file
 * maestro-outstanding-tasks.tpl.php
 */

  foreach ($queue as $task) {
?>
    <?php print $task->taskname; ?>&nbsp;
    <?php print $task->username; ?><br>

<?php
  }
?>

