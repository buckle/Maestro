<?php

  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */

  abstract class MaestroEngine {
//@TODO: Need to convert these to the proper public/protected variables.

    var $_version                 = '';       // Current engine version
    var $_processId               = NULL;     // Current Process the workflow engine is working on
    var $_templateId              = NULL;     // Current workflow template id being processed
    var $_queueId                 = NULL;     // Current Queue record id being processed. This is either null, a single item or a semi colon delimited list
    var $_userId                  = NULL;     // Current User Id
    var $_trackingId              = NULL;     // Workflow grouping Tracking Id to enable project or detail workflow tracking and link related workflows
    var $_taskType                = '';
    var $_debug                   = false;    // Set the current debug level to false.
    var $_userTaskCount           = 0;        // Number of tasks the user has in the queue
    var $_userTaskObject          = NULL;     // Users Active Tasks in the queue
    var $_templateCount           = 0;        // Number of templates the user is able to kick off
    var $_processTaskCount        = 0;        // Number of tasks the current process has in the queue
    var $_status                  = 0;        // Set in cleanQueue to indicate status of last executing task before calling nextStep method
    var $_lastTestStatus          = 0;        // Used in nextStep when the task that last executed will branch to different tasks - like an IF task
    var $task = null;

    // Simply sets the debug parameter.
    function setDebug($debug) {
        if ($debug) {
            watchdog('maestro',"Set debug mode on");
        }
        $this->_debug = $debug;
    }

    public function setProcessId($id) {
      if (intval($id) > 0) {
        $this->_processId = $id;
      }
    }

    function setTrackingId($id) {
      if (intval($id) > 0) {
        $this->_trackingId = $id;
      }
    }

    function getTrackingId() {
      return $this->_trackingId;
    }

    public function getUserTaskCount() {
      return $this->_userTaskCount;
    }

    public function executeTask(MaestroTask $task) {
       return $task->execute();
    }

    public function prepareTask(MaestroTask $task) {
       return $task->prepareTask();
    }

    public function showInteractiveTask(MaestroTask $task,$taskid) {
      /* Common HTML container with an ID set that we will hook onto to show/hide.
       * This lets developer not have to worry about returning a table row with 5 columns
       */
      $prehtmlfragment = '<tr class="maestro_taskconsole_interactivetaskcontent" id="maestro_actionrec' . $taskid . '" style="display:none;"><td colspan="5">';
      $posthtmlfragment = '</td></tr>';
      $retval = $task->showInteractiveTask();
      if ($retval === FALSE) {
      	return '';
      }
      else if (empty($retval)) {
        return $prehtmlfragment . t('empty interactive task - nothing to display for interactive function.') . $posthtmlfragment;
      }
      else {
        return $prehtmlfragment . $retval . $posthtmlfragment;
      }
    }


    // Get a process variable as defined for this template
    // Requires the processID to be set and then pass in a variable's name.
    // if both the process and the name exist, you get a value..
    // otherwise, you get NULL
    function getProcessVariable($variable) {
        $retval = NULL;
        $thisvar = strtolower($variable);
        if(empty($this->_processId)) {
            if ($this->_debug ) {
                watchdog('maestro',"get_ProcessVariable: The Process ID has not been set.");
            }
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
            $retval = $record->variable_value;
            if ($this->_debug ) {
              watchdog('maestro',"get_ProcessVariable: $variable -> $retval");
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
        $retval = NULL;
        $thisvar = strtolower($variableName);
           if(empty($this->_processId)) {
            if ($this->_debug ) {
                watchdog('maestro',"get_ProcessVariable: The Process ID has not been set.");
            }
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

    abstract function completeTask($queueId,$status = 1);

    abstract function archiveTask($queueId);

    abstract function cancelTask($queueId);

    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    abstract function cleanQueue();


}
