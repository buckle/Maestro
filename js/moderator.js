function show_reassign(link, uid) {
  (function ($) {
    var html;
    var show_flag = true;

    if ($('#reassign_form').html() != null) {
      if ($('#reassign_form').closest('tr').attr('id') == $(link).closest('tr').attr('id')) {
        show_flag = false;
      }
      $('#reassign_form').remove();
    }

    if (show_flag == true) {
      html  = '<div id="reassign_form"><form style="margin: 8px 0px 8px 0px; padding: 0px" method="post" action="' + $(link).attr('href') + '">';
      html += $('#user_select').html();
      html += '<input type="hidden" name="current_uid" value="' + uid + '">';
      html += '<input type="submit" value="' + Drupal.t('Go') + '">';
      html += '</form></div>';

      $(link).closest('td').append(html);
    }
  })(jQuery);
}

function switch_process_focus(pid) {
  (function ($) {
    var newclass;

    newclass = $('.focused_process').attr('class').replace('focused', 'blurred').replace('odd', 'even');
    $('.focused_process').attr('class', newclass);
    newclass = $('.process' + pid).attr('class').replace('blurred', 'focused').replace('even', 'odd');
    $('.process' + pid).attr('class', newclass);

    $('.process_variables').hide();
    $('#process_variables' + pid).show();
  })(jQuery);
}

function set_archived(el, index) {
  (function ($) {
    $('#archived' + index).attr('value', (el.checked) ? 1:0);
  })(jQuery);
}

function set_batch_op(el, index) {
  (function ($) {
    $('#batch_op' + index).attr('value', (el.checked) ? 1:0);
  })(jQuery);
}

function save_task_changes(frm) {
  (function ($) {
    enable_activity_indicator();
    $.ajax({
      type: 'POST',
      url: ajax_url,
      cache: false,
      data: $("#maestro_task_history_form").serialize(),
      dataType: 'json',
      success: save_success,
      error: moderator_ajax_error
    });
  })(jQuery);
}

function save_process_variables(frm) {
  (function ($) {
    enable_activity_indicator();
    $.ajax({
      type: 'POST',
      url: ajax_url,
      cache: false,
      data: $("#maestro_process_variables_form").serialize(),
      dataType: 'json',
      success: save_success,
      error: moderator_ajax_error
    });
  })(jQuery);
}

function save_success() {
  location.reload();
}

function enable_activity_indicator() {
  document.getElementById('maestro_ajax_indicator').style.display = '';
}

function disable_activity_indicator() {
  document.getElementById('maestro_ajax_indicator').style.display = 'none';
}

function moderator_ajax_error() {
  disable_activity_indicator();
}
