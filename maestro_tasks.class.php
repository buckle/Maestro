<?php

class MaestroTaskStatusCodes {
  CONST STATUS_READY = 0;
  CONST STATUS_COMPLETE = 1;
  CONST STATUS_ON_HOLD = 2;
  CONST STATUS_ABORTED = 3;
  CONST STATUS_IF_CONDITION_FALSE = 4;
}

abstract class MaestroTask {
  protected $_properties = NULL;
  protected $_message = NULL;
  protected $_archiveStatus = 0;
  public $executionStatus = NULL;

  function __construct($properties) {
    $this->_properties = $properties;
  }

  abstract function execute ();

  /* prepareTask: Opportunity to set task specific data that will be used to create the queue record
     Specifically, the task handler and task_data fields - which is a serialized array of task specific options/data
     @retval:  associative array (handler => varchar, task_data => serialized array)
   */
  abstract function prepareTask ();

  function showInteractiveTask() {
    return FALSE;
  }

  function getTaskConsoleURL(){
    return "#";
  }

  function setMessage($msg) {
    $this->_message = $msg;
  }

  function getMessage() {
    return $this->_message;
  }

  function getArchiveStatus(){
    return $this->_archiveStatus;
  }

  function saveTempData($data) {
    if ($this->_properties->queue_id > 0) {
      db_update('maestro_queue')
        ->fields(array('temp_data' => serialize($data)))
        ->condition('id', $this->_properties->queue_id, '=')
        ->execute();
    }
  }

  function getTempData() {
    if ($this->_properties->queue_id > 0) {
        $data = db_query("SELECT temp_data FROM {maestro_queue} WHERE id = :tid",
          array(':tid' => $this->_properties->queue_id))->fetchField();
        $retval = unserialize($data);
        return $retval;
    }
  }

}


// Classes can be in their own file or library and included via several options

class MaestroTaskTypeStart extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "Start" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }

  function prepareTask() {}


}

class MaestroTaskTypeEnd extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "End" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }

  function prepareTask() {}

}


class MaestroTaskTypeBatch extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "Batch" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $success = FALSE;

    $current_path=variable_get('maestro_batch_script_location',drupal_get_path('module','maestro') . "/batch/");

    if (file_exists($current_path . $this->_properties->handler)) {
      require($current_path . $this->_properties->handler );
    } elseif (file_exists($this->_properties->handler)) {  // Check in current directory
      require($this->_properties->handler);
    }
    //Assumption made here that the $success variable is set by the batch task.
    if ($success) {
      $this->executionStatus = TRUE;
    }
    else {
      $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
      array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => $taskdata['handler'],'serialized_data' => $serializedData);
  }
}

class MaestroTaskTypeBatchFunction extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "BatchFunction" - properties: ' . print_r($this->_properties, true);
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
    }
    else {
      $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
      array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => $taskdata['function'],'serialized_data' => $serializedData);
  }

}


class MaestroTaskTypeAnd extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "AND" - properties: ' . print_r($this->_properties, true);
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

  function prepareTask() {}

}


