var draw_status = 0;
var draw_type = 1;
var line_start = '';
var line_end = '';
var existing_tasks = [];
var line_ids = [];
var lines = [];
var dd = [];
var steptypes = [];
var taskIdCount = -1;
var trueLineColor = "#000000";
var falseLineColor = "#D10000";
var oMenu;
var panels = [];
var oMenuBar;
var menuCheckArray = [];

(function($) {
  $(document).ready(function() {
    initialize_drag_drop();
    initialize_lines();
  });

})(jQuery);

function initialize_drag_drop() {
  (function($) {
    $(".maestro_task_container").bind("dragstart", function(event, ui) {
      $( ".maestro_task_container" ).draggable( "option", "zIndex", 500 );
    });
    $(".maestro_task_container").bind("dragstop", function(event, ui) {
      update_lines(this);
      $( ".maestro_task_container" ).draggable( "option", "zIndex", 100 );
      var task_class = this.className.split(' ')[0];
      var task_id = this.id.substring(4, this.id.length);
      enable_ajax_indicator();
      $.post(ajax_url + task_class + '/' + task_id + '/0/move/', {offset_left: this.offsetLeft, offset_top: this.offsetTop}, disable_ajax_indicator());
    });
    $(".maestro_task_container").bind("drag", function(event, ui) {
      if (document.frm_animate.animateFlag.checked) {
        update_lines(this);
      }
    });
  })(jQuery);
}

function update_snap_to_grid() {
  (function($) {
    if (document.frm_animate.snapToGrid.checked) {
      $( ".maestro_task_container" ).draggable("option", "grid", [25, 25]);
    }
    else {
      $( ".maestro_task_container" ).draggable("option", "grid", false);
    }
  })(jQuery);
}

function update_snap_to_objects() {
  (function($) {
    if (document.frm_animate.snapToObjects.checked) {
      $( ".maestro_task_container" ).draggable("option", "snap", true);
    }
    else {
      $( ".maestro_task_container" ).draggable("option", "snap", false);
    }
  })(jQuery);
}

function update_lines(el) {
  //update transactions involving el with the new coords
  var cnt = 0;
  var length = lines.length;
  for (var i in lines) {
    if (lines[i] != null && lines[i][8] != null) {
      if (lines[i][5] == el || lines[i][6] == el) {
        lines[i][8].clear();
        lines[i] = connect_tasks(lines[i][5], lines[i][6], lines[i][7], lines[i][8]);
      }
    }
  }
}


function initialize_lines() {
  for (var i in line_ids) {
    var start = document.getElementById(line_ids[i][0]);
    var end = document.getElementById(line_ids[i][1]);

    if (start != null && end != null) {
      var line = connect_tasks(start, end, line_ids[i][2]);
    }
    lines.push(line);
  }
  redraw_lines();
}

function begin_task_connect() {
  if (draw_status == 0) {
    draw_status = 1;
    document.getElementById('nf_tooltip').innerHTML = LANG_select_first;
  }
}

function draw_line(line) {
  //draw the line
  line[8].drawLine(line[0], line[1], line[2], line[3]);

  //draw the arrow start
  line[8].fillEllipse(line[0] - 2, line[1] - 2, 6, 6);

  //draw the arrow head
  if (line[2] == line[2] && line[3] == line[3]) {     //since NaN never equals itself, we can use this trick to check for NaN
    var start = line[4] - 30;
    if (start < 0) {
      start += 360;
    }
    var end = line[4] + 30
    line[8].fillArc(line[2] - 10, line[3] - 10, 22, 22, start, end);
  }

  line[8].paint();
}

