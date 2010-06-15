<?php

  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */

  class MaestroEngineVersion1 extends MaestroEngine {

      var $_version = '1.x';
      var $_properties;

      function __construct($options) {
        //echo "<br>Version 1 __constructor";
        print_r($options);
        $this->_properties = $options;
      }


      public function getVersion() {
        return $this->_version;
      }

    /* Generate a new process for a workflow template
     * @param $template:
     *   The workflow template id (int) - Mandatory
     *
     * @param $startoffset
     *   Optional paramater to launch the workflow process at other then the default task step 0.
     *   Also used if the process regeneration will not be at task 0 (automatically handled by engine)
     *
     * @param $pid
     *   Optional paramater Parent Process id. This is used when regenerating a process or
     *   if this new process should be a child process (or associated) with another workflow grouping (project)
     *
     * @param $application_association
     *   Optional BOOLEAN value (default FALSE) that if TRUE triggers the process related records to be grouped (related)
     *   as part of a project or related workflow grouping.
     *
     * @return
     *   The process id
     */

    function newProcess($template, $startoffset = null, $pid = null , $application_association = FALSE) {
      //debugbreak();
        global $user;
        // Execute the SQL statement to retrieve the first step of the process and kick it off
        if ($startoffset == null ) {

            /* Original nexflow query - believe we can remove the 2 LEFT OUTER JOINS */

            // $sql = "SELECT a.nf_templateDataFrom, b.regenAllLiveTasks, c.useProject, c.templateName FROM {$_TABLES["nf_templatedatanextstep"]} a ";
            // $sql .= "inner join {$_TABLES["nf_templatedata"]} b on a.nf_templateDataFrom = b.id ";
            // $sql .= "inner join {$_TABLES["nf_template"]} c on b.nf_templateid = c.id ";
            // $sql .= "left outer join {$_TABLES["nf_templateassignment"]} d on d.nf_templateDataID = b.id ";
            // $sql .= "left outer join {$_TABLES["nf_handlers"]} e on e.id = b.nf_handlerid ";
            // $sql .= "WHERE b.firstTask = 1 AND c.id ='$template' ORDER BY nf_templateDataFrom ASC LIMIT 1 ";

            $query = db_select('maestro_template_data_next_step', 'a');
            $query->fields('a',array('template_data_from'));
            $query->fields('b',array('regen_all_live_tasks','reminder_interval','task_class_name'));
            $query->addField('b','id','template_data_id');
            $query->addField('c','use_project','template_name');
            $query->join('maestro_template_data', 'b', 'a.template_data_from = b.id');     // default is an INNER JOIN
            $query->join('maestro_template', 'c', 'b.template_id = c.id');
            $query->condition('b.first_task',1,'=');
            $query->condition('c.id',$template,'=');
            $query->orderBy('template_data_from','ASC');
            $query->range(0,1);

        } else {
            // Retrieve the one queue record - where it is equal to the passed in start offset.
            $startoffset = int($startoffset);
            $query = db_select('maestro_template_data','a');
            $query->addField('a','id','template_data_id');
            $query->addField('b','template_name');
            $query->fields('a',array('regen_all_live_tasks','reminder_interval','task_class_name'));
            $query->join('maestro_template', 'b', 'b.template_id = a.id');
            $query->condition('a.id',$startoffset);
        }
        if ($this->_debug ) {
            watchdog('maestro','New process code executing');
        }

        // Only 1 record expected - query returns an array of object records
        $templaterec = current($query->execute()->fetchAll());

        if (!empty($templaterec->template_data_id)) {
            $pid = intval($pid);
            if ($pid > 0) {
                $custom_flowname = db_query("SELECT custom_flow_name FROM {maestro_process} WHERE id=$pid")->fetchField();
            }
            else {
              $custom_flowname = '';
            }

            $process_record = new stdClass();
            $process_record->template_id = $template;
            $process_record->custom_flow_name = $custom_flowname;
            $process_record->complete = 0;
            $process_record->pid = $pid;
            $process_record->initiated_date = date('Y-m-d H:i:s' );
            drupal_write_record('maestro_process',$process_record);
            $new_processid = $process_record->id;

            if ($templaterec->reminder_interval > 0) {
                $next_reminder_date->reminder_interval = time() + $templaterec->reminder_interval;
            }
            else {
              $next_reminder_date = 0;
            }

            $queue_record = new stdClass();
            $queue_record->process_id = $new_processid;
            $queue_record->template_data_id = $templaterec->template_data_id;
            $queue_record->task_class_name = $templaterec->task_class_name;
            $queue_record->status = 0;
            $queue_record->archived = 0;
            $queue_record->engine_version = $this->_version;
            $queue_record->created_date = date('Y-m-d H:i:s' );
            $queue_record->next_reminder_date = $next_reminder_date;
            drupal_write_record('maestro_queue',$queue_record);
            $new_taskid = $queue_record->id;

            // Check if notification has been defined for new task assignment
            $this->sendTaskAssignmentNotifications();

            // Determine if the offset is set.. if so, pack the original pid pointer with a status of 2
            if (!empty($startoffset) AND !empty($pid)) {
                $record = new stdClass();
                $record->id = $pid;
                $record->complete = 2;
                $record->completed_date = time();
                drupal_write_record('maestro_process',$record);

                // Within this section we need to detect whether or not the startoffset task has the "regenerate all live tasks" option set.
                // if so, the process we just layed to rest will hold some in-production tasks, and those tasks will have their pids set to the new pid.
                if($templaterec->regen_all_live_tasks == 1) {
                  $q2 = db_select('maestro_queue','a');
                  $query->addField('a','id','id');
                  $q2->join('maestro_template_data', 'b', 'a.template_data_id = b.id');
                  $q2->condition('b.task_class_name','MaestroTaskTypeAnd');
                  $q2->condition('a.process_id',$pid);
                  $q2->condition(db_or()->condition('a.archived',0)->condition('a.archived',NULL));
                  $active_queue_tasks_result = $q2->execute();
                  foreach ($active_queue_tasks_result as $active_queue_record) {
                    /* The maestro_queue_from table is used to simplify later reporting of active tasks
                     * @TODO: Review if this table is really being used or is necessary
                     */
                    $q3 = db_select('maesto_queue_from','a');
                    $q3->addField('a','from_queue_id');
                    $q3->condition("a.queue_id = {$active_queue_record->id}");
                    $queue_reporting_result = $q3->execute();
                    foreach ($queue_reporting_result as $queue_reporting_record) {
                      $record = new stdClass();
                      $record->id = $queue_reporting_record->from_queue_id;
                      $record->process_id = $new_processid;
                      drupal_write_record('maestro_queue',$record);
                    }
                    db_update('maestro_queue')
                      ->fields(array('process_id' => $new_processid))
                      ->condition('id', $active_queue_record->id)
                      ->condition(db_or()->condition('archived',0)->condition('archived',NULL))
                      ->execute();
                  }
                }
                // Select the process variables for the parent and create new ones for the new process $new_processid
                $pvquery = db_select('maestro_process_variables','a');
                $pvquery->addExpression($new_processid,'process_id');
                $pvquery->fields('a',array('template_variable_id','variable_value'));
                $pvquery->condition("a.process_id=$pid");
                db_insert('maestro_process_variables')
                  ->fields('process_id','variable_value','template_variable_id')
                  ->from($pvquery)
                  ->execute();


            } else {
                // Situation where this is the root process, inserts the default template variables into the process
                $pvquery = db_select('maestro_template_variables','a');
                $pvquery->addExpression($new_processid,'process_id');
                $pvquery->fields('a',array('id','variable_value'));
                $pvquery->condition('a.template_id',$template,'=');
                db_insert('maestro_process_variables')
                  ->fields(array('process_id','variable_value','template_variable_id'))
                  ->from($pvquery)
                  ->execute();
            }
            $this->_processId = $new_processid;
            if ($this->_debug ) {
                watchdog('maestro',"New queue id (1) : $new_taskid - Template Taskid: {$templaterec->template_data_id}");
            }

            // Set the initiator variable here if not already set - via a regenerated process creation
            if ($this->getProcessVariable('INITIATOR') == 0) {
                $this->setProcessVariable('INITIATOR',$user->uid);
            }

            $newTaskAssignedUsers = $this->getAssignedUID($new_taskid);
            if (is_array($newTaskAssignedUsers) AND count($newTaskAssignedUsers) > 0) {
                $this->assignTask($new_taskid,$newTaskAssignedUsers);
            }

            if($application_association) {
                // Detect whether this new process needs a more detailed project table association created for it.
                if($templaterec->use_project == 1 && empty($pid)){
                    // Condition where there is no parent (totally new process)
                    $project_record = new stdClass();
                    $project_record->process_id = $new_processid;
                    $project_record->originator_uid = $user->uid;
                    $project_record->task_id = $new_taskid;
                    $project_record->status = 0;
                    $project_record->description = $templaterec->template_name;
                    drupal_write_record('maestro_projects',$project_record);
                    $this->set_ProcessVariable('PID',$project_record->id);
                    if ($this->_debug ) {
                        watchdog('maestro',"new process: created new project_id: {$project_record->id}");
                    }
                } elseif($templaterec->use_project && !empty($pid)) {
                    // Condition where there IS a parent AND we want a project table association
                    // One different step here - to update the wf process association for the original PID to the new insertID
                    db_update('maestro_projects')
                      ->fields(array('process_id' => $new_processid))
                      ->condition('process_id', $pid, '=')
                      ->execute();
                    if ($this->_debug ) {
                      watchdog('maestro',"updated existing project record - set process_id to $new_processid");
                    }
                }
            } else {
                // Condition here where we are spawning a new process from an already existing process
                // BUT we are not going to create a new tracking project.  Rather we are going to associate this process with the
                // parent's already established tracking project
                if(!empty($pid)) {
                    // First, pull back the existing projects entry
                    $existing_project_result = db_select('maestro_projects')
                      ->fields('maestro_projects', array('id', 'related_processes'))
                      ->condition('process_id', $pid, '=')
                      ->execute();
                    if($existing_project_result->related_processes != ''){
                        $existing_project_result->related_processes .= ",";
                    }
                    $existing_project_result->related_processes .= $new_processid;
                    if($existing_project_result->id != 0) {
                      db_update('maestro_projects')
                        ->fields(array('related_processes' => $existing_project_result->related_processes))
                        ->condition('id', $existing_project_result->id, '=')
                        ->execute();
                    }
                }

            }

            return $new_processid;

        } else {
            watchdog('maestro', "New Process Code FAIL! - Template: $template not defined");
        }
    }





    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    function cleanQueue() {
      $processTaskList = array("id" => array(), "processid" => array() );
      $processTaskListcount = 0;

      /* Call Observer Hooks to send out any task notifications and reminders */

      $sql = "SELECT distinct a.id, a.status,a.template_data_id, c.template_id, c.step_type, c.handlerid, ";
      $sql .= "c.function, e.template_name, f.handler, b.id AS process_id, h.steptype ";
      $sql .= "FROM {maestro_queue} a inner join {maestro_process} b on  a.process_id = b.id ";
      $sql .= "inner join {maestro_templatedata} c on a.template_data_id = c.id ";
      $sql .= "inner join {maestro_template} e on b.template_id = e.id ";
      $sql .= "inner join {maestro_steptype} h on c.step_type = h.id ";
      $sql .= "left outer join {maestro_handlers} f on c.handler_id = f.id ";
      $sql .= "left outer join {maestro_templateassignment} g on g.template_data_id = c.id ";
      $sql .= "WHERE ((a.status <>0 AND a.status IS NOT NULL and a.status<>2 and (h.id=1 OR h.id=7 OR h.id=8)) ";
      $sql .= "OR ((a.status=0 or a.status=3 or a.status=4) and (h.id=2 or h.id=3 or h.id=4 or h.id=5 or h.id=6 or h.id=9 or h.id=10 or h.id=11)) ) ";
      $sql .= "AND (a.archived <> 1 OR a.archived IS NULL OR a.archived =0 ) and (b.complete=0)";

      $query = db_select('maestro_queue', 'a');
      $query->join('maestro_process', 'b', 'a.process_id = b.id');     // default is an INNER JOIN
      $query->join('maestro_templatedata', 'c', 'a.template_data_id = c.id');
      $query->join('maestro_template', 'd', 'b.template_id = d.id');
      $query->join('maestro_steptype', 'e', 'c.step_type = e.id');
      $query->join('maestro_handlers', 'f', 'c.handler = f.id');

      $query = db_query($sql);
      $numrows = 0;
      while ($queueRecord = db_fetch_object($query)) {
        $numrows++;
        $this->_taskType = strtolower($queueRecord->step_type);
        $this->_processId = $queueRecord->process_id;
        $this->_queueID = $queueRecord->id;
        $handler = $queueRecord->handler;
        $templateName = $queueRecord->template_name;
        $templateDataID = $queueRecord->template_data_id;

        // this switch is used to determine what task type it is.
        // in the event its a manual web task, we'll just go ahead and clean it up..
        // however, in the event that its an AND task, we have to be careful that
        // we check the preceeding queue elements to ensure that they're all done before completing
        // the and task and then also entering the next queue item.
        if ($this->_debug ) {
            watchdog('maestro',"Process: {$this->_processId} , Step Type: $this->_taskType");
        }
        /* @todo: Need to determine what the task properties object looks like */
        $taskProperties = $queueRecord;

        $taskClassName = 'MaestroTaskType' . ucfirst($this->_taskType);
        $ret = $this->executeTask(new task($taskClassName,$taskProperties));
        if ($ret === FALSE) {
          watchdog('maestro',"Failed Task: {$this->_queueId}, Process: {$this->_processId} , Step Type: $this->_taskType");
        }
      }

      if ($numrows == 0 AND $this->_debug) {
        watchdog('maestro','cleanQueue - 0 rows returned.  Nothing in queue.');
      }

    }


    function assignTask($queueId,$userObject) {

    }

    function getAssignedUID($taskid) {}
    
    function sendTaskAssignmentNotifications () { }

    function completeTask($queueId) {}

    function archiveTask($queueId) {}

    function cancelTask($queueId) {}


    // Get a process variable as defined for this template
    // Requires the processID to be set and then pass in a variable's name.
    // if both the process and the name exist, you get a value..
    // otherwise, you get NULL
    function getProcessVariable($variable) {}


    function setProcessVariable($variable,$value) {}
    
    
    
    function nextStep($queueID, $processID ) {
        global $_TABLES;
        $queueID = NXCOM_filterInt($queueID);
        $processID = NXCOM_filterInt($processID);
        if ($this->_debug ) {
            COM_errorLog("_nfNextStep: Queueid: $queueID, Processid: $processID");
        }
        // using the queueid and the processid, we are able to create or generate the
        // next step or the regenerated next step in a new process
        $thisDate = date('Y-m-d H:i:s' );
        $sql  = "SELECT  c.nf_templateDataTo FROM {$_TABLES['nf_queue']} a, {$_TABLES['nf_templatedatanextstep']} c ";
        $sql .= "WHERE a.nf_templateDataid=c.nf_templateDataFrom AND a.nf_processID='$processID' AND a.id='$queueID'";
        $nextTaskResult = DB_query($sql );
        $nextTaskRows = DB_numRows($nextTaskResult );

        if ($nextTaskRows == 0 ) {
            // echo "no rows! qid:" . $queueID . " procid:" . $processID . "<HR>";
            // if there are no rows for this specific QueueID and nothing for this processID, there's no next task
            $this->archive_task($queueID);
            $sql = "UPDATE {$_TABLES['nf_process']} set complete=1, completedDate='{$thisDate}' where id=$processID";
            $updateQuery = DB_query($sql );

        } else { // we've got tasks
            for($nextStepCntr = 0;$nextStepCntr < $nextTaskRows;$nextStepCntr++ ) {
                $C = DB_fetchArray($nextTaskResult );
                if ($this->_debug ) {
                    COM_errorLog("Got tasks  qid: $queueID. procid: $processID and Next taskid: {$C[0]}");
                }
                // if statement to check if the next template id is null
                // this is a catch all scenario to ensure that if we're on the last task and it points to null, that we end it properly
                if ($C[0] == null or $C[0] == '' ) {
                    // echo "thinks the process is done..  qid:" . $queueID . " procid:" . $processID . "<HR>";
                    // Process is done, set the process status to complete and archive queue item
                    $this->archive_task($queueID);
                    $sql = "UPDATE {$_TABLES['nf_process']} set complete=1, completedDate='{$thisDate}' where id=$processID";
                    $updateQuery = DB_query($sql );
                } else {
                    if ($this->_debug ) {
                        COM_errorLog("Next step qid:$queueID, procid:$processID");
                    }
                    // we have a next step, thus we can archive the queue item and also insert a
                    // new queue item with the next step populated as the next templatestepid
                    $sql  = "SELECT * FROM {$_TABLES['nf_queue']} a ";
                    $sql .= "WHERE a.nf_processid='{$processID}' ";
                    $sql .= "AND a.nf_templateDataid='{$C[0]}'";
                    $updateQuery = DB_query($sql );
                    $updateQueryRows = DB_numRows($updateQuery );
                    $retrieveQueryArray = DB_fetchArray($updateQuery );
                    if ($updateQueryRows == 0 ) {
                        // no next item in the queue.. just create it
                        $sql = "INSERT INTO {$_TABLES['nf_queue']} (nf_processID, nf_templateDataID, status, createdDate) ";
                        $sql .= " values ('{$processID}','{$C[0]}',0,'{$thisDate}')";
                        $updateQuery = DB_query($sql );
                        $newTaskid = DB_insertID();
                        if ($this->_debug ) {
                            $logmsg  = "Nexflow: New queue id (3) : $newTaskid - Template Taskid: {$C[0]} - ";
                            $logmsg .= "Assigned to " . COM_getDisplayName(nf_getTaskOwner($C[0],$processID));
                            nf_notificationLog($logmsg);
                        }
                        $newTaskAssignedUsers = $this->private_getAssignedUID($newTaskid);
                        if (is_array($newTaskAssignedUsers) AND count($newTaskAssignedUsers) > 0) {
                            $this->assign_task($newTaskid,$newTaskAssignedUsers);
                        }

                        // Determine if task has a reminder set and if so then update the nextReminderTime field in the new queue record
                        $reminderInterval = DB_getItem($_TABLES['nf_templatedata'],'reminderInterval',"id='{$C[0]}'");
                        if ($reminderInterval > 0) {
                            DB_query("UPDATE {$_TABLES['nf_queue']} SET nextReminderTime=DATE_ADD( NOW(), INTERVAL $reminderInterval DAY) where id='$newTaskid'");
                        }
                        DB_query("INSERT INTO {$_TABLES['nf_queuefrom']} (queueID,fromQueueID) values ('$newTaskid','{$queueID}')");

                        $this->archive_task($queueID);

                        // Check if notification has been defined for new task assignment
                        $this->private_sendTaskAssignmentNotifications();

                    } else {
                        // we have a situation here where the next item already exists.
                        // need to determine if the next item has a regeneration flag.
                        // if there is a regeneration flag, then create a new process starting with that regeneration flagged item
                        $regenResult = DB_query("SELECT * FROM {$_TABLES['nf_templatedata']} a where a.id='{$C[0]}'");
                        $regenCount = DB_numRows($regenResult );
                        $regenArray = DB_fetchArray($regenResult );

                        $toRegenerate = $regenArray['regenerate'];
                        $template = $regenArray['nf_templateID'];

                        if ($toRegenerate ) {
                            // regenerate the same process starting at the next step
                            // set the current process' complete status to 2.. 0 is active, 1 is done, 2 is has children
                            $this->newprocess($template, $C[0], $processID );
                            $this->archive_task($queueID);

                        } else{
                            //no regeneration  we're done
                            $toQueueID = $retrieveQueryArray['id'];
                            $sql = "INSERT INTO {$_TABLES['nf_queuefrom']} (queueID,fromQueueID) values ('{$toQueueID}','{$queueID}')";
                            $updateQuery = DB_query($sql );
                            $this->archive_task($queueID);

                            $sql = "SELECT * FROM {$_TABLES['nf_queue']} a WHERE a.nf_processid='{$processID}' AND a.nf_templateDataid='{$C[0]}'";
                            $updateQuery = DB_query( $sql );
                            $updateQueryRows = DB_numRows($updateQuery);
                            if($updateQueryRows == 0){
                                $sql = "UPDATE {$_TABLES['nf_process']} SET complete=1, completedDate='{$thisDate}' WHERE id='{$processID}'";
                                $updateQuery = DB_query( $sql );
                                }
                            }
                        }  //end else

                } //end else for the next step routine
            } //end for $nextstep
        } //end else portion for nextStepTest=0
    }    


  }


