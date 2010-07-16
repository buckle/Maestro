<?php
// $Id:

/**
 * @file
 * maestro-taskconsole.tpl.php
 */

?>

<table width="100%">
<tr>
  <th width="5%"><?php print t(''); ?></th>
  <th width="30%"><?php print t('Flow Name'); ?></th>
  <th width="40%"><?php print t('Task Name'); ?></th>
  <th width="25%"><?php print t('Assigned'); ?></th>
</tr>

<?php for ($i = 0; $i < $taskcount; $i++) { ?>
<tr class="<?php print $zebra ?>">
    <td width="3%" class="<?php print $tasks['id'][$i] ?>" style="border-left:1px solid white">
        <img src="<?php print $task_icon ?>" TITLE="<?php print t('Process ID: '); print $tasks['process'][$i]; print t(', Task ID: '); print $tasks['id'][$i]; print $tasks['task_started'][$i]?>" id="taskIconImg<?php print $i; ?>">
    </td>
    <td width="35%"><?php print $tasks['class_newtask'][$i] ?></td>
    <td width="35%">
        <a class="info" style="text-decoration:none;" href="<?php print $tasks['task_action_url'][$i] ?>" <?php print $tasks['task_onclick'][$i] ?>><?php print $tasks['taskname'][$i] ?>
            <span style="width:300px;display: <?php print $tasks['hidetaskinfo'][$i] ?>;">
                <?php print $tasks['onholdnotice'][$i] ?>
                <b><?php print t('Date Assigned:'); ?></b>&nbsp;<?php print $tasks['assigned_longdate'][$i] ?>
                <div style="display:<?php print $tasks['showmoretaskdetail'][$i]; ?>">
                  <b><?php print t('Description:'); ?></b>&nbsp;<?php print $tasks['description'][$i] ?><br>
                  <b><?php print t('Comments:'); ?></b>&nbsp;<?php print $tasks['comment_note'][$i] ?>
                </div>
            </span>
        </a>
    </td>
    <td width="10%" nowrap><?php print $tasks['assigned_shortdate'][$i] ?></td>
    <td width="5%" style="border-right:1px solid white;" nowrap><?php print $details_icon; print $tasks['hold'][$i]; print $tasks['view'][$i]; print $tasks['edit'][$i]; print $tasks['delete'][$i]; ?></td>
</tr>

<tr id="wfdetail_rec<?php print $i ?>" style="display:none;">
    <td colspan="5" style="padding:10px;">
        <div id="projectdetail_rec<?php print $i ?>">&nbsp;</div>
    </td>
</tr>
<!-- {inline action record} -->
<?php print $tasks['action_record'][$i] ?>

<?php } ?>
</table>