<?php

/**
 * @file
 * maestro.views.inc
 * Contains all Views related API calls and default view creation information
 */


function maestro_views_data() {
  $data = array();
  $data['maestro_queue']['table']['group']  = t('Maestro');
  $data['maestro_template_data']['table']['group']  = t('Maestro');

  /* Table Definitions */
  $data['maestro_project_content'] = array (
    'table' => array(
      'group' => t('Maestro'),
      'join' => array(
        'maestro_queue' => array(
          'left_field' => 'id',
          'field' => 'task_id',
          ),
        ),
      ),
    'content_type' => array (
      'title' => t('Content Type'),
      'help'  => t('Content Type as defined by the workflow template process variable'),
      'field' => array (
        'handler' => 'views_handler_field',  //using a custom field handler
          'click sortable' => TRUE,
       ),
       'filter' => array(
          'handler' => 'maestro_handler_filter_content_type',
       ),
       'sort' => array(
          'handler' => 'views_handler_sort',
       ),
    ),
    'nid' => array (
      'title' => t('Content Node ID'),
      'help'  => t('Node ID - links to node'),
      'field' => array (
        'handler' => 'views_handler_field',  //using a custom field handler
          'click sortable' => TRUE,
        ),
      'filter' => array(
        'handler' => 'views_handler_field',
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
    ),
    'tracking_id' => array (
      'title' => t('Tracking ID'),
      'help'  => t('Workflow Instance or Project ID'),
      'field' => array (
        'handler' => 'views_handler_field',  //using a custom field handler
          'click sortable' => TRUE,
        ),
      'filter' => array(
        'handler' => 'views_handler_field',
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
    )
  );

  $data['maestro_template'] = array (
    'table' => array(
      'group' => t('Maestro'),
      'join' => array(
        'maestro_queue' => array(
          'left_table' => 'maestro_template_data',  // The template table joins via the template_data table to the queue table
          'left_field' => 'template_id',
          'field' => 'id',
          ),
        ),
      ),
    'template_name' => array (
      'title' => t('Template Name'),
      'help'  => t('Name of the Workflow Template'),
      'field' => array (
        'handler' => 'views_handler_field',  //using a custom field handler
          'click sortable' => TRUE,
       ),
       'filter' => array(
          'handler' => 'maestro_handler_filter_templatename',
       ),
       'sort' => array(
          'handler' => 'views_handler_sort',
       ),
    ),
  );

  $data['maestro_template_variables'] = array (
    'table' => array(
      'group' => t('Maestro'),
      'join' => array(
        'maestro_queue' => array(
          'left_table' => 'maestro_template',  // The template_variables table joins via the template table to the queue table
          'left_field' => 'id',
          'field' => 'template_id',
          ),
        ),
      ),
    'variable_name' => array (
      'real field' => 'variable_name',
      'title' => t('Process Variable Name'),
      'help'  => t('Display single Process Variable Name'),
      'field' => array (
        'handler' => 'views_handler_field',
          'click sortable' => TRUE,
       ),
       'sort' => array(
          'handler' => 'views_handler_sort',
       ),
       'filter' => array(
          'handler' => 'maestro_handler_filter_process_variable',
       ),
    ),
    'variable_names' => array (
      'real field' => 'variable_name',
      'title' => t('Process Variables'),
      'help'  => t('Displays all the Process Variables and their values'),
      'field' => array (
        'handler' => 'views_handler_field_maestro_process_variables',  //using a custom field handler
          'click sortable' => TRUE,
       ),
       'sort' => array(
          'handler' => 'views_handler_sort',
       ),
    ),
  );

  $data['maestro_process'] = array (
    'table' => array(
      'group' => t('Maestro'),
      'join' => array(
        'maestro_queue' => array(
          'left_field' => 'process_id',
          'field' => 'id',
          ),
        ),
      ),
    'id' => array (
      'title' => t('Process Table Record ID'),
      'help'  => t('Unique Process Identifier'),
      'field' => array (
        'click sortable' => TRUE,
      )
    ),
  );

  $data['maestro_process_variables'] = array (
    'table' => array(
      'group' => t('Maestro'),
      'join' => array(
        'maestro_queue' => array(
          'left_field' => 'process_id',
          'field' => 'process_id',
          ),
        ),
      ),
    'template_variable_id' => array (
      'title' => t('Template Variable ID'),
      'help'  => t('Variable ID'),
      'field' => array (
        'click sortable' => TRUE,
      )
    ),
     'variable_value' => array (
      'title' => t('Process Variable Value'),
      'help'  => t('Process Variable value - use with Process Variable Name'),
      'field' => array (
        'handler' => 'views_handler_field_maestro_process_variable',  //using a custom field handler
        'click sortable' => TRUE,
      ),
       'filter' => array(
          'handler' => 'maestro_handler_filter_variable_value',
       ),
    ),
  );

  // Workflow template tasks
  $data['maestro_template_data']['table']['join'] = array(  //this one allows the template data fields to be available in the queue table view
    'maestro_queue' => array(
      'left_field' => 'template_data_id',
      'field' => 'id',
    ),
  );

  // Workflow Engine Queue
  $data['maestro_queue']['table']['base'] = array(
    'field' => 'id',
    'title' => t('Maestro Queue'),
    'help' => t("Queue holds all of the production data for tasks"),
    'weight' => -11,
  );

  $data['users']['table']['join'] = array(  //telling the users table that we're joined to it by uid
    'maestro_queue' => array(
      'left_field' => 'uid',
      'field' => 'uid',
    ),
  );


  // Production Assignments - active tasks that and their assignment data
  $data['maestro_production_assignments'] = array(
    'table' => array(
      'group' => t('Maestro'),
      'join' => array(  //telling the queue table that we're joined to it by taskID
          'maestro_queue' => array(
          'left_field' => 'id',
          'field' => 'task_id',
          ),
      ),
    ),
    'assign_id' => array (
      'title' => t('Assignment Value'),
      'help'  => t('Production Assignment Record Value'),
      'field' => array (
        'click sortable' => TRUE,
      )
    ),
  );

  // Views Field definitions

  $data['maestro_template_data']['taskname'] = array(
    'title' => t('Task Name'),
    'help' => t('A Reference to the template data table'),
    'field' => array(
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'maestro_handler_filter_taskname',
    ),
  );

  $data['maestro_queue']['assigned_users'] = array(
    'real field' => 'id',   // Need to pass in the queue_id to the handler
    'title' => t('Assigned Users'),
    'help' => t('All of the assigned users'),
    'field' => array (
      'handler' => 'views_handler_field_maestro_assigned_users',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'maestro_handler_filter_assigned_user',
    ),
  );

  $data['maestro_queue']['template_data_id'] = array(
    'title' => t('Template Data ID'),
    'help' => t('A Reference to the template data table'),
    'field' => array (
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
  );

  $data['maestro_queue']['id'] = array(
    'title' => t('Queue Task ID'),
    'help' => t('The workflow engine\'s unique task id'),
    'field' => array (
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ),
  );


  $data['maestro_queue']['process_id'] = array (
    'title' => t('Process ID'),
    'help'  => t('Unique Process Identifier'),
    'field' => array (
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ),
  );

  $data['maestro_queue']['created_date'] = array (
    'title' => t('Assigned Date'),
    'help'  => t('Task Assigned Date'),
    'field' => array (
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
    ),
  );

  $data['maestro_queue']['started_date'] = array (
    'title' => t('Started Date'),
    'help'  => t('Task Started Date'),
    'field' => array (
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
    ),
  );

  $data['maestro_queue']['task_class_name'] = array (
    'title' => t('Task Type'),
    'help'  => t('Task Type'),
    'field' => array(
      'handler' => 'views_handler_field_maestro_tasktype',
      'click sortable' => TRUE,
    ),
  );

  $data['maestro_queue']['completed_date'] = array (
    'title' => t('Completed Date'),
    'help'  => t('Task Completed Date'),
    'field' => array (
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
    ),
  );

  $data['maestro_queue']['engine_version'] = array (
    'title' => t('Engine Version'),
    'help'  => t('Version of the engine used to run this task'),
    'field' => array (
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  $data['maestro_queue']['is_interactive'] = array (
    'title' => t('Interactive Flag'),
    'help'  => t('Set to 1 for interactive. 0 for non-interactive'),
    'field' => array (
      'handler' => 'views_handler_field_maestro_interactive',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

    $data['maestro_queue']['status'] = array (
    'title' => t('Status'),
    'help'  => t('Task Status'),
    'field' => array (
      'handler' => 'views_handler_field_maestro_status',  //using a custom field handler
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'maestro_handler_filter_status',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  $data['maestro_queue']['uid'] = array (
    'title' => t('Completed By'),
    'help'  => t('The user who completed the task'),
    'field' => array (
      'handler' => 'views_handler_field_maestro_uid',  //using a custom field handler
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  return $data;
}



/*** Views Filter Handlers ***/


/**
 * Views Filter Handler for the maestro workflow status
 */
class maestro_handler_filter_status extends views_handler_filter {
  /**
  * Shortcut to display the exposed options form.
  */
  function value_form(&$form, &$form_state) {
    global $base_path;
    @include_once($base_path . drupal_get_path('module','maestro') . '/maestro_constants.class.php');
    $reflect = new ReflectionClass('MaestroTaskStatusCodes');
    $statusConstants = $reflect->getConstants();
    $options = array();
    foreach ($statusConstants as $label => $value) {
      $options[$value] = MaestroTaskStatusCodes::getStatusLabel($value);
    }
    $form['value'] = array(
    '#type' => 'select',
    '#title' => t('Workflow Status'),
    '#options' => $options
    );

    return $form;
  }

  /**
  * Change the query.
  */
  function query() {
    // Ensure table alias has been set
    $this->ensure_my_table();
    $field = "$this->table_alias.$this->real_field";
    // Get status value
    $status_value = '';
    if(isset($this->view->exposed_input['status'])) {
      $status_value = check_plain($this->view->exposed_input['status']);
    } else if (empty($status_value) AND isset($this->options['value'])) {
      $status_value = $this->options['value'];
    }
    // Setup query
    $this->query->add_where($this->options['group'],$field,$status_value,'=');
  }

}

/**
 * Views Filter Handler for the maestro content type - only active workflow content types
 */
class maestro_handler_filter_content_type extends views_handler_filter {
  /**
  * Shortcut to display the exposed options form.
  */
  function value_form(&$form, &$form_state) {
    $query = db_select('maestro_project_content', 'a');
    $query->fields('a', array('content_type'));
    $query->distinct(TRUE);
    $content_type_result = $query->execute();
    $options = array();
    foreach ($content_type_result as $rec) {
      $type = check_plain($rec->content_type);
      $options[$type] = $type;
    }
    $form['value'] = array(
    '#type' => 'select',
    '#title' => t('Content Type'),
    '#options' => $options
    );

    return $form;
  }

  /**
  * Change the query.
  */
  function query() {
    // Ensure table alias has been set
    $this->ensure_my_table();
    $field = "$this->table_alias.$this->real_field";
    // Get content type value
    $content_type_value = '';
    if(isset($this->view->exposed_input['content_type'])) {
      $content_type_value = check_plain($this->view->exposed_input['content_type']);
    } elseif (empty($content_type_value) AND isset($this->options['value'])) {
      $content_type_value = $this->options['value'];
    }
    // Setup query
    $this->query->add_where($this->options['group'],$field,$content_type_value,'=');
  }

}



/**
 * Views Filter Handler for the maestro taskname
 * Return a list of unique interactive task names from all workflow templates
 */
class maestro_handler_filter_taskname extends views_handler_filter {
  /**
  * Shortcut to display the exposed options form.
  */
  function value_form(&$form, &$form_state) {
    $query = db_select('maestro_template_data', 'a');
    $query->fields('a', array('id','taskname'));
    $query->condition('a.is_interactive', 1, '=');
    $query->distinct(TRUE);
    $taskname_result = $query->execute();
    $options = array();
    foreach ($taskname_result as $rec) {
      $taskname = check_plain($rec->taskname);
      $options[$rec->id] = $taskname;
    }
    $form['value'] = array(
    '#type' => 'select',
    '#title' => t('Task Name'),
    '#options' => $options
    );

    return $form;
  }

  /**
  * Change the query.
  */
  function query() {
    // Ensure table alias has been set
    $this->ensure_my_table();
    $field = "$this->table_alias.id";
    // Get taskname value
    $taskname_value = '';
    if(isset($this->view->exposed_input['taskname'])) {
      $taskname_value = check_plain($this->view->exposed_input['taskname']);
    } elseif (empty($taskname_value) AND isset($this->options['value'])) {
      $taskname_value = $this->options['value'];
    }
    // Setup query
    $this->query->add_where($this->options['group'],$field,$taskname_value,'=');
  }

}


/**
 * Views Filter Handler for the maestro template Name
 * Return a list of the maestro workflow templates
 */
class maestro_handler_filter_templatename extends views_handler_filter {
  /**
  * Shortcut to display the exposed options form.
  */
  function value_form(&$form, &$form_state) {
    $query = db_select('maestro_template', 'a');
    $query->fields('a', array('id','template_name'));
    $taskname_result = $query->execute();
    $options = array();
    foreach ($taskname_result as $rec) {
      $template = check_plain($rec->template_name);
      $options[$rec->id] = $template;
    }
    $form['value'] = array(
    '#type' => 'select',
    '#title' => t('Template Name'),
    '#options' => $options
    );

    return $form;
  }

  /**
  * Change the query.
  */
  function query() {
    // Ensure table alias has been set
    $this->ensure_my_table();
    $field = "$this->table_alias.id";
    // Get templatename value
    $templatename_value = '';
    if(isset($this->view->exposed_input['template_name'])) {
      $templatename_value = check_plain($this->view->exposed_input['template_name']);
    } elseif (empty($templatename_value) AND isset($this->options['value'])) {
      $templatename_value = $this->options['value'];
    }
    // Setup query
    $this->query->add_where($this->options['group'],$field,$templatename_value,'=');
  }

}


/**
 * Views Filter Handler for the maestro content type - only active workflow content types
 */
class maestro_handler_filter_process_variable extends views_handler_filter {
  /**
  * Shortcut to display the exposed options form.
  */
  function value_form(&$form, &$form_state) {
    $query = db_select('maestro_template_variables', 'a');
    $query->fields('a', array('variable_name'));
    $query->distinct(TRUE);
    $result = $query->execute();
    $options = array();
    foreach ($result as $rec) {
      $name = check_plain($rec->variable_name);
      $options[$name] = $name;
    }

    $form['value'] = array(
    '#type' => 'select',
    '#title' => t('Process Variable'),
    '#options' => $options
    );

    return $form;
  }

  /**
  * Change the query.
  */
  function query() {
    // Ensure table alias has been set
    $this->ensure_my_table();
    $field = "$this->table_alias.$this->real_field";
    // Get variable value
    $variable_value = '';
    if(isset($this->view->exposed_input['variable_name'])) {
      $variable_value = check_plain($this->view->exposed_input['variable_name']);
    } elseif (empty($variable_value) AND isset($this->options['value'])) {
      $variable_value = $this->options['value'];
    }

    /* The selected variable may be used for different templates.
     * We need to find the variable id's since we don't have a single template
     */
    $query = db_select('maestro_template_variables', 'a');
    $query->fields('a', array('id'));
    $query->condition('a.variable_name',$variable_value,'=');
    $result = $query->execute();
    $template_variables = array();
    foreach ($result as $rec) {
      $template_variables[] = $rec->id;
    }

    // Setup query: Requires that the variable_value field be seleted as that is what adds the maestro_process_variables table.
    //$this->query->add_field('maestro_process_variables','template_variable_id');
    $this->query->add_where(0,'maestro_process_variables.template_variable_id', $template_variables);
  }


}


/**
 * Views Filter Handler for the task assigned user
 */
class maestro_handler_filter_assigned_user extends views_handler_filter {
  /**
  * Shortcut to display the exposed options form.
  */
  function value_form(&$form, &$form_state) {
    $query = db_select('users', 'a');
    $query->fields('a', array('uid','name'));
    $query->condition('a.status',1,'=');
    $query->orderBy('name');
    $result = $query->execute();
    $options = array();
    foreach ($result as $rec) {
      $name = check_plain($rec->name);
      $options[$rec->uid] = $name;
    }

    $form['value'] = array(
    '#type' => 'select',
    '#title' => t('Assigned User'),
    '#options' => $options
    );

    return $form;
  }

  /**
  * Change the query.
  */
  function query() {
    // Ensure table alias has been set
    $this->ensure_my_table();
    $field = "$this->table_alias.$this->real_field";
    // Get variable value
    $variable_value = '';
    if(isset($this->view->exposed_input['assigned_users'])) {
      $assigned_user = check_plain($this->view->exposed_input['assigned_users']);
    } elseif (empty($variable_value) AND isset($this->options['value'])) {
      $assigned_user = $this->options['value'];
    }


    if(!empty($assigned_user) AND $assigned_user != 'All') {

      /* Assume we are looking for currently assigned tasks
       * Tasks in the ready state - use the production_assignment table
       *
       * If the task status filter is being used and State 'Completed' is selected
       * then we need to just check the queue table (uid field).
       *
       * Any other state makes no sense to use the 'assigned users' filter for
       *
       */

       if (isset($this->view->exposed_input['status'])) {

        if($this->view->exposed_input['status'] == MaestroTaskStatusCodes::STATUS_READY OR $this->view->exposed_input['status'] == 'All') {
          /* Task is assignment is done by 3 types (1: by user, 2: by role, 3: by group)
           * Need to determine all the roles and groups the selected filter user is in
           * and then modify the query.
           */

          $expression = '(maestro_production_assignments.assign_id IN (:uids) AND maestro_production_assignments.assign_type = 1) ';
          $args = array(':uids' => array($assigned_user));

          /* Add filter condition for roles if user is a member of any roles */
          $query = db_select('users_roles', 'a');
          $query->fields('a', array('rid'));
          $query->condition('a.uid',$assigned_user,'=');
          $result = $query->execute();
          $rids = array();
          foreach ($result as $rec) {
            $rids[] = $rec->rid;
          }
          if (!empty($rids)) {
            $expression .= 'OR (maestro_production_assignments.assign_id IN (:rids) AND maestro_production_assignments.assign_type = 2) ';
            $args[':rids']  = $rids;
          }

          /* Add filter condition for groups if enabled and user is any groups */
          if (module_exists('og')) {
            $query = db_select('field_data_group_audience', 'a');
            $query->addField('a','group_audience_gid', 'gid');
            $query->condition('a.entity_type','user','=');
            $query->condition('a.entity_id',$assigned_user,'=');
            $result = $query->execute();
            $gids = array();
            foreach ($result as $rec) {
              $gids[] = $rec->gid;
            }
            if (!empty($gids)) {
              $expression .= 'OR (maestro_production_assignments.assign_id IN (:gids) AND maestro_production_assignments.assign_type = 3)';
              $args[':gids'] = $gids;
            }
          }

          // Setup query: Requires that the variable_value field be seleted as that is what adds the maestro_process_variables table.
          $this->query->add_field('maestro_production_assignments','assign_id');
          $this->query->add_where(0,'1', 1);
          $this->query->add_where_expression(1, $expression, $args);

        } elseif ($this->view->exposed_input['status'] == MaestroTaskStatusCodes::STATUS_COMPLETE) {
          /* Looking for completed tasks - which are by a single user and only need to now use the queue table */
          $this->query->add_where(0,'maestro_queue.uid', $assigned_user);
        }

       }

    }

  }

}

/**
 * Views Filter Handler for the maestro content type - only active workflow content types
 */
class maestro_handler_filter_variable_value extends views_handler_filter {
  /**
  * Shortcut to display the exposed options form.
  */
  function value_form(&$form, &$form_state) {
    $query = db_select('maestro_process_variables', 'a');
    $query->fields('a', array('variable_value'));
    $query->distinct(TRUE);
    $result = $query->execute();
    $options = array();
    foreach ($result as $rec) {
      $value = check_plain($rec->variable_value);
      $options[$value] = $value;
    }

    natsort($options);

    $form['value'] = array(
    '#type' => 'select',
    '#title' => t('Variable Value'),
    '#options' => $options
    );

    return $form;
  }

  /**
  * Change the query.
  */
  function query() {
    // Ensure table alias has been set
    $this->ensure_my_table();
    $field = "$this->table_alias.$this->real_field";
    // Get variable value
    $variable_value = '';
    if(isset($this->view->exposed_input['variable_value'])) {
      $variable_value = check_plain($this->view->exposed_input['variable_value']);
    } elseif (empty($variable_value) AND isset($this->options['value'])) {
      $variable_value = $this->options['value'];
    }

    if (!empty($variable_value)) {
     $this->query->add_where(0,'maestro_process_variables.variable_value', $variable_value);
    }
  }
}



/*** View Handlers for the Field Value Display ***/

/**
 * Field handler to display the Maestro Status
 */
class views_handler_field_maestro_status extends views_handler_field_numeric {

  function render($values) {
    global $base_path;
    @include_once($base_path . drupal_get_path('module','maestro') . '/maestro_constants.class.php');
    $value = $values->{$this->field_alias};
    return MaestroTaskStatusCodes::getStatusLabel($value);
  }
}

/**
 * Field handler to display the Maestro Interactive Task
 */
class views_handler_field_maestro_interactive extends views_handler_field_numeric {

  function render($values) {
    if ($values->maestro_queue_is_interactive == 1) {
      return t('Interactive');
    } else {
      return t('Non-Interactive');
    }
  }
}

/**
 * Field handler to display the Maestro Task Type
 */
class views_handler_field_maestro_tasktype extends views_handler_field_numeric {

  function render($values) {
    return str_replace('MaestroTaskType','',$values->maestro_queue_task_class_name);
  }
}


/**
 * Field handler to display the Completed User
 */
class views_handler_field_maestro_uid extends views_handler_field_numeric {
  function render($values) {
    $value = $values->{$this->field_alias};
    if ($value==0 && isset($value)) {
      return 'Maestro';
    }
    elseif (!isset($value)) {
      return t('Not yet completed');
    }
    else {
      return $value;
    }
  }
}


/**
 * Field handler to display the assigned users
 */
class views_handler_field_maestro_assigned_users extends views_handler_field_numeric {
  function render($values) {
    $output = '';
    $taskid = $values->{$this->field_alias};
    $maestro = Maestro::createMaestroObject(1);
    if ($maestro->engine()->getTaskStatus($taskid) == MaestroTaskStatusCodes::STATUS_READY ) {
      $assigned = $maestro->engine()->getAssigned($taskid);
      foreach ($assigned as $type => $assignments) {
        if ($type == 'USER') {
          foreach ($assignments as $assignment) {
              if($output != '') $output .= "<br>";
              $output .= t('User') . ": {$assignment}";
          }
        } elseif ($type == 'ROLE') {
          foreach ($assignments as $assignment) {
              if($output != '') $output .= "<br>";
              $output .= t('Role') . ": {$assignment}";
          }
        } elseif ($type == 'GROUP') {
          foreach ($assignments as $assignment) {
              if($output != '') $output .= "<br>";
              $output .= t('Group') . ": {$assignment}";
          }
        }
      }
      if($output == '') {
        $output = t('Maestro');
      }
    } else {
        $output = 'N/A';
    }
    return $output;
  }
}


/**
 * Field handler to display the values for all the current process variables
 */
class views_handler_field_maestro_process_variables extends views_handler_field {
  function render($values) {
    $output = '';
    $query = db_select('maestro_queue', 'a');
    $query->join('maestro_process_variables', 'b', 'b.process_id = a.process_id');
    $query->join('maestro_template_variables', 'c', 'b.template_variable_id = c.id');
    $query->addField('a','process_id');
    $query->addField('b','variable_value');
    $query->addField('c','variable_name');
    $query->condition('a.id', $values->id, '=');
    $res = $query->execute();
    foreach ($res as $rec) {
      if($output != '') $output .= "<br>";
      $output .= "{$rec->variable_name}:&nbsp;{$rec->variable_value}";
    }

    return $output;

  }
}


/**
 * Field handler to display the single process variable value
 */
class views_handler_field_maestro_process_variable extends views_handler_field {
  function render($values) {
    $output = '';
    $query = db_select('maestro_queue', 'a');
    $query->join('maestro_process_variables', 'b', 'b.process_id = a.process_id');
    $query->join('maestro_template_variables', 'c', 'b.template_variable_id = c.id');
    $query->addField('b','variable_value');
    $query->condition('a.id', $values->id, '=');
    $query->condition('c.variable_name', $values->maestro_template_variables_variable_name, '=');
    $res = $query->execute()->fetchAssoc();
    if ($res) {
      return $res['variable_value'];
    } else {
      return 'N/A';
    }

  }
}



/**
 * HOOK views_pre_execute
 * Need to alter the SQL query for the join on the process_variables table
 */
function maestro_views_pre_execute(&$view) {
  $query = $view->build_info['query'];
  $tables =& $query->getTables();
  $fields =& $query->getFields();
  $where =& $query->conditions();

  if(isset($view->exposed_input['variable_name']) OR isset($fields['maestro_template_variables_variable_name'])) {

    /* The query object in $view is a selectQuery object
       To eliminate the duplicate records, needed to change the
       order of the joins and use a more specific join condition.
       The join of the process_variables table was creating
       the duplicates even with DISTINCT query option.

       Reference: http://drupal.org/node/310077
     */

    $process_variables = $tables['maestro_process_variables'];
    unset($tables['maestro_process_variables']);
    $template_variables = $tables['maestro_template_variables'];
    unset($tables['maestro_template_variables']);
    $tables['maestro_process_variables'] = $process_variables;
    $tables['maestro_template_variables'] = $template_variables;

    $tables['maestro_template_variables']['condition'] = "maestro_template.id = maestro_template_variables.template_id AND " .
      "maestro_process_variables.template_variable_id = maestro_template_variables.id AND " .
      "maestro_process_variables.process_id = maestro_queue.process_id";

  }

}


/**
 * Implements hook_views_default_views().
 */
function maestro_views_default_views() {

  /* Example view showing process variables */
  $view = new view;
  $view->name = 'Maestro Task Process Variables';
  $view->description = 'Example view showing process variables';
  $view->tag = 'maestro';
  $view->base_table = 'maestro_queue';
  $view->human_name = 'Maestro View - All Tasks and Process Variables';
  $view->core = 0;
  $view->api_version = '3.0-alpha1';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Defaults */
  $handler = $view->new_display('default', 'Defaults', 'default');
  $handler->display->display_options['title'] = 'Maestro View - All Tasks';
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '50';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'process_id' => 'process_id',
    'id' => 'id',
    'status' => 'status',
    'taskname' => 'taskname',
    'uid' => 'uid',
    'template_name' => 'template_name',
    'assigned_users' => 'assigned_users',
    'assign_id' => 'assign_id',
    'variable_name' => 'variable_name',
    'variable_value' => 'variable_value',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'process_id' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'id' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'status' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'taskname' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'uid' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'template_name' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'assigned_users' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'assign_id' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'variable_name' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
    'variable_value' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
    ),
  );
  $handler->display->display_options['style_options']['override'] = 1;
  $handler->display->display_options['style_options']['sticky'] = 0;
  $handler->display->display_options['style_options']['empty_table'] = 0;
  /* Field: Maestro: Process ID */
  $handler->display->display_options['fields']['process_id']['id'] = 'process_id';
  $handler->display->display_options['fields']['process_id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['process_id']['field'] = 'process_id';
  /* Field: Maestro: Status */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  /* Field: Maestro: Task Name */
  $handler->display->display_options['fields']['taskname']['id'] = 'taskname';
  $handler->display->display_options['fields']['taskname']['table'] = 'maestro_template_data';
  $handler->display->display_options['fields']['taskname']['field'] = 'taskname';
  /* Field: Maestro: Completed By */
  $handler->display->display_options['fields']['uid']['id'] = 'uid';
  $handler->display->display_options['fields']['uid']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['uid']['field'] = 'uid';
  /* Field: Maestro: Template Name */
  $handler->display->display_options['fields']['template_name']['id'] = 'template_name';
  $handler->display->display_options['fields']['template_name']['table'] = 'maestro_template';
  $handler->display->display_options['fields']['template_name']['field'] = 'template_name';
  /* Field: Maestro: Assigned Users */
  $handler->display->display_options['fields']['assigned_users']['id'] = 'assigned_users';
  $handler->display->display_options['fields']['assigned_users']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['assigned_users']['field'] = 'assigned_users';
  /* Filter criterion: Maestro: Process Variable Name */
  $handler->display->display_options['filters']['variable_name']['id'] = 'variable_name';
  $handler->display->display_options['filters']['variable_name']['table'] = 'maestro_template_variables';
  $handler->display->display_options['filters']['variable_name']['field'] = 'variable_name';
  $handler->display->display_options['filters']['variable_name']['value'] = 'initiator';
  $handler->display->display_options['filters']['variable_name']['expose']['operator_id'] = '';
  $handler->display->display_options['filters']['variable_name']['expose']['label'] = 'Maestro: Process Variable Name';
  $handler->display->display_options['filters']['variable_name']['expose']['use_operator'] = FALSE;
  $handler->display->display_options['filters']['variable_name']['expose']['operator'] = 'variable_name_op';
  $handler->display->display_options['filters']['variable_name']['expose']['identifier'] = 'variable_name';

  /* Display: Maestro View - Task Process Variables */
  $handler = $view->new_display('page', 'Maestro View - Task Process Variables', 'all_queue_tasks');
  $handler->display->display_options['defaults']['fields'] = FALSE;
  /* Field: Maestro: Process ID */
  $handler->display->display_options['fields']['process_id']['id'] = 'process_id';
  $handler->display->display_options['fields']['process_id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['process_id']['field'] = 'process_id';
  /* Field: Maestro: Queue Task ID */
  $handler->display->display_options['fields']['id']['id'] = 'id';
  $handler->display->display_options['fields']['id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['id']['field'] = 'id';
  $handler->display->display_options['fields']['id']['label'] = 'Queue ID';
  $handler->display->display_options['fields']['id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['id']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['id']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['id']['empty_zero'] = 0;
  /* Field: Maestro: Status */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  $handler->display->display_options['fields']['status']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['status']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['status']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['status']['alter']['external'] = 0;
  $handler->display->display_options['fields']['status']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['status']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['status']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['status']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['status']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['status']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['status']['alter']['html'] = 0;
  $handler->display->display_options['fields']['status']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['status']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['status']['hide_empty'] = 0;
  $handler->display->display_options['fields']['status']['empty_zero'] = 0;
  $handler->display->display_options['fields']['status']['format_plural'] = 0;
  /* Field: Maestro: Template Name */
  $handler->display->display_options['fields']['template_name']['id'] = 'template_name';
  $handler->display->display_options['fields']['template_name']['table'] = 'maestro_template';
  $handler->display->display_options['fields']['template_name']['field'] = 'template_name';
  $handler->display->display_options['fields']['template_name']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['template_name']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['template_name']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['template_name']['alter']['external'] = 0;
  $handler->display->display_options['fields']['template_name']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['template_name']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['template_name']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['template_name']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['template_name']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['template_name']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['template_name']['alter']['html'] = 0;
  $handler->display->display_options['fields']['template_name']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['template_name']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['template_name']['hide_empty'] = 0;
  $handler->display->display_options['fields']['template_name']['empty_zero'] = 0;
  /* Field: Maestro: Task Name */
  $handler->display->display_options['fields']['taskname']['id'] = 'taskname';
  $handler->display->display_options['fields']['taskname']['table'] = 'maestro_template_data';
  $handler->display->display_options['fields']['taskname']['field'] = 'taskname';
  $handler->display->display_options['fields']['taskname']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['external'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['taskname']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['taskname']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['html'] = 0;
  $handler->display->display_options['fields']['taskname']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['taskname']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['taskname']['hide_empty'] = 0;
  $handler->display->display_options['fields']['taskname']['empty_zero'] = 0;
  /* Field: Maestro: Process Variables */
  $handler->display->display_options['fields']['variable_names']['id'] = 'variable_names';
  $handler->display->display_options['fields']['variable_names']['table'] = 'maestro_template_variables';
  $handler->display->display_options['fields']['variable_names']['field'] = 'variable_names';
  $handler->display->display_options['fields']['variable_names']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['variable_names']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['variable_names']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['variable_names']['alter']['external'] = 0;
  $handler->display->display_options['fields']['variable_names']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['variable_names']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['variable_names']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['variable_names']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['variable_names']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['variable_names']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['variable_names']['alter']['html'] = 0;
  $handler->display->display_options['fields']['variable_names']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['variable_names']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['variable_names']['hide_empty'] = 0;
  $handler->display->display_options['fields']['variable_names']['empty_zero'] = 0;
  /* Field: Maestro: Template Variable ID */
  $handler->display->display_options['fields']['template_variable_id']['id'] = 'template_variable_id';
  $handler->display->display_options['fields']['template_variable_id']['table'] = 'maestro_process_variables';
  $handler->display->display_options['fields']['template_variable_id']['field'] = 'template_variable_id';
  $handler->display->display_options['fields']['template_variable_id']['exclude'] = TRUE;
  $handler->display->display_options['fields']['template_variable_id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['template_variable_id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['template_variable_id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['template_variable_id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['template_variable_id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['template_variable_id']['empty_zero'] = 0;
  $handler->display->display_options['path'] = 'maestro_process_variables';
  $handler->display->display_options['menu']['type'] = 'normal';
  $handler->display->display_options['menu']['title'] = 'Maestro Process Variables';
  $handler->display->display_options['menu']['weight'] = '0';
  $translatables['All Queue Tasks'] = array(
    t('Defaults'),
    t('Maestro View - All Tasks'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Items per page'),
    t('- All -'),
    t('Offset'),
    t('Process ID'),
    t('Status'),
    t('.'),
    t(','),
    t('Task Name'),
    t('Completed By'),
    t('Template Name'),
    t('Assigned Users'),
    t('Maestro: Process Variable Name'),
    t('Maestro View - Task Process Variables'),
    t('Queue ID'),
    t('Process Variables'),
    t('Template Variable ID'),
  );
  $views[$view->name] = $view;

  /* Example with using exposed filters for Assigned User and Process Variable */
  $view = new view;
  $view->name = 'maestro_exposed';
  $view->description = 'Example with using exposed filters for Assigned User and Process Variable';
  $view->tag = 'default';
  $view->base_table = 'maestro_queue';
  $view->human_name = 'Maestro Assigned User and Process Variable';
  $view->core = 7;
  $view->api_version = '3.0-alpha1';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Maestro - exposed filter example';
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '50';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['expose']['items_per_page_options_all'] = 0;
  $handler->display->display_options['style_plugin'] = 'table';
  /* Field: Maestro: Process ID */
  $handler->display->display_options['fields']['process_id']['id'] = 'process_id';
  $handler->display->display_options['fields']['process_id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['process_id']['field'] = 'process_id';
  $handler->display->display_options['fields']['process_id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['process_id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['process_id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['process_id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['process_id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['process_id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['process_id']['empty_zero'] = 0;
  /* Field: Maestro: Queue Task ID */
  $handler->display->display_options['fields']['id']['id'] = 'id';
  $handler->display->display_options['fields']['id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['id']['field'] = 'id';
  $handler->display->display_options['fields']['id']['label'] = 'Task ID';
  $handler->display->display_options['fields']['id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['id']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['id']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['id']['empty_zero'] = 0;
  /* Field: Maestro: Status */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  $handler->display->display_options['fields']['status']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['status']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['status']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['status']['alter']['external'] = 0;
  $handler->display->display_options['fields']['status']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['status']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['status']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['status']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['status']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['status']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['status']['alter']['html'] = 0;
  $handler->display->display_options['fields']['status']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['status']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['status']['hide_empty'] = 0;
  $handler->display->display_options['fields']['status']['empty_zero'] = 0;
  $handler->display->display_options['fields']['status']['format_plural'] = 0;
  /* Field: Maestro: Task Name */
  $handler->display->display_options['fields']['taskname']['id'] = 'taskname';
  $handler->display->display_options['fields']['taskname']['table'] = 'maestro_template_data';
  $handler->display->display_options['fields']['taskname']['field'] = 'taskname';
  $handler->display->display_options['fields']['taskname']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['external'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['taskname']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['taskname']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['taskname']['alter']['html'] = 0;
  $handler->display->display_options['fields']['taskname']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['taskname']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['taskname']['hide_empty'] = 0;
  $handler->display->display_options['fields']['taskname']['empty_zero'] = 0;
  /* Field: Maestro: Assigned Users */
  $handler->display->display_options['fields']['assigned_users']['id'] = 'assigned_users';
  $handler->display->display_options['fields']['assigned_users']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['assigned_users']['field'] = 'assigned_users';
  /* Field: Maestro: Process Variable Name */
  $handler->display->display_options['fields']['variable_name']['id'] = 'variable_name';
  $handler->display->display_options['fields']['variable_name']['table'] = 'maestro_template_variables';
  $handler->display->display_options['fields']['variable_name']['field'] = 'variable_name';
  $handler->display->display_options['fields']['variable_name']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['external'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['variable_name']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['variable_name']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['html'] = 0;
  $handler->display->display_options['fields']['variable_name']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['variable_name']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['variable_name']['hide_empty'] = 0;
  $handler->display->display_options['fields']['variable_name']['empty_zero'] = 0;
  /* Field: Maestro: Process Variable Value */
  $handler->display->display_options['fields']['variable_value']['id'] = 'variable_value';
  $handler->display->display_options['fields']['variable_value']['table'] = 'maestro_process_variables';
  $handler->display->display_options['fields']['variable_value']['field'] = 'variable_value';
  $handler->display->display_options['fields']['variable_value']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['external'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['variable_value']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['variable_value']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['html'] = 0;
  $handler->display->display_options['fields']['variable_value']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['variable_value']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['variable_value']['hide_empty'] = 0;
  $handler->display->display_options['fields']['variable_value']['empty_zero'] = 0;
  /* Filter criterion: Maestro: Status */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'maestro_queue';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = '-3';
  $handler->display->display_options['filters']['status']['exposed'] = TRUE;
  $handler->display->display_options['filters']['status']['expose']['operator_id'] = '';
  $handler->display->display_options['filters']['status']['expose']['label'] = 'Status';
  $handler->display->display_options['filters']['status']['expose']['use_operator'] = FALSE;
  $handler->display->display_options['filters']['status']['expose']['operator'] = 'status_op';
  $handler->display->display_options['filters']['status']['expose']['identifier'] = 'status';
  /* Filter criterion: Maestro: Task Name */
  $handler->display->display_options['filters']['taskname']['id'] = 'taskname';
  $handler->display->display_options['filters']['taskname']['table'] = 'maestro_template_data';
  $handler->display->display_options['filters']['taskname']['field'] = 'taskname';
  $handler->display->display_options['filters']['taskname']['value'] = '3';
  $handler->display->display_options['filters']['taskname']['exposed'] = TRUE;
  $handler->display->display_options['filters']['taskname']['expose']['operator_id'] = '';
  $handler->display->display_options['filters']['taskname']['expose']['label'] = 'Task Name';
  $handler->display->display_options['filters']['taskname']['expose']['use_operator'] = FALSE;
  $handler->display->display_options['filters']['taskname']['expose']['operator'] = 'taskname_op';
  $handler->display->display_options['filters']['taskname']['expose']['identifier'] = 'taskname';
  /* Filter criterion: Maestro: Process Variable Name */
  $handler->display->display_options['filters']['variable_name']['id'] = 'variable_name';
  $handler->display->display_options['filters']['variable_name']['table'] = 'maestro_template_variables';
  $handler->display->display_options['filters']['variable_name']['field'] = 'variable_name';
  $handler->display->display_options['filters']['variable_name']['value'] = 'initiator';
  $handler->display->display_options['filters']['variable_name']['exposed'] = TRUE;
  $handler->display->display_options['filters']['variable_name']['expose']['operator_id'] = '';
  $handler->display->display_options['filters']['variable_name']['expose']['label'] = 'Maestro: Process Variable Name';
  $handler->display->display_options['filters']['variable_name']['expose']['use_operator'] = FALSE;
  $handler->display->display_options['filters']['variable_name']['expose']['operator'] = 'variable_name_op';
  $handler->display->display_options['filters']['variable_name']['expose']['identifier'] = 'variable_name';
  /* Filter criterion: Maestro: Assigned Users */
  $handler->display->display_options['filters']['assigned_users']['id'] = 'assigned_users';
  $handler->display->display_options['filters']['assigned_users']['table'] = 'maestro_queue';
  $handler->display->display_options['filters']['assigned_users']['field'] = 'assigned_users';
  $handler->display->display_options['filters']['assigned_users']['value'] = '1';
  $handler->display->display_options['filters']['assigned_users']['exposed'] = TRUE;
  $handler->display->display_options['filters']['assigned_users']['expose']['operator_id'] = '';
  $handler->display->display_options['filters']['assigned_users']['expose']['label'] = 'Assigned Users';
  $handler->display->display_options['filters']['assigned_users']['expose']['use_operator'] = FALSE;
  $handler->display->display_options['filters']['assigned_users']['expose']['operator'] = 'assigned_users_op';
  $handler->display->display_options['filters']['assigned_users']['expose']['identifier'] = 'assigned_users';

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'maestro-assigned';
  $translatables['maestro_all_tasks'] = array(
    t('Master'),
    t('Maestro - exposed filter example'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Items per page'),
    t('- All -'),
    t('Offset'),
    t('Process ID'),
    t('Task ID'),
    t('Status'),
    t('.'),
    t(','),
    t('Task Name'),
    t('Assigned Users'),
    t('Process Variable Name'),
    t('Process Variable Value'),
    t('Maestro: Process Variable Name'),
    t('Page'),
  );
  $views[$view->name] = $view;


  /* Example using the variable name and variable value exposed filters  */
  $view = new view;
  $view->name = 'maestro_variable_value_filter';
  $view->description = 'Example using the variable name and variable value exposed filters';
  $view->tag = 'default';
  $view->base_table = 'maestro_queue';
  $view->human_name = 'maestro variable value filter';
  $view->core = 7;
  $view->api_version = '3.0-alpha1';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'maestro variable value filter';
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '50';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['expose']['items_per_page_options_all'] = 0;
  $handler->display->display_options['style_plugin'] = 'table';
  /* Field: Maestro: Process ID */
  $handler->display->display_options['fields']['process_id']['id'] = 'process_id';
  $handler->display->display_options['fields']['process_id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['process_id']['field'] = 'process_id';
  $handler->display->display_options['fields']['process_id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['process_id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['process_id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['process_id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['process_id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['process_id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['process_id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['process_id']['empty_zero'] = 0;
  /* Field: Maestro: Queue Task ID */
  $handler->display->display_options['fields']['id']['id'] = 'id';
  $handler->display->display_options['fields']['id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['id']['field'] = 'id';
  $handler->display->display_options['fields']['id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['id']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['id']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['id']['empty_zero'] = 0;
  /* Field: Maestro: Process Variable Name */
  $handler->display->display_options['fields']['variable_name']['id'] = 'variable_name';
  $handler->display->display_options['fields']['variable_name']['table'] = 'maestro_template_variables';
  $handler->display->display_options['fields']['variable_name']['field'] = 'variable_name';
  $handler->display->display_options['fields']['variable_name']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['external'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['variable_name']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['variable_name']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['variable_name']['alter']['html'] = 0;
  $handler->display->display_options['fields']['variable_name']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['variable_name']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['variable_name']['hide_empty'] = 0;
  $handler->display->display_options['fields']['variable_name']['empty_zero'] = 0;
  /* Field: Maestro: Process Variable Value */
  $handler->display->display_options['fields']['variable_value']['id'] = 'variable_value';
  $handler->display->display_options['fields']['variable_value']['table'] = 'maestro_process_variables';
  $handler->display->display_options['fields']['variable_value']['field'] = 'variable_value';
  $handler->display->display_options['fields']['variable_value']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['external'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['variable_value']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['variable_value']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['variable_value']['alter']['html'] = 0;
  $handler->display->display_options['fields']['variable_value']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['variable_value']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['variable_value']['hide_empty'] = 0;
  $handler->display->display_options['fields']['variable_value']['empty_zero'] = 0;
  /* Filter criterion: Maestro: Process Variable Name */
  $handler->display->display_options['filters']['variable_name']['id'] = 'variable_name';
  $handler->display->display_options['filters']['variable_name']['table'] = 'maestro_template_variables';
  $handler->display->display_options['filters']['variable_name']['field'] = 'variable_name';
  $handler->display->display_options['filters']['variable_name']['value'] = 'initiator';
  $handler->display->display_options['filters']['variable_name']['exposed'] = TRUE;
  $handler->display->display_options['filters']['variable_name']['expose']['operator_id'] = '';
  $handler->display->display_options['filters']['variable_name']['expose']['label'] = 'Variable Name';
  $handler->display->display_options['filters']['variable_name']['expose']['use_operator'] = FALSE;
  $handler->display->display_options['filters']['variable_name']['expose']['operator'] = 'variable_name_op';
  $handler->display->display_options['filters']['variable_name']['expose']['identifier'] = 'variable_name';
  /* Filter criterion: Maestro: Process Variable Value */
  $handler->display->display_options['filters']['variable_value']['id'] = 'variable_value';
  $handler->display->display_options['filters']['variable_value']['table'] = 'maestro_process_variables';
  $handler->display->display_options['filters']['variable_value']['field'] = 'variable_value';
  $handler->display->display_options['filters']['variable_value']['value'] = '1';
  $handler->display->display_options['filters']['variable_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['variable_value']['expose']['operator_id'] = '';
  $handler->display->display_options['filters']['variable_value']['expose']['label'] = 'Maestro: Process Variable Value';
  $handler->display->display_options['filters']['variable_value']['expose']['use_operator'] = FALSE;
  $handler->display->display_options['filters']['variable_value']['expose']['operator'] = 'variable_value_op';
  $handler->display->display_options['filters']['variable_value']['expose']['identifier'] = 'variable_value';

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'maestro-variable-value-filter';
  $translatables['maestro_variable_value_filter'] = array(
    t('Master'),
    t('maestro variable value filter'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Items per page'),
    t('- All -'),
    t('Offset'),
    t('Process ID'),
    t('Queue Task ID'),
    t('Process Variable Name'),
    t('Process Variable Value'),
    t('Variable Name'),
    t('Maestro: Process Variable Value'),
    t('Page'),
  );



  $views[$view->name] = $view;
  return $views;
}