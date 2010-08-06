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
    var newclass = $('.focused_process').attr('class').replace('focused', 'blurred');
    $('.focused_process').attr('class', newclass);
    newclass = $('.process' + pid).attr('class').replace('blurred', 'focused');
    $('.process' + pid).attr('class', newclass);
  })(jQuery);
}
