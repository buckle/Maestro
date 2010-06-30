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
      '#tid' => $this->_template_id
    );
    $build['workflow_template']['#attached']['library'][] = array('system', 'ui.draggable');
    $build['workflow_template']['#attached']['js'][] = array('data' => '(function($){$(function() { $(".maestro_task_container").draggable( { grid: [25, 25], snap: true } ); })})(jQuery);', 'type' => 'inline');

    return drupal_render($build, array('tid' => $this->_template_id));
  }

  //should get the valid task types to create, excluding start and end tasks, from the drupal cache
  function getContextMenu() {
    $options = array(
      'iftask' => 'New If Task',
      'batchtask' => 'New Batch Task'
    );

    return $options;
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
