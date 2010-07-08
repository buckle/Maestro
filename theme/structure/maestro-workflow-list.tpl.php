<?php
// $Id:

/**
 * @file
 * maestro-workflow-list.tpl.php
 */

?>
<div id="maestro_template_admin">
<script type="text/javascript">
var num_records = <?php print $num_records; ?>;
var ajax_url = '<?php print filter_xss($ajax_url); ?>';
</script>
  <div id="addtemplate" style="padding:10px 0px 10px 10px;">
    <input type="button" value="<?php print t('New Template'); ?>" onClick="jQuery('#newtemplate').toggle();">&nbsp;


    <a href="#" onClick="document.getElementById('newappgroup').style.display = ''; document.getElementById('addtemplate').style.visibility = 'hidden'">Add Application Groups</a>&nbsp;&nbsp;
    <a href="#" onClick="document.getElementById('editappgroup').style.display = ''; document.getElementById('addtemplate').style.visibility = 'hidden'">Delete Application Groups</a>
  </div>

  <table cellpadding="2" cellspacing="1" border="1" width="100%" style="border:1px solid #CCC;">
    <tr>
      <td colspan="3" class="pluginInfo"><?php print t('Click on desired action to edit template'); ?></td>
    </tr>
    <tr>
      <td class="pluginTitle">ID</td><td class="pluginTitle"><?php print t('Template Name'); ?></td><td class="pluginTitle" ><?php print t('Actions'); ?></td>
    </tr>
    <tr id="newtemplate" style="display:none;">
      <td colspan="3" class="pluginRow1">
          <table cellspacing="1" cellpadding="1" border="0" width="100%">
            <tr>
              <td><?php print t('Name'); ?>:</td>
              <td><input type="text" id="newTemplateName" value="" size="50" style="border: solid black 1px;"></td>
              <td style="text-align:right;padding-right:10px;">
                <span id="maestro_new_template_updating"></span>
                <input type="button" value="<?php print t('Create'); ?>" onClick='maestro_CreateTemplate();'>&nbsp;
                <input type="button" value="<?php print t('Close'); ?>" onClick="jQuery('#newtemplate').toggle();">&nbsp;



              </td>
            </tr>
          </table>
      </td>
    </tr>
    <tr id="newappgroup" style="display:none;">
      <td colspan="3" class="pluginRow1">
        <form method="get" action="{public_url}/templates.php" style="margin:0px;" name="appGroupForm">
          <table cellspacing="1" cellpadding="1" border="0"  width="100%">
            <tr>
              <td><?php print t('New Application Group Name'); ?>:</td>
              <td><input type="text" name="appGroupName" value="" size="50" ></td>
              <td style="text-align:right;padding-right:10px;">
                <input type="hidden" name="operation" value="addappgroup">
                <input type="submit" value="&nbsp;Add&nbsp;">&nbsp;<input type="button" value="<?php print t('Cancel'); ?>" onClick='restoreAction();document.appGroupForm.appGroupName.value="";getElementById("newappgroup").style.display="none"'>
              </td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
    <tr id="editappgroup" style="display:none;">
      <td colspan="3" class="pluginRow1">
        <form method="get" action="{public_url}/templates.php" style="margin:0px;" name="editGroupForm">
          <table cellspacing="1" cellpadding="1" border="0" width="100%">
            <tr>
              <td valign="top" nowrap><?php print t('Delete Application Group'); ?>:</td>
              <td>
                <select name="deleteAppGroup" size="4">
                  {deleteAppGroup}
                </select>
              </td>
              <td width="60%">&nbsp;</td>
              <td style="text-align:right;padding-right:10px;" nowrap>
                <input type="hidden" name="operation" value="editappgroup">
                <input type="submit" value="&nbsp;Delete&nbsp;">&nbsp;<input type="button" value="Cancel" onClick='restoreAction();getElementById("editappgroup").style.display="none"'>
              </td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
    <tr style="color:red">
      <td colspan="3" class="" style="color:red">
        <br>
          <span id="maestro_error_message"><?php print filter_xss($error_message); ?></span>
        <br><br>
      </td>
    </tr>
    <?php print $workflow_list; ?>

  </table>
</div>