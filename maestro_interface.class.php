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
    $options = cache_get('maestro_context_menu');


    if($options === FALSE) {
      //need to scan through each available class type and fetch its corresponding context menu.
      foreach (module_implements('maestro_context_menu') as $module) {
        $function = $module . '_maestro_context_menu';
        if ($arr = $function()) {
          $options[] = $arr;
        }
      }
      cache_set('maestro_context_menu', $options);
    }
    else {
      $options = current($options->data);
    }
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
    //we need to rebuild the cache in the event it is empty.
    $options = cache_get('maestro_context_menu');
    return $options;
  }

  function getContextMenuHTML() {
    $options = $this->getContextMenu();
    $html = "<div id=\"maestro_main_context_menu\" class=\"maestro_context_menu\"><ul>\n";

    foreach ($options->data[0] as $key => $option) {
      $task_type = substr($option['class_name'], 20);
      watchdog('maestro', $task_type);
      $option = t($option['display_name']);
      $html .= "<li style=\"white-space: nowrap;\" id=\"$task_type\">$option</li>\n";
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

    foreach ($options->data[0] as $key => $option) {
      $task_type = substr($option['class_name'], 20);
      $js .= "'$task_type': function(t) {\n";
      $js .= "\$.ajax({
        type: \"POST\",
        url: ajax_url + 'MaestroTaskInterface$task_type/0/{$this->_template_id}/create/',
        dataType: \"html\",
        data: {task_type: '$task_type', offset_left: t.offsetLeft, offset_top: t.offsetTop},
        success: add_task_success
      });\n";
      //$js .= "\$.post(ajax_url + 'MaestroTaskInterface$key/0/create/', {task_type: '$key', offset_left: t.offsetLeft, offset_top: t.offsetTop});\n";
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
