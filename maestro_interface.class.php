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
}

?>
