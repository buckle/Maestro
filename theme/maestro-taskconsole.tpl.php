<?php
// $Id:

/**
 * @file
 * maestro-taskconsole.tpl.php
 */

?>

<table width="100%">
<tr>
  <th width="5%">Process</th>
  <th width="30%">Flow Name</th>
  <th width="40%">Task Name</th>
  <th width="25%">Task Type</th>
</tr>

<?php for ($i = 0; $i < $taskcount; $i++) { ?>
<tr class="<?php print $zebra ?>">
  <td><?php print $tasks['process'][$i] ?></td>
  <td><?php print $tasks['template_name'][$i] ?></td>
  <td><?php print $tasks['taskname'][$i] ?></td>
  <td><?php print $tasks['tasktype'][$i] ?></td>
</tr>
<?php } ?>
</table>