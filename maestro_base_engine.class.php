<?php

  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */

  abstract class MaestroEngine {
//@TODO: Need to convert these to the proper public/protected variables.

    var $_version                 = '';       // Current engine version
    var $_processId               = null;     // Current Process the workflow engine is working on
    var $_templateId              = null;     // Current workflow template id being processed
    var $_queueId                 = null;     // Current Queue record id being processed. This is either null, a single item or a semi colon delimited list
    var $_userId                  = null;     // Current User Id
    var $_taskType                = '';
    var $_debug                   = false;    // Set the current debug level to false.
    var $_userTaskCount           = 0;        // Number of tasks the user has in the queue
    var $_userTaskList            = NULL;     // Users Active Tasks in the queue
    var $_templateCount           = 0;        // Number of templates the user is able to kick off
    var $_processTaskCount        = 0;        // Number of tasks the current process has in the queue
    var $_archiveStatus           = 0;        // Set by the executing task to signify what status the archive routine should set.
    var $task = null;

    public function executeTask(MaestroTask $task) {
       return $task->execute();
    }

    public function prepareTask(MaestroTask $task) {
       return $task->prepareTask();
    }

    // Simply sets the debug parameter.
    function setDebug($debug) {
        if ($debug) {
            watchdog('maestro',"Set debug mode on");
        }
        $this->_debug = $debug;
    }


    // Get a process variable as defined for this template
    // Requires the processID to be set and then pass in a variable's name.
    // if both the process and the name exist, you get a value..
    // otherwise, you get NULL
    function getProcessVariable($variable) {
        $retval = null;
        $thisvar = strtolower($variable);
        if(empty($this->_processId)) {
            if ($this->_debug ) {
                watchdog('maestro',"get_ProcessVariable: The Process ID has not been set.");
            }
            $retval=NULL;
        }
        else {
          $query = db_select('maestro_process_variables', 'a');
          $query->addField('a','variable_value');
          $query->join('maestro_template_variables', 'b', 'a.template_variable_id = b.id');
          $query->condition('a.process_id',$this->_processId,'=');
          $query->condition('b.variable_name',$thisvar,'=');
          $result = $query->execute();
          $numrows = $query->countQuery()->execute()->fetchField();
          if ($numrows > 0 ) {
            $record = $result->fetchObject();
            if ($this->_debug ) {
              watchdog('maestro',"get_ProcessVariable: $variable -> {$record->variable_value}");
            }
          }
          else {
            if ($this->_debug ) {
              watchdog('maestro',"get_processVariable -> Process:{$this->_processId}, variable:$variable - DOES NOT EXIST");
            }
          }
        }
        return $retval;
    }


    // Set a process variable as defined for this template
    // Requires the processID to be set and then pass in a variable's name and value
    // if both the process and the name exist, you get a value..
    // otherwise, you get NULL
    function setProcessVariable($variableName, $variableValue=0) {
        $retval = null;
        $thisvar = strtolower($variableName);
           if(empty($this->_processId)) {
            if ($this->_debug ) {
                watchdog('maestro',"get_ProcessVariable: The Process ID has not been set.");
            }
            $retval = NULL;
        }
        else {
          // setting the value
          $query = db_select('maestro_process_variables', 'a');
          $query->addField('a','id','process_variable_id');
          $query->addField('a','template_variable_id','variable_id');
          $query->join('maestro_template_variables', 'b', 'a.template_variable_id = b.id');
          $query->condition('a.process_id',$this->_processId,'=');
          $query->condition('b.variable_name',$thisvar,'=');
          $result = $query->execute();
          $numrows = $query->countQuery()->execute()->fetchField();
          if ($numrows > 0 ) {
            $processVariableRecord = $result->fetchObject();
            $count = db_update('maestro_process_variables')
              ->fields(array('variable_value' => $variableValue))
              ->condition('id', $processVariableRecord->process_variable_id, '=')
              ->condition('process_id',$this->_processId,'=')
              ->execute();
                if ($this->_debug ) {
                    watchdog('maestro',"set_processVariable -> Process:{$this->_processId}, variable:$thisvar, value:$variableValue");
                }
                if ($count == 1) {
                    $retval = $variableValue;
                }
                // Now see if that process variable controlled assignment
                $query = db_select('maestro_queue', 'a');
                $query->fields('a',array('id'));
                $query->join('maestro_template_data', 'b', 'b.id = a.template_data_id');
                $query->join('maestro_template_assignment', 'c', 'c.template_data_id = a.template_data_id');
                $query->condition('a.process_id',$this->_processId,'=');
                $query->condition('b.assigned_by_variable',1,'=');
                $query->condition(db_or()->condition('a.archived',0)->condition('a.archived',NULL));
                $query->condition('c.process_variable',$processVariableRecord->variable_id,'=');
                $queueRecords = $query->execute();
                foreach ($queueRecords as $queueRecord) {
                    $this->assignTask($queueRecord->id,array($processVariableRecord->variable_id => $variableValue));
                }

            }
            else {
                if ($this->_debug ) {
                    watchdog('maestro',"set_processVariable -> Process:{$this->_processId}, variable:$thisvar - DOES NOT EXIST");
                }
            }
        }
        return $retval;
    }

    abstract function getVersion();

    abstract function assignTask($queueId,$userObject);

    abstract function getAssignedUID();

    abstract function completeTask($queueId);

    abstract function archiveTask($queueId);

    abstract function cancelTask($queueId);


    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    abstract function cleanQueue();


}