function connect_tasks(start, end, styleFlag, drawClass) {
  var start_bottom = start.offsetTop + start.offsetHeight;
  var end_bottom = end.offsetTop + end.offsetHeight;
  var start_halfwidth = start.offsetWidth / 2;
  var start_halfheight = start.offsetHeight /2;
  var end_halfwidth = end.offsetWidth / 2;
  var end_halfheight = end.offsetHeight / 2;
  var start_midx = start.offsetLeft + start_halfwidth;
  var start_midy = start.offsetTop + start_halfheight;
  var end_midx = end.offsetLeft + end_halfwidth;
  var end_midy = end.offsetTop + end_halfheight;
  var line = [];
  var x, y;
  var x_st, y_st;

  var x_diff = end_midx - start_midx;
  var y_diff = end_midy - start_midy;

  //find the point to draw the arc
  var m = y_diff / x_diff;
  var b = start.offsetTop + start_halfheight;

  if (start_midx < end_midx) {
    x = end.offsetLeft - end_halfwidth - 1;
    x_st = start.offsetLeft + start_halfwidth + 1;
  }
  else {
    x = end.offsetLeft + end_halfwidth;
    x_st = start.offsetLeft - start_halfwidth - 1;
  }

  var excess = end_halfwidth - start_halfwidth;
  var excess_st = start_halfwidth - start_halfwidth;
  line[0] = x_st + start_halfwidth;
  line[1] = (m * (x_st - start.offsetLeft + excess_st)) + b;

  line[2] = x + end_halfwidth;
  line[3] = (m * (x - start.offsetLeft + excess)) + b;

  //start pos
  if (line[1] > (start.offsetTop + start.offsetHeight)) {
    y = start.offsetTop + start.offsetHeight + 1;
    if (x_diff == 0) {
      x = start_halfwidth + start.offsetLeft;
      y -= start.offsetHeight + 1;
    }
    else {
      x = ((y - b) / m) + start.offsetLeft - excess_st + start_halfwidth;
    }
    line[0] = x;
    line[1] = y;
  }
  else if (line[1] < start.offsetTop) {
    y = start.offsetTop - 1;
    if (x_diff == 0) {
      x = start_halfwidth + start.offsetLeft;
      y += start.offsetHeight + 2;
    }
    else {
      x = ((y - b) / m) + start.offsetLeft - excess_st + start_halfwidth;
    }
    line[0] = x;
    line[1] = y;
  }

  //end pos
  if (line[3] < end.offsetTop) {
    y = end.offsetTop - 1;
    if (x_diff == 0) {
      x = end_halfwidth + end.offsetLeft;
      y += end.offsetHeight;
    }
    else {
      x = ((y - b) / m) + start.offsetLeft - excess + end_halfwidth;
    }
    line[2] = x;
    line[3] = y;
  }
  else if (line[3] > (end.offsetTop + end.offsetHeight)) {
    y = end.offsetTop + end.offsetHeight - 1;
    if (x_diff == 0) {
      x = end_halfwidth + end.offsetLeft;
      y -= end.offsetHeight;
    }
    else {
      x = ((y - b) / m) + start.offsetLeft - excess + end_halfwidth;
    }
    line[2] = x;
    line[3] = y;
  }

  //find the angle to draw the arrow
  if (x_diff > 0 && y_diff < 0) {         //quadrant 1
    y_diff *= -1;
    angle = cot(y_diff / x_diff);
    angle += 180;
    quad = 1;
  }
  else if (x_diff < 0 && y_diff < 0) {    //quadrant 2
    x_diff *= -1;
    y_diff *= -1;
    angle = cot(x_diff / y_diff) + 270;
    quad = 2;
  }
  else if (x_diff < 0 && y_diff > 0) {    //quadrant 3
    x_diff *= -1;
    angle = cot(y_diff / x_diff);
    quad = 3;
  }
  else if (x_diff > 0 && y_diff > 0) {    //quadrant 4
    angle = cot(x_diff / y_diff) + 90;
    quad = 4;
  }
  else if (x_diff == 0 && y_diff < 0) {   //vertical, positive slope
    angle = 270;
  }
  else if (x_diff == 0 && y_diff > 0) {   //vertical, negative slope
    angle = 90;
  }
  else if (y_diff == 0 && x_diff < 0) {   //horizontal, moving left
    angle = 0;
  }
  else if (y_diff == 0 && x_diff > 0) {   //horizontal, moving right
    angle = 180;
  }
  else if (x_diff == 0 && y_diff == 0) {  //line to nowhere
    angle = 0;
  }

  line[4] = angle;

  //add to the record of lines
  line[5] = start;
  line[6] = end;
  line[7] = styleFlag;
  if (drawClass != null) {
    line[8] = drawClass;
  }
  else if (line[8] != null) {
    line[8].clear();
  }
  else {
    line[8] = new jsGraphics('maestro_workflow_container');
    line[8].setStroke(2);
    if (styleFlag) {
      line[8].setColor(trueLineColor);
    }
    else {
      line[8].setColor(falseLineColor);
    }
  }

  draw_line(line);

  return line;
}

function redraw_lines() {
  for (var i in lines) {
    if (lines[i] != null && lines[i][5] != null) {
      draw_line(lines[i]);
    }
  }
}

