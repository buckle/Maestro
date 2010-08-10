  <div class="taskconsoleActionRec" style="min-width:600px;">
    <span id="expandProject<?php print $rowid; ?>" style="padding:0px 0px 15px 10px;"><a href="#" onClick='projectDetailToggleAll("expand",<?php print $rowid; ?>);return false;'><?php print t('Expand All'); ?></a></span>
    <span id="collapseProject<?php print $rowid; ?>" style="padding:0px 0px 15px 10px;display:none;"><a href="#" onClick='projectDetailToggleAll("collapse",<?php print $rowid; ?>);return false;'><?php print t('Collapse All'); ?></a></span>
    <span style="padding:0px 0px 15px 20px;display:<?php print $hiderequestlink ?>;">[&nbsp;<a href="<?php print $project_link ?>" onclick="alert('Not yet implemented');return false;"><?php print t('Request Link'); ?></a>&nbsp;]</span>
        <div id="newcomment_{<?php print $rowid; ?>}" style="padding-top:10px;display:none;">
            <form name="fprjcmt_{<?php print $rowid; ?>}" id="fprjcmt_{<?php print $rowid; ?>}" ACTION="{actionurl}" METHOD="post" style="margin:0px;">
                <fieldset><legend><b><?php print t('New Comment'); ?></b></legend>
                <div style="padding:5px;"><TEXTAREA id="newcomment{project_id}" name="comment" cols="100" rows="3"></TEXTAREA></div>
                <div style="padding-left:50px;">
                    <input type="button" value="<?php print t('Cancel'); ?>" onClick="document.getElementById('newcomment_{<?php print $rowid; ?>}').style.display='none';">&nbsp;
                    <input type="button" value="<?php print t('Add Comment'); ?>" onClick="ajaxProjectComment('addcomment',{<?php print $rowid; ?>},{project_id},{taskuser});">
                    <input type="hidden" name="projectid" value="{project_id}">
                    <input type="hidden" name="taskuser" value="{taskuser}">
                </div>
                </fieldset>
            </form>
        </div>

        <div class="taskdetail taskdetailOpenRec<?php print $rowid; ?>" id="summaryOpen_rec<?php print $rowid; ?>">
        <fieldset>
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/collapse.png" border="0" onClick="toggleProjectSection('summary','Open',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('summary','Open',<?php print $rowid; ?>)"><b><?php print t('Summary'); ?></b></span>
                </span>
            </legend>
            <table class="pluginSubTable" cellpadding="0" cellspacing="0" width="98%" border="0" style="margin:10px 5px 5px 5px;">
                <tr class="taskconsolesummary">
                    <td width="160"><?php print t('Flow Name'); ?>:</td><td><?php print $flow_description ?></td>
                </tr>
                <tr class="taskconsolesummary">
                    <td width="160"><?php print t('Flow Tracking ID'); ?></td><td><?php print $tracking_id ?></td>
                </tr>
                <tr class="taskconsolesummary">
                    <td width="160"><?php print t('Status'); ?>:</td>
                    <td nowrap><?php print $variables['project_status']; print $variables['special_status_action']; ?>
                        <span style="padding-left:20px;"><?php print $variables['delete_project_action'] ?></span>
                    </td>
                </tr>
                <?php print $custom_workflow_summary ?>
            </table>
        </fieldset>
        </div>
        <div class="taskdetail taskdetailClosedRec<?php print $rowid; ?>" id="summaryClosed_rec<?php print $rowid; ?>" style="padding:5px 19px;display:none;">
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/expand.png" border="0" onClick="toggleProjectSection('summary','Closed',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('summary','Closed',<?php print $rowid; ?>)"><b><?php print t('Summary'); ?></b></span>
                </span>
            </legend>
        </div>

        <div class="taskdetail taskdetailOpenRec<?php print $rowid; ?>" id="projectContentOpen_rec<?php print $rowid; ?>" style="display:none;">
        <fieldset>
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/collapse.png" border="0" onClick="toggleProjectSection('projectContent','Open',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('projectContent','Open',<?php print $rowid; ?>)"><b><?php print t('Content'); ?></b>
                </span>
            </legend>&nbsp;
            {content_records}
        </fieldset>
        </div>

        <div class="taskdetail taskdetailClosedRec<?php print $rowid; ?>" id="projectContentClosed_rec<?php print $rowid; ?>" style="padding:5px 19px;">
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/expand.png" border="0" onClick="toggleProjectSection('projectforms','Closed',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('projectContent','Closed',<?php print $rowid; ?>)"><b><?php print t('Content'); ?></b></span>
                </span>
            </legend>
        </div>

        <div class="taskdetail taskdetailOpenRec<?php print $rowid; ?>" id="outstandingTasksOpen_rec<?php print $rowid; ?>" style="display:none;">
        <fieldset>
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/collapse.png" border="0" onClick="toggleProjectSection('outstandingTasks','Open',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('outstandingTasks','Open',<?php print $rowid; ?>)"><b><?php print t('Outstanding Tasks'); ?></b></span>
                </span>
            </legend>
            <div id="outstanding_task_rec<?php print $rowid; ?>">
                <table class="pluginSubTable" cellpadding="0" cellspacing="1" width="99%" border="0" style="margin:10px 5px 5px 5px;">
                    <tr>
                        <th><?php print t('Task Name'); ?></th>
                        <th><?php print t('Owner'); ?></th>
                        <th><?php print t('Assigned'); ?></th>
                        <th style="display:<?php print $show_otaskaction ?>;"><?php print t('Action'); ?></th>
                    </tr>
                        <?php
                        foreach ($outstanding_tasks as $otask) { ?>
                        <tr>
                          <td><?php print $otask->taskname ?></td>
                          <td><?php print $otask->owner ?></td>
                          <td><?php print $otask->assigned_date ?></td>
                          <td><?php
                            if ($workflow_admin) {
                              ?>
                              <form style="margin:0px;">
                                  <input type="hidden" name="variable_id" value="<?php print $otask->variable_id; ?>">
                                  <input type="hidden" name="taskuser" value="<?php print $otask->taskuser ?>">
                                  <input type="hidden" name="taskassign_mode" value="<?php print $otask->taskassign_mode; ?>">
                                  <input type="hidden" name="id" value="<?php print $otask->task_id; ?>">
                                  <select name="task_reassign_uid">
                                      <option value="0"><?php print t('Assign to user'); ?></option>
                                        <?php
                                          foreach ($reassign_user_options as $user_id => $user_name) {
                                        ?>
                                            <option value="<?php print $user_id; ?>"><?php print $user_name; ?></option>
                                        <?php
                                           }
                                        ?>
                                  </select>
                                  <input type="button" name="op" value="<?php print t('Re-Assign'); ?>" onClick="ajaxUpdateTaskAssignment(<?php print $rowid; ?>,<?php print $tracking_id; ?>,this.form.task_reassign_uid.value,<?php print $otask->task_id; ?>,<?php print $otask->variable_id; ?>);">
                              </form>
                            <?php
                            } else {
                              print '&nbsp;';
                            }
                            ?>
                         </td>

                        </tr>
                        <?php
                        }
                        ?>
                </table>
            </div>
        </fieldset>
        </div>
        <div class="taskdetail taskdetailClosedRec<?php print $rowid; ?>" id="outstandingTasksClosed_rec<?php print $rowid; ?>" style="padding:5px 19px;">
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/expand.png" border="0" onClick="toggleProjectSection('outstandingTasks','Closed',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('outstandingTasks','Closed',<?php print $rowid; ?>)"><b><?php print t('Outstanding Tasks'); ?></b></span>
                </span>
            </legend>
        </div>
        <div class="taskdetail taskdetailOpenRec<?php print $rowid; ?>" id="tasklogOpen_rec<?php print $rowid; ?>" style="display:none;">
        <fieldset>
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/collapse.png" border="0" onClick="toggleProjectSection('tasklog','Open',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('tasklog','Open',<?php print $rowid; ?>)"><b><?php print t('Task History'); ?></b></span>
                </span>
            </legend>
            <?php print $task_history; ?>
        </fieldset>
        </div>
        <div class="taskdetail taskdetailClosedRec<?php print $rowid; ?>" id="tasklogClosed_rec<?php print $rowid; ?>" style="padding:5px 19px;">
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/expand.png" border="0" onClick="toggleProjectSection('tasklog','Closed',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('tasklog','Closed',<?php print $rowid; ?>)"><b><?php print t('Task History'); ?></b></span>
                </span>
            </legend>
        </div>
        <div class="taskdetail taskdetailOpenRec<?php print $rowid; ?>" id="projectCommentsOpen_rec<?php print $rowid; ?>" style="display:none;">
        <fieldset>
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/collapse.png" border="0" onClick="toggleProjectSection('projectComments','Open',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('projectComments','Open',<?php print $rowid; ?>)"><b><?php print t('Comments'); ?></b></span>
                </span>
            </legend>
            {comment_records}
        </fieldset>
        </div>
        <div class="taskdetail taskdetailClosedRec<?php print $rowid; ?>" id="projectCommentsClosed_rec<?php print $rowid; ?>" style="padding:5px 19px;">
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/expand.png" border="0" onClick="toggleProjectSection('projectComments','Closed',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('projectComments','Closed',<?php print $rowid; ?>)"><b><?php print t('Comments'); ?></b> </span>
                </span>
            </legend>
        </div>
    </div>