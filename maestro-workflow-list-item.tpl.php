<?php
// $Id:

/**
 * @file
 * maestro-workflow-list-item.tpl.php
 */
  global $base_url;
  $module_path = $base_url . '/' . drupal_get_path('module', 'maestro');
  $res = db_query("SELECT id, template_name FROM {maestro_template}");
  foreach ($res as $rec) {
?>
    <tr id="tview{cntr}" class="pluginRow{cssid}">
      <td width="5%"  style="padding-left:5px;"><?php echo $rec->id; ?></td>
      <td width="80%" style="padding-left:5px;"><span id="tname{template_id}"><?php echo $rec->template_name; ?></span></td>
      <td width="15%" style="text-align:right;padding-right:5px;" nowrap>
        <?php echo l('<img src="' . $module_path . '/images/admin/edit_tasks.gif" border="0" title="Edit Tasks">', 'admin/structure/maestro/edit/' . $rec->id, array('html' => TRUE)); ?>&nbsp;
        <a id="etemplate_{cntr}" href="#"><img src="<?php echo $module_path; ?>/images/admin/edit_properties.gif" border="0" title="Edit Template and Variables"></a>&nbsp;
        <a href="{copy_template_url}"><img src="<?php echo $module_path; ?>/images/admin/copy.gif" border="0" title="Copy Template"></a>&nbsp;
        <a href="{del_template_url}" onclick="return confirm('{LANG_DELCONFIRM}');"><img src="<?php echo $module_path; ?>/images/admin/delete.gif" border="0" title="Delete Template"></a>&nbsp;
        <a href="#" onclick="exporttemplate({template_id});"><img src="<?php echo $module_path; ?>/images/admin/export.gif" border="0" title="Export Template"></a>&nbsp;
      </td>
    </tr>

    <tr id="tedit{cntr}" class="pluginRow{cssid}" style="vertical-align:top;display:none;">
      <td width="5%" class="pluginRow{cssid}" style="padding-left:5px;">{template_id}</td>
      <td width="95%" colspan="2" class="pluginRow{cssid}">
        <form name="tform_{cntr}" style="margin:0px;">
          <table cellspacing="1" cellpadding="1" border="0" width="100%" style="margin-top:5px;">
            <tr>
              <td width="70" style="padding-left:10px;" nowrap>
                <input type="text" name="templateName" size="50" value="{template_name}" READONLY></td>
              <td>
                <span id="editname_{template_id}">{editname_link}</span>
                <span id="updatename_{template_id}" style="display:none;">
                  <input type="button" value="Save" onClick='ajaxUpdateTemplateVar("updateTemplateName",{template_id},{cntr});'>&nbsp;
                  <input type="button" value="Cancel" onClick='ajaxUpdateTemplateVar("cancelTemplateName",{template_id},{cntr},{var_id});' >
                </span>
              </td>
              <td width="30%" style="text-align:right;padding-right:5px;" nowrap>
                <span id="fieldaction_{template_id}">
                  <input id="tcancel_{cntr}" type="button" value="Close">
                </span>
                <span id="fieldstatus_{template_id}" class="pluginInfo"  style="display:none;">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <td colspan="4" style="padding-left:10px;" >Create Tracking Entries:
                <span id="editNeedPrj_{template_id}">
                  <input type="checkbox" name="useProject" {editNeedPrj_check} onclick='ajaxUpdateTemplateVar("useProject",{template_id},{cntr});'>
                </span>
              </td>
            </tr>
            <tr>
              <td colspan="4" style="vertical-align:top;padding-left:10px;">
                <table cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="aligntop">Bind Flow to a Flow Application Group: </td>
                    <td class="aligntop">
                      <span id="editNeedApp_{template_id}">
                        <select name="needApp{template_id}" size=1 onchange='ajaxUpdateTemplateVar("updateApplicationGroup",{template_id},{cntr});'>
                          {editUseApp}
                        </select>
                      </span>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </form>
        <div id="addvarlabel{cntr}" style="padding:5px;">[&nbsp;<a href="#" onClick="addVariable({cntr});">Add&nbsp;Variable</a>&nbsp;]</div>
        <div id="{vdivid}" style="padding-left:10px;display:{show_vars};">
          <fieldset style="margin:10px 10px 10px 0px;"><legend>Template Variables</legend>
            <form name="tvars{cntr}" style="margin:0px;">
              {template_variables}
            </form>
          </fieldset>
        </div>
      </td>
    </tr>
<?php
  }
?>
