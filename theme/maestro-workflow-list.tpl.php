<?php
// $Id:

/**
 * @file
 * maestro-workflow-list.tpl.php
 */

  global $base_url;

  drupal_add_css($base_url . '/' . drupal_get_path('module', 'maestro') . '/css/maestro.css');

?>

  <script type="text/javascript">
    // Note: JS code included in this template because I use a couple template vars
    // to modify the code before executing it - specifically [num_records]

    // Function attached to "Add Variable" Link on the template variable form.
    function addVariable(i) {
      document.getElementById('newtvar_container'+i).style.display = '';
      if (document.getElementById('vars' + i)) {
        document.getElementById('vars' + i).style.display = '';
      }
      document.getElementById('addvarlabel' + i).style.display = 'none';
    }

    // Function action attached to click event when user clicks on "Edit" Template
    // Reset all other template display areas and un-hide selected template
    function editTemplateAction() {
      if (window.event && window.event.srcElement)  {  // IE Method
        var id = window.event.srcElement.parentNode.id;
      }
      else {
        var id = this.id;
      }
      var templateid = id.split('_');
      document.getElementById('addtemplate').style.visibility = 'hidden';
      document.getElementById('tview' + templateid[1]).style.display = 'none';
      document.getElementById('tedit' + templateid[1]).style.display = '';

      // Hide any other template record details
      for (var i = 0; i < {num_records}; i++) {
        if (i != templateid[1]) {
          document.getElementById('tedit' + i).style.display = 'none';
          document.getElementById('tview' + i).style.display = '';
          document.getElementById('addvarlabel' + i).style.display = '';
          document.getElementById('newtvar_container' + i).style.display = 'none';
        }
      }
    }

    // Function action attached to click event when user clicks on "Cancel" button
    // Restore all form defaults
    function restoreAction() {
      document.getElementById('addtemplate').style.visibility = '';
      document.getElementById('newtemplate').style.display = 'none';

      // Hide any other template record details
      for (var i = 0; i < {num_records}; i++) {
        document.getElementById('tedit'+i).style.display = 'none';
        document.getElementById('tview'+i).style.display = '';
        document.getElementById('addvarlabel'+i).style.display = '';
        document.getElementById('newtvar_container'+i).style.display = 'none';
        if (document.getElementById('vars'+i)) {
          document.getElementById('vars'+i).style.display = 'none';
        }
      }
    }

    /* Locate all the template records and install listener for the edit action */
    function installListeners() {
      for (var i = 0; i < {num_records}; i++) {
        var element1 = document.getElementById('etemplate_' + i);
        var element2 = document.getElementById('tcancel_' + i);
        addEvent(element1, 'click', editTemplateAction, false);
        addEvent(element2, 'click', restoreAction, false);
      }
    }

    addEvent(window, 'load', installListeners, false);

    // cross-browser event handling for IE5+, NS6+ and Mozilla/Gecko
    // By Scott Andrew
    function addEvent(elm, evType, fn, useCapture) {
      if (elm.addEventListener) {
        elm.addEventListener(evType, fn, useCapture);
        return true;
      }
      else if (elm.attachEvent) {
        var r = elm.attachEvent('on' + evType, fn);
        return r;
      }
      else {
        elm['on' + evType] = fn;
      }
    }
  </script>
  <script type="text/javascript" src="{public_url}/include/ajaxsupport.js"></script>

  <div id="addtemplate" style="padding:10px 0px 10px 10px;">
    <a href="#" onClick="document.getElementById('newtemplate').style.display = ''; document.getElementById('addtemplate').style.visibility = 'hidden'">New Template</a>&nbsp;&nbsp;
    <a href="#" onClick="document.getElementById('newappgroup').style.display = ''; document.getElementById('addtemplate').style.visibility = 'hidden'">Add Application Groups</a>&nbsp;&nbsp;
    <a href="#" onClick="document.getElementById('editappgroup').style.display = ''; document.getElementById('addtemplate').style.visibility = 'hidden'">Delete Application Groups</a>
  </div>

  <table cellpadding="2" cellspacing="1" border="0" width="100%" style="border:1px solid #CCC;">
    <tr>
      <td colspan="3" class="pluginInfo">Click on desired action to edit template</td>
    </tr>
    <tr>
      <td class="pluginTitle">ID</td><td class="pluginTitle">Template Name</td><td class="pluginTitle" >Actions</td>
    </tr>
    <tr id="newtemplate" style="display:none;">
      <td colspan="3" class="pluginRow1">
        <form method="get" action="{public_url}/templates.php" style="margin:0px;">
          <table cellspacing="1" cellpadding="1" border="0" width="100%">
            <tr>
              <td>Name:</td>
              <td><input type="text" name="templateName" value="" size="50" ></td>
              <td style="text-align:right;padding-right:10px;">
                <input type="hidden" name="operation" value="save">
                <input type="submit" value="&nbsp;Add&nbsp;">&nbsp;<input type="button" value="Cancel" onClick='restoreAction();'>
              </td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
    <tr id="newappgroup" style="display:none;">
      <td colspan="3" class="pluginRow1">
        <form method="get" action="{public_url}/templates.php" style="margin:0px;" name="appGroupForm">
          <table cellspacing="1" cellpadding="1" border="0"  width="100%">
            <tr>
              <td>New Application Group Name:</td>
              <td><input type="text" name="appGroupName" value="" size="50" ></td>
              <td style="text-align:right;padding-right:10px;">
                <input type="hidden" name="operation" value="addappgroup">
                <input type="submit" value="&nbsp;Add&nbsp;">&nbsp;<input type="button" value="Cancel" onClick='restoreAction();document.appGroupForm.appGroupName.value="";getElementById("newappgroup").style.display="none"'>
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
              <td valign="top" nowrap>Delete Application Group:</td>
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
    <tr style="display:{showErrorMessage};color:red">
      <td colspan="3" class="pluginRow1" style="display:{showErrorMessage};color:red">
        <br>
          {errorMessage}
        <br><br>
      </td>
    </tr>
    <?php echo theme('maestro_workflow_list_item', array()); ?>
  </table>
