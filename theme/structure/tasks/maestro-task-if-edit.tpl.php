<?php
// $Id:

/**
 * @file
 * maestro-task-if-edit.tpl.php
 */

?>
<table>
  <tr>
    <td ><?php print t('Task Name:'); ?></td>
    <td><input type="text" name="taskname" value="<?php print $td_rec->taskname; ?>"></td>
  </tr>
  <tr>
    <td colspan="2"><?php print t('Set your IF parameters to check by variable OR by Last Task Status below:'); ?></td>

  </tr>

  <tr>
    <td>
      <?php print t('Argument Variable:'); ?>
    </td>
    <td>
      <select name="argumentVariable">
      <?php print $argument_variables; ?>
      </select>
      <select name="ifOperator">
        <option value="0"></option>
        <option value="=" <?php if($td_rec->task_data['if_operator'] == '=') print 'selected'; ?>>=</option>
        <option value=">" <?php if($td_rec->task_data['if_operator'] == '>') print 'selected'; ?>>&gt;</option>
        <option value="<" <?php if($td_rec->task_data['if_operator'] == '<') print 'selected'; ?>>&lt;</option>
        <option value="!=" <?php if($td_rec->task_data['if_operator'] == '!=') print 'selected'; ?>>!=</option>
      </select>
      <input type="text" name="ifValue" value="<?php print $td_rec->task_data['if_value']; ?>" size="3"></input>
    </td>
    <td>

    </td>
  </tr>
    <td>
      <?php print t('Or by Last Task Status:'); ?>
    </td>
    <td>

    <select name="ifProcessArguments">
        <option value="0"></option>
        <option value="lasttasksuccess" <?php if($td_rec->task_data['if_process_arguments'] == 'lasttasksuccess') print 'selected'; ?>>Last Task Status is Success</option>
        <option value="lasttaskcancel" <?php if($td_rec->task_data['if_process_arguments'] == 'lasttaskcancel') print 'selected'; ?>>Last Task Status is Cancel</option>
        <option value="lasttaskhold" <?php if($td_rec->task_data['if_process_arguments'] == 'lasttaskhold') print 'selected'; ?>>Last Task Status is Hold</option>
        <option value="lasttaskaborted" <?php if($td_rec->task_data['if_process_arguments'] == 'lasttaskaborted') print 'selected'; ?>>Last Task Status is Aborted</option>
      </select>
    </td>
  <tr>



</table>
