<?php
// $Id$

/**
 * @file
 * maestro-task-interactive-function-edit.tpl.php
 */

?>

<table>
  <tr>
    <td><?php print t('Form API PHP Array:'); ?></td>
  </tr>
  <tr>
    <td><textarea name="form_api_code" rows="8" style="width: 100%;"><?php print $td_rec->task_data['form_api_code']; ?></textarea></td>
  </tr>
  <tr>
    <td style="font-style: italic; font-size: 0.8em;"><?php print t('Create your form fields in an array variable named $form. Leave out any default values, the system will add them automatically.'); ?></td>
  </tr>
</table>
