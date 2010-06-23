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

  function setMessage($msg) {
    $this->_message = $msg;
  }

  function getMessage() {
    return $this->_message;
  }

  function getArchiveStatus(){
    return $this->_archiveStatus;
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


}

class MaestroTaskTypeEnd extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "End" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }


}


class MaestroTaskTypeBatch extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "Batch" - properties: ' . print_r($this->_properties, true);
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
    }else{
      $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
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

}


class MaestroTaskTypeIf extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "IF" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $query = db_select('maestro_template_data', 'a');
    $query->leftJoin('maestro_template_variables','b','a.argument_variable=b.id');
    $query->fields('a',array('if_value', 'if_argument_process', 'operator'));
    $query->fields('b',array('variable_name'));
    $query->addField('b','id','variableID');
    $query->condition("a.id",$this->_properties->template_data_id,"=");
    $res = $query->execute();

    $nextTaskRows=$query->countQuery()->execute()->fetchField();
    if ($nextTaskRows > 0 ) {  //this will/should equal 1.  We only use the first instance of the result anyways.
      $row=$res->fetchObject();
      $templateVariableID = $row->variableid;
      $operator = $row->operator;
      $ifValue = $row->if_value;
      $ifArgumentProcess = $row->if_argument_process;
      if ($templateVariableID == null or $templateVariableID == '' ) { // logical entry it is
        //this is a logical entry.  that is, not using a variable.. need to see what the last task's status is.
        $query = db_select('maestro_queue_from', 'a');
        $query->join('maestro_queue','b','a.from_queue_id=b.id');
        $query->fields('b',array('status'));
        $query->condition("a.queue_id",$this->_properties->id,"=");
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
        $query->condition("a.process_id",$this->_properties->process_id,"=");
        $ifRes = $query->execute();

        $ifQueryNumRows=$query->countQuery()->execute()->fetchField();
        if ($ifQueryNumRows > 0 ) {
          // should have a variable Value here.
          $ifArray = $ifRes->fetchObject;
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
        $this->_archiveStatus=$statusToInsert;
        //now, at this point we need to set the archive task status of this task to the $statusToinsert variable.
        //so that the nextstep method of the engine can properly save it

    }//end if/else for using variable or not in the IF

  }//end if($nexTaskRows>0)

    $this->executionStatus = TRUE;
    return $this;
  }

}

class MaestroTaskTypeInteractivefunction extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "MaestroTaskTypeInteractivefunction" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }

}



class MaestroTaskTypeSetProcessVariable extends MaestroTask {

  function execute() {

    $this->executionStatus = FALSE;
    $msg = 'Execute Task Type: "SetProcessVariable" - properties: ' . print_r($this->_properties, true);
    watchdog('maestro',$msg);

    $query = db_select('maestro_template_data', 'a');
    $query->fields('a',array('form_id','field_id','var_value','inc_value','var_to_set'));
    $query->condition('a.id', $this->_properties->template_data_id,'=');
    $taskDefinitionRec = $query->execute()->fetchObject();
    if ($taskDefinitionRec AND $taskDefinitionRec->var_to_set > 0) {   // Needs to be valid variable to set
      if ($taskDefinitionRec->var_value != '') {  // Set by input
        $count = db_update('maestro_process_variables')
        ->fields(array('variable_value' => intval($taskDefinitionRec->var_value)) )
        ->condition('process_id', $this->_properties->process_id, '=')
        ->condition('template_variable_id',$taskDefinitionRec->var_to_set,'=')
        ->execute();
        if ($count == 1)  $this->executionStatus = TRUE;
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
        $count = db_update('maestro_process_variables')
        ->fields(array('variable_value' => $setvalue))
        ->condition('process_id', $this->_properties->process_id, '=')
        ->condition('template_variable_id',$taskDefinitionRec->var_to_set,'=')
        ->execute();
        if ($count == 1)  $this->executionStatus = TRUE;
      }

    }
    return $this;
  }
}
