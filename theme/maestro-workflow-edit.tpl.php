<?php
// $Id:

/**
 * @file
 * maestro-workflow-edit.tpl.php
 */

  global $base_url;
  $yui_base_url = 'http://yui.yahooapis.com/2.7.0/build';
  $maestro_path = '/' . drupal_get_path('module', 'maestro');
  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');

  drupal_add_js($base_url . $maestro_path . '/js/init_workflowadmin.js');
  drupal_add_js($base_url . $maestro_path . '/js/workflowadmin.js');

  //drupal_add_css('http://yui.yahooapis.com/2.7.0/build/fonts/fonts.css');
  //drupal_add_css('http://yui.yahooapis.com/2.7.0/build/container/assets/container.css');
  //drupal_add_css('http://yui.yahooapis.com/2.7.0/build/container/assets/skins/sam/container.css');
  //drupal_add_css('http://yui.yahooapis.com/2.7.0/build/menu/assets/menu.css');

?>

<script type="text/javascript" src="<?php print $yui_base_url; ?>/yuiloader/yuiloader.js"></script>
<script type="text/javascript">
  var ajax_action_url = '/plugins/nexflow/';
  var draw_status = 0;
  var draw_type = 1;
  var line_start = '';
  var line_end = '';
  var existing_tasks = [];
  var line_ids = [];
  var lines = [];
  var dd = [];
  var steptypes = [];
  var taskIdCount = -1;
  var trueLineColor = "#0F367B";
  var falseLineColor = "#D10000";
  var oMenu;
  var panels = [];
  var oMenuBar;
  var templateid = 0 //{template_id};
  var menuCheckArray = [];

  var LANG_select_first       = "{LANG_select_first}";
  var LANG_select_last        = "{LANG_select_last}";
  var LANG_task_type          = "{LANG_task_type}";
  var LANG_assigned_to        = "{LANG_assigned_to}";
  var LANG_nobody_assigned    = "{LANG_nobody_assigned}";
  var LANG_new_task           = "{LANG_new_task}";
  var LANG_confirm_del_task   = "{LANG_confirm_del_task}";
  var LANG_new_task           = "{LANG_new_task}";
  var LANG_draw_line          = "{LANG_draw_line}";
  var LANG_draw_line_true     = "{LANG_draw_line_true}";
  var LANG_draw_line_false    = "{LANG_draw_line_false}";
  var LANG_clear_adj_lines    = "{LANG_clear_adj_lines}";
  var LANG_edit_task          = "{LANG_edit_task}";
  var LANG_delete_task        = "{LANG_delete_task}";

</script>

<script>
  // Instantiate and configure Loader:
  var loader = new YAHOO.util.YUILoader({

    base: 'https://ajax.googleapis.com/ajax/libs/yui/2.7.0/build/',

    // Identify the components you want to load.  Loader will automatically identify
    // any additional dependencies required for the specified components.
    require: ["container","layout","resize","connection","dragdrop","menu","button","autocomplete","treeview","element","cookie","uploader","logger","animation"],

    // Configure loader to pull in optional dependencies.  For example, animation
    // is an optional dependency for slider.
    loadOptional: true,

    // The function to call when all script/css resources have been loaded
    onSuccess: function() {
      //timeDiff.setStartTime();
      Dom = YAHOO.util.Dom;
      Event = YAHOO.util.Event;
      Event.onDOMReady(function() {
        setTimeout('init_nexflow()',500);
      });
    },

    // Configure the Get utility to timeout after 10 seconds for any given node insert
    timeout: 10000,

    // Combine YUI files into a single request (per file type) by using the Yahoo! CDN combo service.
    combine: false
  });


  // Load the files using the insert() method. The insert method takes an optional
  // configuration object, and in this case we have configured everything in
  // the constructor, so we don't need to pass anything to insert().
  loader.insert();

</script>

<div class="nf_progress_bar" id="nf_progress_bar" style="display: none;"><img src="{layout_url}/nexflow/images/admin/progress.gif"></div>

<table style="border-collapse: collapse;">
<tr>
  <td class="pluginInfo" id="nf_tooltip" width="100%">
  </td>
  <td class="pluginInfo" nowrap>
    <form name="frm_animate" action="#" method="post">
      {LANG_animation}: <input type="checkbox" name="animateFlag" value="1" checked="checked">
    </form>
  </td>
</tr>
</table>
<form name="frm_workflow" method="post" action="{site_admin_url}/plugins/nexflow/edit.php">
<div id="workflow_container">
  <input type="hidden" name="workflow_id" value="{workflow_id}">
  <input type="hidden" name="op" value="save_workflow">

  {existing_tasks}

</div>
</form>

<!-- hidden tables as templates for new tasks -->
<div id="task_template" style="display: none;">
  {task_template}
</div>
<!-- end of hidden tables -->

<!-- Task Edit Panels -->
<div id="mw_template" class="nf_panel">
  <div id="mw_tasktitle" class="nf_panel_header">Task Title</div>
  <div id="mw_navbar" class="nf_panel_nav"></div>
  <div id="mw_body" class="nf_panel_body"></div>
</div>
<div id="and_template" class="nf_panel">
  <div id="and_tasktitle" class="nf_panel_header">Task Title</div>
  <div id="and_navbar" class="nf_panel_nav"></div>
  <div id="and_body" class="nf_panel_body"></div>
</div>
<div id="bat_template" class="nf_panel">
  <div id="bat_tasktitle" class="nf_panel_header">Task Title</div>
  <div id="bat_navbar" class="nf_panel_nav"></div>
  <div id="bat_body" class="nf_panel_body"></div>
</div>
<div id="if_template" class="nf_panel">
  <div id="if_tasktitle" class="nf_panel_header">Task Title</div>
  <div id="if_navbar" class="nf_panel_nav"></div>
  <div id="if_body" class="nf_panel_body"></div>
</div>
<div id="bf_template" class="nf_panel">
  <div id="bf_tasktitle" class="nf_panel_header">Task Title</div>
  <div id="bf_navbar" class="nf_panel_nav"></div>
  <div id="bf_body" class="nf_panel_body"></div>
</div>
<div id="int_template" class="nf_panel">
  <div id="int_tasktitle" class="nf_panel_header">Task Title</div>
  <div id="int_navbar" class="nf_panel_nav"></div>
  <div id="int_body" class="nf_panel_body"></div>
</div>
<div id="nfm_template" class="nf_panel">
  <div id="nfm_tasktitle" class="nf_panel_header">Task Title</div>
  <div id="nfm_navbar" class="nf_panel_nav"></div>
  <div id="nfm_body" class="nf_panel_body"></div>
</div>
<div id="spv_template" class="nf_panel">
  <div id="spv_tasktitle" class="nf_panel_header">Task Title</div>
  <div id="spv_navbar" class="nf_panel_nav"></div>
  <div id="spv_body" class="nf_panel_body"></div>
</div>
<!-- End of Task Edit Panels -->

<script type="text/javascript">
  <!-- {additional_js} -->
</script>
