<?php

  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */

  abstract class MaestroEngine {

    var $_version                 = '';       // Current engine version
    var $_processId               = null;     // Current Process the workflow engine is working on
    var $_templateId              = null;     // Current workflow template id being processed
    var $_queueId                 = null;     // Current Queue record id being processed. This is either null, a single item or a semi colon delimited list
    var $_userId                  = null;     // Current User Id
    var $_taskType                = '';
    var $_debug                   = false;    // Set the current debug level to false.
    var $_userTaskCount           = 0;        // Number of tasks the user has in the queue
    var $_templateCount           = 0;        // Number of templates the user is able to kick off
    var $_processTaskCount        = 0;        // Number of tasks the current process has in the queue

    var $task = null;

    public function executeTask(MaestroTask $task) {
       return $task->execute();
    }

    // Simply sets the debug parameter.
    function setDebug($debug ) {
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
    /*
    function setProcessVariable($variableName, $variableValue=0) {
        global $_TABLES;
        $retval = null;
        $thisvar = strtolower($variableName);
        if($this->_nfProcessId==NULL || $this->_nfProcessId==''){
            if ($this->_debug ) {
                COM_errorLog("set_ProcessVariable: The Process ID has not been set.");
            }
            $retval=NULL;
        }else{
            // setting the value
            $sql  = "SELECT a.id, a.nf_templateVariableID FROM {$_TABLES['nf_processvariables']} a ";
            $sql .= "INNER JOIN {$_TABLES['nf_templatevariables']} b ON a.nf_templateVariableID=b.id ";
            $sql .= "WHERE a.nf_processID='{$this->_nfProcessId}' ";
            $sql .= "AND b.variableName='$thisvar'";
            $result = DB_query($sql );
            if (DB_numRows($result ) > 0 ) {
                list($processVariable_id,$variable_id) = DB_fetchArray($result );
                $sql =  "UPDATE {$_TABLES['nf_processvariables']} set variableValue='$variableValue' WHERE id='$processVariable_id' ";
                $sql .= "AND nf_processID='{$this->_nfProcessId}'";
                $result = DB_Query($sql);
                if ($this->_debug ) {
                    COM_errorLog("set_processVariable -> Process:{$this->_nfProcessId}, variable:$variableName, value:$variableValue");
                }
                if ($result) {
                    $retval = $variableValue;
                }
                //now see if that process variable controlled assignment
                $sql  = "SELECT a.id FROM {$_TABLES['nf_queue']} a LEFT JOIN {$_TABLES['nf_templatedata']} b ON a.nf_templateDataID=b.id ";
                $sql .= "LEFT JOIN {$_TABLES['nf_templateassignment']} c ON a.nf_templateDataID=c.nf_templateDataID ";
                $sql .= "WHERE (a.archived IS NULL OR a.archived=0) AND a.nf_processID={$this->_nfProcessId} AND b.assignedByVariable=1 ";
                $sql .= "AND c.nf_processVariable=$variable_id;";
                $res = DB_query($sql);
                while ($queueRec = DB_fetchArray($res)) {
                    $userAssignmentInfo = array();
                    $userAssignmentInfo[$variable_id] = $variableValue;
                    $this->assign_task($queueRec['id'],$userAssignmentInfo);
                }
            } else {
                if ($this->_debug ) {
                    COM_errorLog("set_processVariable -> Process:{$this->_nfProcessId}, variable:$variableName - DOES NOT EXIST");
                }
            }
        }
        return $retval;
    }
    */


    abstract function setProcessVariable();

    abstract function getVersion();

    abstract function assignTask($queueId,$userObject);

    abstract function getAssignedUID($taskid);

    abstract function completeTask($queueId);

    abstract function archiveTask($queueId);

    abstract function cancelTask($queueId);


    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    abstract function cleanQueue();


}