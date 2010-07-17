// $Id:

/**
 * @file
 * taskconsole.js
 */

jQuery(function($) {
  $('.maestro_taskconsole_interactivetask a').click(function() {
    var taskid = jQuery(this).attr('taskid');
    $('#maestro_actionrec' + taskid).toggle();
    maestro_startTask(taskid);
  })
});

function maestro_startTask(taskid) {
  (function($) {
  $.post(ajax_url + '/starttask/',"taskid=" + taskid);
  })(jQuery);
}
