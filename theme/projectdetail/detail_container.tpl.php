  <div class="taskconsoleActionRec" style="min-width:600px;">
    <span style="padding:0px 0px 15px 10px;"><a id="expand{rowid}" href="#" onClick='expandall({rowid});'>Expand All</a></span>
    <span style="padding:0px 0px 15px 20px;display:{hiderequestlink};">[&nbsp;<a href="{project_link}">Request Link</a>&nbsp;]</span>
        <div id="newcomment_{rowid}" style="padding-top:10px;display:none;">
            <form name="fprjcmt_{rowid}" id="fprjcmt_{rowid}" ACTION="{actionurl}" METHOD="post" style="margin:0px;">
                <fieldset><legend><b>New Comment</b></legend>
                <div style="padding:5px;"><TEXTAREA id="newcomment{project_id}" name="comment" cols="100" rows="3"></TEXTAREA></div>
                <div style="padding-left:50px;">
                    <input type="button" value="Cancel" onClick="document.getElementById('newcomment_{rowid}').style.display='none';">&nbsp;
                    <input type="button" value="Add Comment" onClick="ajaxProjectComment('addcomment',{rowid},{project_id},{taskuser});">
                    <input type="hidden" name="projectid" value="{project_id}">
                    <input type="hidden" name="taskuser" value="{taskuser}">
                </div>
                </fieldset>
            </form>
        </div>

        <div class="taskdetail" id="summaryOpen_rec{rowid}">
        <fieldset>
            <legend>
                <span>
                    <img src="{layout_url}/images/collapse.png" border="0" onClick="togglerec('summaryOpen_',{rowid})">
                        <b>Summary</b>
                </span>
            </legend>
            <table class="pluginSubTable" cellpadding="0" cellspacing="0" width="98%" border="0" style="margin:10px 5px 5px 5px;">
                <tr class="taskconsolesummary">
                    <td width="160">Flow Name:</td><td><?php print $flow_description ?></td>
                </tr>

                <tr class="taskconsolesummary">
                    <td width="160">Flow Tracking ID</td><td><?php print $flow_tracking_number ?></td>
                </tr>
                <tr class="taskconsolesummary">
                    <td width="160">Status:</td>
                    <td nowrap>{project_status}{special_status_action}
                        <span style="padding-left:20px;">{delete_project_action}</span>
                    </td>
                </tr>
                <?php print $custom_workflow_summary ?>
            </table>
        </fieldset>
        </div>
        <div class="taskdetail" id="summaryClosed_rec{rowid}" style="padding:5px 19px;display:none;">
            <legend>
                <span>
                    <img src="{layout_url}/images/expand.png" border="0" onClick="togglerec('summaryClosed_',{rowid})">
                        <b>Summary</b>
                </span>
            </legend>
        </div>

        <div class="taskdetail" id="projectformsOpen_rec{rowid}" style="display:none;">
        <fieldset>
            <legend>
                <span>
                    <img src="{layout_url}/images/collapse.png" border="0" onClick="togglerec('projectformsOpen_',{rowid})">
                        <b>Forms</b>
                </span>
            </legend>&nbsp;
            {form_records}
        </fieldset>
        </div>

        <div class="taskdetail" id="projectformsClosed_rec{rowid}" style="padding:5px 19px;">
            <legend>
                <span>
                    <img src="{layout_url}/images/expand.png" border="0" onClick="togglerec('projectformsClosed_',{rowid})">
                        <b>Forms</b>
                </span>
            </legend>
        </div>

        <div class="taskdetail" id="outstandingTasksOpen_rec{rowid}" style="display:none;">
        <fieldset>
            <legend>
                <span>
                    <img src="{layout_url}/images/collapse.png" border="0" onClick="togglerec('outstandingTasksOpen_',{rowid})">
                        <b>Outstanding Tasks</b>
                </span>
            </legend>
            <div id="outstanding_task_rec{rowid}">
                <table class="pluginSubTable" cellpadding="0" cellspacing="1" width="99%" border="0" style="margin:10px 5px 5px 5px;">
                    <tr>
                        <th>Task Name</th>
                        <th>Owner</th>
                        <th>Assigned</th>
                        <th style="display:{show_otaskaction};">Action</th>
                    </tr>
                        {outstandingtask_records}
                </table>
            </div>
        </fieldset>
        </div>
        <div class="taskdetail" id="outstandingTasksClosed_rec{rowid}" style="padding:5px 19px;">
            <legend>
                <span>
                    <img src="{layout_url}/images/expand.png" border="0" onClick="togglerec('outstandingTasksClosed_',{rowid})">
                        <b>Outstanding Tasks</b>
                </span>
            </legend>
        </div>
        <div class="taskdetail" id="tasklogOpen_rec{rowid}" style="display:none;">
        <fieldset>
            <legend>
                <span>
                    <img src="{layout_url}/images/collapse.png" border="0" onClick="togglerec('tasklogOpen_',{rowid})">
                        <b>Task History</b>
                </span>
            </legend>
            <table class="pluginSubTable" cellpadding="0" cellspacing="1" border="0" width="99%" style="margin:10px 5px 5px 5px;">
                <tr>
                    <th>Task Name</th>
                    <th>Owner</th>
                    <th>Assigned</th>
                    <th>Started</th>
                    <th>Completed</th>
                    <th>Status</th>
                </tr>
                    {task_records}
            </table>
        </fieldset>
        </div>
        <div class="taskdetail" id="tasklogClosed_rec{rowid}" style="padding:5px 19px;">
            <legend>
                <span>
                    <img src="{layout_url}/images/expand.png" border="0" onClick="togglerec('tasklogClosed_',{rowid})">
                        <b>Task History</b>
                </span>
            </legend>
        </div>
        <div class="taskdetail" id="projectCommentsOpen_rec{rowid}" style="display:none;">
        <fieldset>
            <legend>
                <span>
                    <img src="{layout_url}/images/collapse.png" border="0" onClick="togglerec('projectCommentsOpen_',{rowid})">
                        <b>Comments</b>
                </span>
            </legend>
            {comment_records}
        </fieldset>
        </div>
        <div class="taskdetail" id="projectCommentsClosed_rec{rowid}" style="padding:5px 19px;">
            <legend>
                <span>
                    <img src="{layout_url}/images/expand.png" border="0" onClick="togglerec('projectCommentsClosed_',{rowid})">
                        <b>Comments</b>
                </span>
            </legend>
        </div>
    </div>