class MaestroTaskTypeIf extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "IF" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');

    $serializedData = db_query("SELECT task_data FROM {maestro_queue} WHERE id = :tid",
      array(':tid' => $this->_properties->id))->fetchField();
    $taskdata = @unserialize($serializedData);

    $templateVariableID = $taskdata['if_argument_variable'];
    $operator = $taskdata['if_operator'];
    $ifValue = $taskdata['if_value'];
    $ifArgumentProcess = $taskdata['if_process_arguments'];

    if ($templateVariableID == null or $templateVariableID == '' ) { // logical entry it is
      //this is a logical entry.  that is, not using a variable.. need to see what the last task's status is.
      $query = db_select('maestro_queue_from', 'a');
      $query->join('maestro_queue','b','a.from_queue_id=b.id');
      $query->fields('b', array('status'));
      $query->condition("a.queue_id", $this->_properties->id,"=");
      $res = $query->execute();
      $row=$res->fetchObject();
      $lastStatus = intval($row->status);
      $whichBranch = null;
      switch (strtolower($ifArgumentProcess) ) {
        case 'lasttasksuccess':
          if ($lastStatus == 0 or $lastStatus == 1) {
            $whichBranch = 1;
          }
          else {
            $whichBranch = 0;
          }
          break;
        case 'lasttaskcancel':
          if ($lastStatus == 3) {
            $whichBranch = 1;
          }
          else {
            $whichBranch = 0;
          }
          break;
        case 'lasttaskhold':
          if ($lastStatus == 2) {
            $whichBranch = 1;
          }
          else {
            $whichBranch = 0;
          }
          break;
        case 'lasttaskaborted':
          if ($lastStatus == 3) {
            $whichBranch = 1;
          }
          else {
            $whichBranch = 0;
          }
          break;
      } //end switch
    }
    else {// variableID it is
      //we're using a variable here.

      // need to perform a variable to value operation based on the selected operation!
      // $templateVariableID ,$operator ,$ifValue, $processID
      // need to select the process variable using the ID from the current process

      $query = db_select('maestro_process_variables', 'a');
      $query->fields('a',array('variable_value'));
      $query->condition(db_and()->condition("a.process_id",$this->_properties->process_id)->condition('a.template_variable_id',$templateVariableID));
      $ifRes = $query->execute();
      $ifQueryNumRows = $query->countQuery()->execute()->fetchField();
      if ($ifQueryNumRows > 0 ) {
        // should have a variable Value here.
        $ifArray = $ifRes->fetchObject();
        $variableValue = $ifArray->variable_value;
        switch ($operator ) {
          case '=':
            if ($variableValue == $ifValue ) {
              $whichBranch = 1;
            } else {
              $whichBranch = 0;
            }
            break;
          case '<':
            if ($variableValue < $ifValue ) {
              $whichBranch = 1;
            } else {
              $whichBranch = 0;
            }
            break;
          case '>':
            if ($variableValue > $ifValue ) {
              $whichBranch = 1;
            } else {
              $whichBranch = 0;
            }
            break;
          case '!=':
            if ($variableValue != $ifValue ) {
              $whichBranch = 1;
            } else {
              $whichBranch = 0;
            }

            break;
        } //end switch($operator)
      } //end if$ifQueryNumRows>0)
      else { // force the branch to the false side since the variable dosent exist...
        // can't be true if it dosent exist!!!
        $whichBranch = 0;
      }

      if ($whichBranch == 1 ) {
        // point to the true branch
        $statusToinsert = MaestroTaskStatusCodes::STATUS_COMPLETE;
      }
      else {
        // point to the false branch
        $statusToinsert = MaestroTaskStatusCodes::STATUS_IF_CONDITION_FALSE;
      }
      $this->_archiveStatus=$statusToinsert;
      //now, at this point we need to set the archive task status of this task to the $statusToinsert variable.
      //so that the nextstep method of the engine can properly save it

    }//end if/else for using variable or not in the IF


    $this->executionStatus = TRUE;
    return $this;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
    array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => '' ,'serialized_data' => $serializedData);
  }


}

