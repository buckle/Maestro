<?php
// $Id:

/**
 * @file
 * maestro-workflow-list-item.tpl.php
 */

?>
    <tr class="<?php print $zebra ?>" id="tview<?php print $cntr; ?>" class="">
      <td width="5%"  style="padding-left:5px;"><?php print $rec->id; ?></td>
      <td width="80%" style="padding-left:5px;"><span id="tname<?php print $rec->id; ?>"><?php print filter_xss($rec->template_name); ?></span></td>
      <td width="15%" style="text-align:right;padding-right:5px;" nowrap>
      <?php print l('<img src="' . $module_path . '/images/admin/edit_tasks.gif" border="0" title="' . t('Edit Tasks') .'">', 'admin/structure/maestro/edit/' . $rec->id, array('html' => TRUE)); ?>&nbsp;
      <?php print l('<img src="' . $module_path . '/images/admin/edit_properties.gif" border="0" title="' . t('Edit Template Properties and Variables') .'">', 'admin/structure/maestro/edit_properties/' . $rec->id, array('html' => TRUE)); ?>&nbsp;
      <input title="<?php print t('Copy Template'); ?>" type="image" src="<?php print $module_path; ?>/images/admin/copy.gif" onclick="maestro_copyTemplate(<?php print $rec->id; ?>);" >&nbsp;
      <input title="<?php print t('Delete Template'); ?>" type="image" src="<?php print $module_path; ?>/images/admin/delete.gif" onclick="maestro_deleteTemplate(<?php print $rec->id; ?>);" >&nbsp;
      <?php print l('<img src="' . $module_path . '/images/admin/export.gif" border="0" title="' . t('Export Template') .'">', 'admin/structure/maestro/export/' . $rec->id, array('html' => TRUE)); ?>&nbsp;
      </td>
    </tr>
    <tr id="tedit<?php print $cntr; ?>" class="" style="vertical-align:top;display:<?php print $show_item; ?>;">
      <td width="5%" class="" style="padding-left:5px;"></td>
      <td width="95%" colspan="2" class="">
        <form id="maestro_template_save_<?php print $cntr; ?>" style="margin:0px;">
          <table cellspacing="1" cellpadding="1" border="0" width="100%" style="margin-top:5px;">
            <tr>
              <td width="60" style="padding-left:10px;" nowrap>
                <input class="form-text" type="text" name="templateName" size="50" value="<?php print filter_xss($rec->template_name); ?>">
              </td>
              <td>
                <span id="updatename_<?php print $rec->id; ?>" style="display:<?php print $show_item; ?>;"></span>
              </td>
              <td width="30%" style="text-align:right;padding-right:5px;" nowrap>
                <span id="fieldaction_<?php print $rec->id; ?>">
                  <span id="maestro_updating_<?php print $cntr; ?>"  class=""></span>
                  <input class="form-submit" type="button" value="<?php print t('Save'); ?>" onClick='maestro_saveTemplateName(<?php print $rec->id; ?>,<?php print $cntr; ?>);'>&nbsp;
                  <?php print l('<input class="form-submit" id="tcancel_<?php print $cntr; ?>" type="button" value="' . t('Close') . '">', 'admin/structure/maestro', array('html' => TRUE)); ?>
                </span>
                <span id="fieldstatus_<?php print $rec->id; ?>" class="pluginInfo"  style="display:none;">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <td colspan="3" style="padding-left:10px;" ><?php print t('Create Tracking Entries'); ?>:
                <span id="editNeedPrj_<?php print $rec->id; ?>">
                  <input type="checkbox" name="useProject" <?php print $use_project; ?> value="1">
                </span>
              </td>
            </tr>
            <tr>
              <td colspan="3" style="vertical-align:top;padding-left:10px;">
                <div style="float:left;width:75%"><?php print t('Bind Flow to a Flow Application Group'); ?>: </div>
                <div style="float:right;width:20%;white-space:nowrap;"> <?php print $template_app_groups; ?> </div>
              </td>
            </tr>
          </table>
        </form>
        <div id="addvarlabel<?php print $cntr; ?>" style="padding:5px;">
          [&nbsp;<a href="#" onclick="maestro_OpenCloseCreateVariable(<?php print $cntr; ?>);"><?php print t('Add Variable'); ?></a>&nbsp;]
            <div id="variableAdd_<?php print $cntr; ?>" style="display:none">
              <form id="frmVariableAdd_<?php print $cntr; ?>">
              <table border=0>
                <tr>
                  <td>Variable Name:</td>
                  <td><input class="form-text" type="text" name="newVariableName" id="newVariableName" size="30" value=""></td>
                  <td rowspan="2">
                    <span id="maestro_variable_updating_<?php print $cntr; ?>"></span>
                    <input class="form-submit" type="button" value="<?php print t('Create'); ?>" onClick='maestro_CreateVariable(<?php print $rec->id; ?>,<?php print $cntr; ?>);'>&nbsp;
                    <input class="form-submit" type="button" value="<?php print t('Close'); ?>" onClick='maestro_OpenCloseCreateVariable(<?php print $cntr; ?>);'>&nbsp;
                  </td>
                </tr>
                <tr>
                  <td>Default Value:</td>
                  <td><input class="form-text" type="text" name="newVariableValue" id="newVariableValue" size="10" value=""></td>
                </tr>
              </table>
              </form>
            </div>
        </div>
        <div id="{vdivid}" style="padding-left:10px;display:{show_vars};">
          <fieldset style="margin:10px 10px 10px 0px;"><legend><?php print t('Template Variables'); ?></legend>
              <div id="ajaxReplaceTemplateVars_<?php print $rec->id; ?>">
                 <?php print $template_variables; ?>
              </div>
          </fieldset>
        </div>
      </td>
    </tr>

