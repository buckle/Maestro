<?php

abstract class MaestroTask {
  protected $_properties = NULL;
  protected $_message = NULL;
  public $executionStatus = NULL;

  function __construct($properties) {
    $this->_properties = $properties;

  }

  abstract function execute ();

  function setMessage($msg) {
    $this->_message = $msg;
  }

  function getMessage() {
    return $this->_message;
  }

}


// Classes can be in their own file or library and included via several options

class MaestroTaskTypeStart extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "Start" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }


}

class MaestroTaskTypeEnd extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "End" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }


}


class MaestroTaskTypeBatch extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "Batch" - properties: ';
    watchdog('maestro',$msg);
    $success = FALSE;

    $current_path = drupal_get_path('module','maestro') . "/batch/";

    if (file_exists($current_path . $this->_properties->handler)) {
      require($current_path . $this->_properties->handler );
    } elseif (file_exists($this->_properties->handler)) {  // Check in current directory
      require($this->_properties->handler);
    }
    //Assumption made here that the $success variable is set by the batch task.
    if ($success) {
      $this->executionStatus = TRUE;
    }else{
      $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
  }
}

class MaestroTaskTypeBatchFunction extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "BatchFunction" - properties: ';
    watchdog('maestro',$msg);
    $success = FALSE;
    $current_path = drupal_get_path('module','maestro') . "/batch/";
    include($current_path . "batch_functions.php" );

    if (function_exists($this->_properties->handler)) {
      $this->_properties->handler($this->_properties->id,$this->_properties->process_id);
    }
    //Assumption made here that the $success variable is set by the batch task.
    if ($success) {
      $this->executionStatus = TRUE;
    }else{
      $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
  }
}


class MaestroTaskTypeAnd extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "AND" - properties: ';
    watchdog('maestro',$msg);

    $numComplete = 0;
    $numIncomplete = 0;

    $query = db_select('maestro_queue', 'a');
    $query->join('maestro_template_data_next_step', 'b', 'a.template_data_id = b.template_data_to OR a.template_data_id=b.template_data_to_false');
    $query->addExpression('COUNT(a.id)','templateCount');
    $query->condition("a.id",$this->_properties->id,"=");
    $numComplete = $query->execute()->fetchObject();

    $query = db_select('maestro_queue_from', 'a');
    $query->join('maestro_queue', 'b', 'a.from_queue_id = b.id');
    $query->addExpression('COUNT(a.id)','processCount');
    $query->condition(db_and()->condition("a.queue_id",$this->_properties->id,"=")->condition("b.process_id",$this->_properties->process_id,"="));
    $numIncomplete = $query->execute()->fetchObject();

    // sounds confusing, but if the processCount is greater than the completed ones, we're ok too
    if ($numIncomplete->processCount == $numComplete->templateCount || $numIncomplete->processCount > $numComplete->templateCount ) {
        // we have all of the incoming items done for this AND
        // we can now carry out updating this queue item's information
        $this->executionStatus = TRUE;
    } else {
        // not all the incomings for the AND are done
        // just here for troubleshooting purposes
        $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
  }

}


class MaestroTaskTypeIf extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "IF" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }

}

class MaestroTaskTypeInteractivefunction extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "MaestroTaskTypeInteractivefunction" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }

}



class MaestroTaskTypeSetProcessVariable extends MaestroTask {

  function execute() {
    $query = db_select('maestro_template_data', 'a');
    $query->fields('a',array('form_id','field_id','var_value','inc_value','var_to_set'));
    $query->condition('a.id', $this->_properties->template_data_id,'=');
    $taskDefinitionRec = $query->execute()->fetchObject();

    if ($taskDefinitionRec AND $taskDefinitionRec->var_to_set > 0) {   // Needs to be valid variable to set
      if ($taskDefinitionRec->var_value != '') {  // Set by input
        db_update('maestro_process_variables')
          ->fields(array('variable_value' => intval($taskDefinitionRec->var_value)) )
          ->condition('process_id', $this->_properties->process_id, '=')
          ->condition('template_variable_id',$taskDefinitionRec->var_to_set,'=')
          ->execute();
        }
        else if ($taskDefinitionRec->form_id > 0 && $taskDefinitionRec->field_id > 0) {  //set by form result
          // Have to find the form result, first need to get the project id
          /* @TODO: Need to complete logic to set process variable from a web form field id */
          watchdog('maestro',"Incomplete Code - MaestroTaskTypeSetProcessVariable via form field not complete");
        }
        else if ($taskDefinitionRec->inc_value != 0) {  // Set by increment
          $query = db_select('maestro_process_variables', 'a');
          $query->addField('a','variable_value');
          $query->condition('a.process_id', $this->_properties->process_id,'=');
          $query->condition('a.template_variable_id', $taskDefinitionRec->var_to_set,'=');
          $curvalue = intval($query->execute()->fetchField());
          $setvalue = $curvalue + intval($taskDefinitionRec->var_value);
          db_update('maestro_process_variables')
            ->fields(array('variable_value' => $setvalue))
            ->condition('process_id', $this->_properties->process_id, '=')
            ->condition('template_variable_id',$taskDefinitionRec->var_to_set,'=')
            ->execute();
        }
    }
  }
}