class MaestroTaskTypeInteractivefunction extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "MaestroTaskTypeInteractivefunction" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $serializedData = db_query("SELECT task_data FROM {maestro_queue} WHERE id = :tid",
      array(':tid' => $this->_properties->id))->fetchField();
    $taskdata = @unserialize($serializedData);
    $this->executionStatus = FALSE;
    return $this;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
      array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => $taskdata['handler'],'serialized_data' => $serializedData);
  }

  function showInteractiveTask() {
    /* Place our custom interactive functions in this file for now but we need a far more automatic method */
    include_once './' . drupal_get_path('module', 'maestro') . '/custom_functions/myfunctions.php';

    $retval = '';
    $serializedData = db_query("SELECT task_data FROM {maestro_queue} WHERE id = :id",
    array(':id' => $this->_properties->queue_id))->fetchField();
    $taskdata = @unserialize($serializedData);
    if (function_exists($taskdata['handler'])) {
      $ret = $taskdata['handler']('display',$this,$taskdata['optional_parm']);
      if ($ret->retcode === TRUE) {
        $retval = $ret->html;
      }
    } else {
      $retval = '<div style="text-align:center;margin:5px;padding:10px;border:1px solid #CCC;font-size:14pt;">';
      $retval .= t('Interactive Function "@taskname" was  not found.',array('@taskname' => $taskdata['handler']));
      $retval .= '</div>';
    }
    return $retval;
  }

  function processInteractiveTask($taskid,$taskop) {
    $ret = new stdClass();
    $ret->retcode = FALSE;
    $ret->engineop = '';
    /* Place our custom interactive functions in this file for now but we need a far more automatic method */
    include_once './' . drupal_get_path('module', 'maestro') . '/custom_functions/myfunctions.php';
    $serializedData = db_query("SELECT task_data FROM {maestro_queue} WHERE id = :id",
    array(':id' => $taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    if (function_exists($taskdata['handler'])) {
      $ret = $taskdata['handler']($taskop,$this,$taskdata['optional_parm']);
    }
    return $ret;
  }

}



class MaestroTaskTypeSetProcessVariable extends MaestroTask {

  function execute() {

    $this->executionStatus = FALSE;
    $msg = 'Execute Task Type: "SetProcessVariable" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);

    $query = db_select('maestro_template_data', 'a');
    $query->fields('a',array('task_data'));
    $query->condition('a.id', $this->_properties->template_data_id,'=');
    $taskDefinitionRec = $query->execute()->fetchObject();
    if ($taskDefinitionRec) {   // Needs to be valid variable to set
      $taskDefinitionRec->task_data = unserialize($taskDefinitionRec->task_data);
      if ($taskDefinitionRec->task_data['var_to_set'] > 0) {
        if ($taskDefinitionRec->task_data['var_value'] != '') {  // Set by input
          $count = db_update('maestro_process_variables')
          ->fields(array('variable_value' => intval($taskDefinitionRec->task_data['var_value'])) )
          ->condition('process_id', $this->_properties->process_id, '=')
          ->condition('template_variable_id',$taskDefinitionRec->task_data['var_to_set'],'=')
          ->execute();
          if ($count == 1)  $this->executionStatus = TRUE;
        }
        else if ($taskDefinitionRec->task_data['inc_value'] != 0) {  // Set by increment
          $query = db_select('maestro_process_variables', 'a');
          $query->addField('a','variable_value');
          $query->condition('a.process_id', $this->_properties->process_id,'=');
          $query->condition('a.template_variable_id', $taskDefinitionRec->task_data['var_to_set'],'=');
          $curvalue = intval($query->execute()->fetchField());
          $setvalue = $curvalue + intval($taskDefinitionRec->task_data['inc_value']);
          $count = db_update('maestro_process_variables')
          ->fields(array('variable_value' => $setvalue))
          ->condition('process_id', $this->_properties->process_id, '=')
          ->condition('template_variable_id',$taskDefinitionRec->task_data['var_to_set'],'=')
          ->execute();
          if ($count == 1)  $this->executionStatus = TRUE;
        }
      }
    }
    return $this;
  }

  function prepareTask() {}

}


class MaestroTaskTypeManualWeb extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "Manual Web" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $success = FALSE;

    //Assumption made here that the $success variable is set by the batch task.
    if ($success) {
      $this->executionStatus = TRUE;
    }
    else {
      $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
  }

  function getTaskConsoleURL(){
    $prop=unserialize($this->_properties->task_data);
    $url = $prop['handler'];
    if(strpos($url, "?")) {
      $url .= "&queueid=" . $this->_properties->queue_id;
    }
    else {
      $url .= "?queueid=" . $this->_properties->queue_id;
    }
    return $url;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
      array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => $taskdata['handler'],'serialized_data' => $serializedData);
  }
}