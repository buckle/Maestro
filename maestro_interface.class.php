<?php
// $Id:

/**
 * @file
 * maestro_task_interface.class.php
 */

class MaestroInterface {
  private $_template_id;

  function __construct($template_id) {
    $this->_template_id = $template_id;
  }

  //displays the main task page
  function displayPage() {
    $build['workflow_template'] = array(
      '#theme' => 'maestro_workflow_edit',
      '#tid' => $this->_template_id,
      '#mi' => $this
    );
    $build['workflow_template']['#attached']['library'][] = array('system', 'ui.draggable');
    $build['workflow_template']['#attached']['js'][] = array('data' => '(function($){$(function() { $(".maestro_task_container").draggable( {snap: true} ); })})(jQuery);', 'type' => 'inline');

    return drupal_render($build);
  }

  //should get the valid task types to create, excluding start and end tasks, from the drupal cache
  function getContextMenu() {
    $options = array(
      'If' => 'If Task',
      'Batch' => 'Batch Task'
    );

    return $options;
  }

  function getContextMenuHTML() {
    $options = $this->getContextMenu();
    $html = "<div id=\"maestro_main_context_menu\" class=\"maestro_context_menu\"><ul>\n";

    foreach ($options as $key => $option) {
      $option = t($option);
      $html .= "<li style=\"white-space: nowrap;\" id=\"$key\">$option</li>\n";
    }
    $html .= "</ul></div>\n";

    return $html;
  }

  function getContextMenuJS() {
    $options = $this->getContextMenu();
    $js  = "(function ($) {\n";
    $js .= "\$('#maestro_workflow_container').contextMenu('maestro_main_context_menu', {\n";
    $js .= "menuStyle: {\n";
    $js .= "width: 175,\n";
    $js .= "fontSize: 12,\n";
    $js .= "},\n";

    $js .= "itemStyle: {\n";
    $js .= "padding: 0,\n";
    $js .= "paddingLeft: 10,\n";
    $js .= "},\n";

    $js .= "bindings: {\n";

    foreach ($options as $key => $option) {
      $js .= "'$key': function(t) {\n";
      $js .= "\$.post(ajax_url + 'MaestroTaskInterface$key/0/create/', {task_type: '$key', offset_left: t.offsetLeft, offset_top: t.offsetTop});\n";
      $js .= "},\n";
    }

    $js .= "}\n";
    $js .= "});\n";
    $js .= "})(jQuery);\n";

    return $js;
  }

  function initializeJavascriptArrays() {
    $js = '';
    $res = db_query('SELECT id, offset_left, offset_top FROM {maestro_template_data} WHERE template_id=:tid', array(':tid' => $this->_template_id));
    $i = 0;
    $j = 0;
    foreach ($res as $rec) {
      $js .= "existing_tasks[{$i}] = ['task{$rec->id}', {$rec->offset_left}, {$rec->offset_top}];\n";
      $i++;
      $res2 = DB_query("SELECT template_data_to, template_data_to_false FROM {maestro_template_data_next_step} WHERE template_data_from=:tid", array(':tid'=>$rec->id));
      foreach ($res2 as $rec2) {
        $to = intval ($rec2->template_data_to);
        $to_false = intval ($rec2->template_data_to_false);
        if ($to != 0) {
          $js .= "line_ids[{$j}] = ['task{$rec->id}', 'task{$to}', true];\n";
          $j++;
        }
        if ($to_false != 0) {
          $js .= "line_ids[{$j}] = ['task{$rec->id}', 'task{$to_false}', false];\n";
          $j++;
        }
      }
    }

    return $js;
  }
}

?>
