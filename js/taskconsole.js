// $Id:

/**
 * @file
 * taskconsole.js
 */

/* When the task name in the task console is clicked, open the interactive task (inline function)
 * Trigger the AJAX update to update the task start_date
 */
jQuery(function($) {
  $('.maestro_taskconsole_interactivetaskName a').click(function() {
    var taskid = jQuery(this).attr('taskid');
    $('#maestro_actionrec' + taskid).toggle();
    $.post(ajax_url + '/starttask/',"taskid=" + taskid);
  })
});


/* When the task name in the task console is clicked, open the interactive task (inline function)
 * Trigger the AJAX update to update the task start_date
 */
jQuery(function($) {
  $('.maestro_taskconsole_viewdetail').click(function() {
    var taskid = jQuery(this).attr('taskid');
    $('#maestro_taskconsole_detail_rec' + taskid).toggle();
  })
});

/* Function handles the form submit buttons for the inline interactive tasks
 * All the form buttons should be of input type 'button' even the 'task complete'
 * Function will fire automatically when a form button is pressed and execute the
 * ajax operation for the interactive_post action and automatically post the form contents plus
 * the taskid and task operation that was picked up from the button's custom 'maestro' attribute.
 * <input type="button" maestro="save" value="Save Data">
 */
jQuery(function($) {
  $('.maestro_taskconsole_interactivetaskcontent input[type=button]').click(function() {
    var id = jQuery(this).parents('tr').filter(".maestro_taskconsole_interactivetaskcontent").attr('id');
    var idparts = id.split('maestro_actionrec');
    var taskid = idparts[1];
    var op = jQuery(this).attr('maestro');
    dataString = jQuery(this).closest('form').serialize();
    dataString += "&queueid=" + taskid;
    dataString += "&op=" + op;
    jQuery.ajax( {
      type : 'POST',
      cache : false,
      url : ajax_url + '/interactivetask_post',
      dataType : "json",
      success : function(data) {
        $("#maestro_actionrec" + taskid).hide();
        if (data.status == 1) {
          if (data.hidetask == 1) {
            $("#maestro_taskcontainer" + taskid).hide();
          }
        } else {
          alert('An error occurred processing this interactive task');
        }
      },
      error : function() { alert('there was a SERVER Error processing AJAX request'); },
      data : dataString
    });
    return false;

  })
});


jQuery(function($) {
  $('#taskConsoleLaunchNewProcess').click(function() {
  	$("#newProcessStatusRowSuccess").hide();
  	$("#newProcessStatusRowFailure").hide();
    dataString = jQuery('#frmLaunchNewProcess').serialize();
    dataString += "&op=newprocess";
    jQuery.ajax( {
      type : 'POST',
      cache : false,
      url : ajax_url + '/newprocess',
      dataType : "json",
      success : function(data) {
        if (data.status == 1 && data.processid > 0) {
	        $("#newProcessStatusRowSuccess").show();	          
        } else {
        	$("#newProcessStatusRowFailure").show();
        }
      },
      error : function() { $("#newProcessStatusRowFailure").show(); },
      data : dataString
    });
    return false;
  })
});

