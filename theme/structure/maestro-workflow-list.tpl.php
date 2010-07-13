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
    <input class="form-submit" type="button" value="<?php print t('New Template'); ?>" onClick="jQuery('#newtemplate').toggle();">&nbsp;
    <input class="form-submit" type="button" value="<?php print t('Application Groups'); ?>" onClick="jQuery('#newappgroup').toggle();">&nbsp;
  </div>

  <table cellpadding="2" cellspacing="1" border="1" width="100%" style="border:1px solid #CCC;">
    <tr>
      <td colspan="3" class="pluginInfo"><?php print t('Click on desired action to edit template'); ?></td>
    </tr>
    <tr>
      <td class="pluginTitle"><?php print t('ID'); ?></td>
      <td class="pluginTitle"><?php print t('Template Name'); ?></td>
      <td class="pluginTitle" ><?php print t('Actions'); ?></td>
    </tr>
    <tr id="newtemplate" style="display:none;">
      <td colspan="3" class="pluginRow1">
          <table cellspacing="1" cellpadding="1" border="0" width="100%" style="border:none;">
            <tr>
              <td><?php print t('Name'); ?>:</td>
              <td><input class="form-text" type="text" id="newTemplateName" value="" size="50"></td>
              <td style="text-align:right;padding-right:10px;">
                <span id="maestro_new_template_updating"></span>
                <input class="form-submit" type="button" value="<?php print t('Create'); ?>" onClick='maestro_CreateTemplate();'>&nbsp;
                <input class="form-submit" type="button" value="<?php print t('Close'); ?>" onClick="jQuery('#newtemplate').toggle();">&nbsp;
              </td>
            </tr>
          </table>
      </td>
    </tr>
    <tr id="newappgroup" style="display:none;">
      <td colspan="3" class="pluginRow1">
          <table cellspacing="1" cellpadding="1" border="0"  width="100%" style="border:none;">
            <tr>
              <td width="180"><?php print t('New Application Group Name'); ?>:</td>
              <td><input class="form-text" type="text" id="appGroupName" value="" size="50">
              <input class="form-submit" type="button" value="<?php print t('Create'); ?>" onClick='maestro_CreateAppgroup();'>&nbsp;
              </td>
              <td style="text-align:right;padding-right:10px;">
                <span id="maestro_new_appgroup_updating"></span>

                <input class="form-submit" type="button" value="<?php print t('Close'); ?>" onClick="jQuery('#newappgroup').toggle();">&nbsp;
              </td>
            </tr>
          </table>
          <table cellspacing="1" cellpadding="1" border="0" width="100%" style="border:none;">
            <tr>
              <td class="aligntop" nowrap width="180"><?php print t('Delete Application Group'); ?>:</td>
              <td>
                <div id="replaceDeleteAppGroup">
                <?php print $app_groups; ?>
                </div>
                <input class="form-submit" type="button" value="<?php print t('Delete'); ?>" onClick='maestro_DeleteAppgroup();'>&nbsp;
                <span id="maestro_del_appgroup_updating"></span>
              </td>

          </table>

      </td>
    </tr>
    </tr>
    <tr style="color:red">
      <td colspan="3" class="" style="color:red">
          <span id="maestro_error_message"><?php print filter_xss($error_message); ?></span>
      </td>
    </tr>
    <?php print $workflow_list; ?>

  </table>
</div>