// $Id:

/**
 * @file
 * taskconsole.js
 */

jQuery(function($) {
  $('.maestro_taskconsole_interactivetaskName a').click(function() {
    var taskid = jQuery(this).attr('taskid');
    $('#maestro_actionrec' + taskid).toggle();
    maestro_startTask(taskid);
  })
});


jQuery(function($) {
  $('.maestro_taskconsole_interactivetaskcontent input[type=button]').click(function() {
    var id = jQuery(this).closest('tr').attr('id');
    var idparts = id.split('maestro_actionrec');
    var taskid = idparts[1];
    var op = jQuery(this).attr('maestro');
    dataString = jQuery("#" + id).serialize();
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


function maestro_startTask(taskid) {
  (function($) {
  $.post(ajax_url + '/starttask/',"taskid=" + taskid);
  })(jQuery);
}