function display_task_panel(r) {
  (function($) {
    $.modal(r.html, { modal: true, overlayClose: true, autoPosition: true, overlayCss: {backgroundColor:"#888"}, opacity:80 });
    disable_ajax_indicator();
  })(jQuery);
}

function add_task_success(r) {
  (function($) {
    $('#maestro_workflow_container').append(r.html);
    eval(r.js);
    $(".maestro_task_container").draggable( {snap: true} );
    initialize_drag_drop();
    update_snap_to_grid();
    update_snap_to_objects();
    initialize_drag_drop();
    disable_ajax_indicator();
  })(jQuery);
}

function save_task(frm) {
  (function($) {
    enable_ajax_indicator();
    $.post(ajax_url + frm.task_class.value + '/' + frm.template_data_id.value + '/0/save/', $("#maestro_task_edit_form").serialize(), save_task_success);
  })(jQuery);
  return false;
}

function save_task_success() {
  (function($) {
    $.modal.close();
    disable_ajax_indicator();
  })(jQuery);
}

function draw_line_to(element) {
  (function($) {
    if (draw_status == 1) {
      set_tool_tip('');
      line_end = element;
      if (line_start != line_end) {
        //draw line now
        var type = (draw_type == 1) ? true:false;
        var line = connect_tasks(line_start, line_end, type);
        lines.push(line);
        var template_data_id = line_start.id.substr(4, line_start.id.length - 4);
        var template_data_id2 = line_end.id.substr(4, line_end.id.length - 4);
        var task_class = line_start.className.split(' ')[0];

        if (draw_type == 2) {
          $.post(ajax_url + task_class + '/' + template_data_id + '/0/drawLineFalse/', { line_to: template_data_id2 }, disable_ajax_indicator);
        }
        else {
          $.post(ajax_url + task_class + '/' + template_data_id + '/0/drawLine/', { line_to: template_data_id2 }, disable_ajax_indicator);
        }

        enable_ajax_indicator();
      }
      draw_status = 0;
    }
  })(jQuery);
}

function clear_task_lines(el) {
  (function($) {
    var indexes = [];
    var i = 0;
    var j = 0;
    var template_data_id = el.id.substr(4, el.id.length - 4);
    var task_class = el.className.split(' ')[0];

    for (i in lines) {
      if (lines[i] != null && lines[i][8] != null) {
        if (el == lines[i][5] || el == lines[i][6]) {
          lines[i][8].clear();
          delete lines[i][8];
          indexes.push(i);
        }
      }
    }
    var cnt = 0;
    var length = indexes.length;
    for (i in indexes) {
      if (++cnt > length) {
        break;
      }
      lines.splice(indexes[i] - j++, 1);
    }

    $.post(ajax_url + task_class + '/' + template_data_id + '/0/clearAdjacentLines/');
  })(jQuery);
}

function delete_task(r) {
  (function($) {
    disable_ajax_indicator();
    if (r.success == 0) {  //warn the user first
      set_tool_tip(r.message);
    }
    else {  //just make the task invisible for now, it will get fully deleted on page reload
      var el = document.getElementById('task' + r.task_id);
      clear_task_lines(el);
      el.style.display = 'none';
    }
  })(jQuery);
}

function grow_canvas() {
  (function($) {
    $('#maestro_workflow_container').height($('#maestro_workflow_container').height() + 100);
    enable_ajax_indicator();
    $.post(ajax_url + 'MaestroTaskInterfaceStart/0/' + template_id + '/setCanvasHeight/', { height: $('#maestro_workflow_container').height() }, disable_ajax_indicator);
  })(jQuery);
}

function shrink_canvas() {
  (function($) {
    $('#maestro_workflow_container').height($('#maestro_workflow_container').height() - 100);
    enable_ajax_indicator();
    $.post(ajax_url + 'MaestroTaskInterfaceStart/0/' + template_id + '/setCanvasHeight/', { height: $('#maestro_workflow_container').height() }, disable_ajax_indicator);
  })(jQuery);
}



//general helper functions
function set_tool_tip(msg) {
  if (msg == '') {
    document.getElementById('maestro_tool_tip_container').style.display = 'none';
  }
  else {
    document.getElementById('maestro_tool_tip_container').style.display = '';
  }
  document.getElementById('maestro_tool_tip').innerHTML = msg;
}

function enable_ajax_indicator() {
  document.getElementById('maestro_ajax_indicator').style.display = '';
}

function disable_ajax_indicator() {
  document.getElementById('maestro_ajax_indicator').style.display = 'none';
}

function cot(x) {
  return Math.atan(x) * 57.2957795;
}



