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

function show_panel_tab(id, prefix) {
    document.getElementById(prefix + 'main').style.display = 'none';
    document.getElementById(prefix + 'assign_by_user').style.display = 'none';
    document.getElementById(prefix + 'assign_by_var').style.display = 'none';
    document.getElementById(prefix + 'notify_on_assign').style.display = 'none';
    document.getElementById(prefix + 'notify_on_complete').style.display = 'none';
    document.getElementById(prefix + 'notify_reminders').style.display = 'none';

    document.getElementById(prefix + id).style.display = '';
}

function header_clicked(element) {
    if (draw_status == 1) {
        document.getElementById('nf_tooltip').innerHTML = '';
        line_end = element;
        if (line_start != line_end) {
            //draw line now
            var type = (draw_type == 1) ? true:false;
            var line = connect_tasks(line_start, line_end, type);
            lines.push(line);
            SaveTaskLines.request(line_start, line_end, type);
        }
        draw_status = 0;
    }
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
    line[8].fillEllipse(line[0] - 5, line[1] - 5, 10, 10);

    //draw the arrow head
    if (line[2] == line[2] && line[3] == line[3]) {     //since NaN never equals itself, we can use this trick to check for NaN
        var start = line[4] - 30;
        if (start < 0) {
            start += 360;
        }
        var end = line[4] + 30
        line[8].fillArc(line[2] - 12, line[3] - 12, 26, 26, start, end);
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
        line[8] = new jsGraphics('workflow_container');
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

function add_task(taskid, tasktype, offsetLeft, offsetTop, isInteractive) {
    var div = document.getElementById('workflow_container');
    var dropdown = [];
    var tbl;
    var type;
    var text;

    var MenuManager = YAHOO.widget.MenuManager;

    //reset the task elements
    for (i in lines) {
        if (lines[i] != null && lines[i][8] != null) {
            lines[i][8].clear();
            delete lines[i][8];
        }
    }

    text = LANG_task_type + ': ' + tasktype;
    if (isInteractive == 1) {
        type = 'interactive';
        text += '<br>' + LANG_assigned_to + ': ' + LANG_nobody_assigned;
    }
    else {
        if (tasktype == 'If') {
            type = 'if';
        }
        else {
            type = 'noninteractive';
        }
    }

    tbl = document.getElementById('task_template').innerHTML;
    tbl = str_replace_all('%d', taskid, tbl);
    tbl = str_replace_all('%s', LANG_new_task, tbl);
    tbl = str_replace_all('%c', 'nf_' + type, tbl);
    tbl = str_replace_all('%i', text, tbl);
    tbl = str_replace_all('%u', str_replace_all(' ', '_', tasktype), tbl);
    tbl = str_replace_all('%t', tasktype, tbl);

    div.innerHTML += tbl;
    el = document.getElementById('task' + taskid);
    var attributes = {
        points: { from: [-300, -300], to: [offsetLeft, offsetTop] }
    };
    var ani = new YAHOO.util.Motion(el, attributes);
    ani.duration = 1;
    ani.method = YAHOO.util.Easing.backOut;
    ani.animate();
    delete (ani);

    dropdown[0] = 'task' + taskid;
    dropdown[1] = null;
    dropdown[2] = null;
    dd.push(dropdown);

    var length = dd.length;
    var cnt = 0;
    for (i in dd) {
        if (++cnt > length) {
            break;
        }
        //recreate dragdrop
        delete (dd[i][1]);
        dd[i][1] = new YAHOO.example.DDRegion(dd[i][0], '', { cont: 'workflow_container' });
        dd[i][1].setHandleElId(dd[i][0] + '_handle');

        //recreate context menu
        if (dd[i][0] != 'task' + taskid) { //there is no menu to delete for the new task yet.
            var child_obj = document.getElementById(dd[i][0] + '_menu');
            div.removeChild(child_obj);
            MenuManager.removeMenu(dd[i][2]);
            delete (dd[i][2]);
        }
        dd[i][2] = init_task_context_menu(dd[i][0]);
    }

    for (i in lines) {
        if (lines[i] != null && lines[i][5] != null) {
            lines[i][5] = document.getElementById(lines[i][5].id);
            lines[i][6] = document.getElementById(lines[i][6].id);

            lines[i][8] = new jsGraphics('workflow_container');
            lines[i][8].setStroke(2);
            if (lines[i][7]) {
                lines[i][8].setColor(trueLineColor);
            }
            else {
                lines[i][8].setColor(falseLineColor);
            }
        }
    }

    redraw_lines();
}

function edit_task(id) {
    var type = document.getElementById(id + '_type').value;
    var i = 0;

    switch (type) {
    case 'Manual_Web':
        panels[0].show();
        break;

    case 'And':
        panels[1].show();
        break;

    case 'Batch':
        panels[2].show();
        break;

    case 'If':
        panels[3].show();
        break;

    case 'Batch_Function':
        panels[4].show();
        break;

    case 'Interactive_Function':
        panels[5].show();
        break;

    case 'Nexform':
        panels[6].show();
        break;

    case 'Set_Process_Variable':
        panels[7].show();
        break;
    }
}

function finish_task_edit(id) {
    var type = document.getElementById(id + '_type').value;

    switch (type) {
    case 'Manual_Web':
        panels[0].hide();
        break;

    case 'And':
        panels[1].hide();
        break;

    case 'Batch':
        panels[2].hide();
        break;

    case 'If':
        panels[3].hide();
        break;

    case 'Batch_Function':
        panels[4].hide();
        break;

    case 'Interactive_Function':
        panels[5].hide();
        break;

    case 'Nexform':
        panels[6].hide();
        break;

    case 'Set_Process_Variable':
        panels[7].hide();
        break;
    }
}


function delete_task(id) {
    var div = document.getElementById(id);

    if (!confirm(LANG_confirm_del_task)) {
        return false;
    }

    var old_height = div.offsetHeight;

    clear_task_lines(div);
    div.innerHTML = '';
    div.style.width = '0px';
    div.style.height = old_height + 'px';

    for (i in dd) {
        if (dd[i][0] == id) {
            dd.splice(i, 1);
            break;
        }
    }

    DeleteTask.request(div);
}

function clear_task_lines(el) {
    var indexes = [];
    var i = 0;
    var j = 0;

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

    ClearTaskLines.request(el);
}

//
// General Helper Functions
//////////////////////////////////////////////////////////

function cot(x) {
    return Math.atan(x) * 57.2957795;
}

function str_replace_all (search, replace, str) {
    while (str.indexOf(search) != -1) {
        str = str.replace(search, replace);
    }
    return str;
}

function moveSelectedOption(fromID, toID) {
    var from = document.getElementById(fromID);
    var to = document.getElementById(toID);

    while (from.selectedIndex >= 0) {
        to.options[to.options.length] = new Option(from.options[from.selectedIndex].innerHTML, from.options[from.selectedIndex].value);
        from.options[from.selectedIndex] = null;
    }
}

function toggleDynamicNameSelector(prefix, checked) {
    if (checked) {
        document.getElementById(prefix + 'dynamicNameSelectVariable').style.display = '';
    }
    else {
        document.getElementById(prefix + 'dynamicNameSelectVariable').style.display = 'none';
    }
}

function updateFieldList(formid, fieldid) {
    var fieldValue = document.getElementById('fieldValue');
    var options = nf_getElementsByClassName('nfForm' + formid, 'option', document.getElementById('fieldValueCpy'));
    var i;

    //first clear out the old options
    while (fieldValue.options.length > 0) {
        fieldValue.options[0] = null;
    }

    cnt = 0;
    length = options.length;
    for (i in options) {
        if (++cnt > length) {
            break;
        }
        fieldValue.options[fieldValue.options.length] = new Option(options[i].innerHTML, options[i].value);
        if (options[i].value == fieldid) {
            fieldValue.options[fieldValue.options.length - 1].selected = true;
        }
    }
}

function nf_getElementsByClassName(className, tag, elm){
    var testClass = new RegExp("(^|\\s)" + className + "(\\s|$)");
    var tag = tag || "*";
    var elm = elm || document;
    var elements = (tag == "*" && elm.all)? elm.all : elm.getElementsByTagName(tag);
    var returnElements = [];
    var current;
    var length = elements.length;
    for(var i=0; i<length; i++){
        current = elements[i];
        if(testClass.test(current.className)){
            returnElements.push(current);
        }
    }
    return returnElements;
}


//Main Context Menu Function
function onMenuItemClick(p_sType, p_aArguments) {
    var menu = document.getElementById('nf_maincontextmenu');

    //call the connection manager to add the task and get the id
    SaveNewTask.request(this.activeItem.value[1], menu.offsetLeft, menu.offsetTop, this.activeItem.value[2]);
}

function onContextMenuRender(p_sType, p_aArgs) {
    this.clickEvent.subscribe(onMenuItemClick);
}

function init_context_menu() {
    var taskmenu = [];

    oMenu = new YAHOO.widget.ContextMenu("nf_maincontextmenu", { trigger: document.body, lazyload: true });

    //remove any existing items
    var cnt = 0;
    var length = steptypes.length;
    for (var i in steptypes) {
        if (++cnt > length) {
            break;
        }
        taskmenu.push({ text: steptypes[i][1], value: steptypes[i] });
    }
    oMenu.addItems(taskmenu);
    oMenu.setItemGroupTitle(LANG_new_task + ': ', 0);
    oMenu.showEvent.subscribe(function () {
        this.focus();
    });

    oMenu.renderEvent.subscribe(onContextMenuRender);
    oMenu.render(document.body);

    document.getElementById('nf_maincontextmenu').className = 'nf_contextmenu';
}

//Task Context Menu Functions
function onTaskMenuItemClick(p_sType, p_aArguments) {
    var tbl = document.getElementById(this.activeItem.value[1]);

    switch (this.activeItem.value[0]) {
    case 'edit':
        GetPanelForm.request(this.activeItem.value[1]);
        break;

    case 'clear':
        clear_task_lines(tbl);
        break;

    case 'draw':
        document.getElementById('nf_tooltip').innerHTML = LANG_select_last;
        line_start = tbl;
        draw_status = 1;
        draw_type = 1;
        break;

    case 'draw_false':
        document.getElementById('nf_tooltip').innerHTML = LANG_select_last;
        line_start = tbl;
        draw_status = 1;
        draw_type = 0;
        break;

    case 'delete':
        delete_task(this.activeItem.value[1]);
        break;
    }

}

function onTaskContextMenuRender(p_sType, p_aArgs) {
    this.clickEvent.subscribe(onTaskMenuItemClick);
}

function init_task_context_menu(taskid) {
    var oTaskMenu;
    var taskmenu = [];
    var handle = document.getElementById(taskid + '_handle');
    var menuid = taskid + '_menu';

    oTaskMenu = new YAHOO.widget.ContextMenu(menuid, { trigger: taskid, lazyload: true });

    //build task menu
    var taskMenuItems = [];

    if (handle.className != 'nf_end' && handle.className != 'nf_if') {
        taskMenuItems.push({ text: LANG_draw_line, value: ['draw', taskid] });
    }

    if (handle.className == 'nf_if') {
        taskMenuItems.push({ text: LANG_draw_line_true, value: ['draw', taskid] });
        taskMenuItems.push({ text: LANG_draw_line_false, value: ['draw_false', taskid] });
    }

    taskMenuItems.push({ text: LANG_clear_adj_lines, value: ['clear', taskid] });

    if (handle.className != 'nf_start' && handle.className != 'nf_end') {
        taskMenuItems.push({ text: LANG_edit_task, value: ['edit', taskid] });
        taskMenuItems.push({ text: LANG_delete_task, value: ['delete', taskid] });
    }

    oTaskMenu.addItems(taskMenuItems);
    oTaskMenu.showEvent.subscribe(function () {
        this.focus();
    });

    oTaskMenu.renderEvent.subscribe(onTaskContextMenuRender);
    oTaskMenu.render('workflow_container');

    document.getElementById(menuid).className = 'nf_contextmenu';

    return oTaskMenu;
}

//MenuBar Functions
function onMenuBarClick(p_sType, p_aArguments) {
    show_panel_tab(this.activeItem.value[0], this.activeItem.value[1]);
}

function onMenuBarRender(p_sType, p_aArgs) {
    this.clickEvent.subscribe(onMenuBarClick);

    var submenus = this.getSubmenus();
    for (var i in submenus) {
        submenus[i].clickEvent.subscribe(onMenuBarClick);
    }
}

function init_task_edit_menu(prefix) {
    var items = [
        { text: 'Main', value: ['main', prefix] },
        { text: 'Assignment', submenu: { id: 'assignment', itemdata: [
                { text: 'By User', value: ['assign_by_user', prefix], checked: menuCheckArray[0] },
                { text: 'By Variable', value: ['assign_by_var', prefix], checked: menuCheckArray[1] }
            ]}
        },
        { text: 'Notifications', submenu: { id: 'notifications', itemdata: [
                { text: 'On Assignment', value: ['notify_on_assign', prefix], checked: menuCheckArray[2] },
                { text: 'On Completion', value: ['notify_on_complete', prefix], checked: menuCheckArray[3] },
                { text: 'Reminders', value: ['notify_reminders', prefix], checked: menuCheckArray[4] }
            ]}
        }
    ];

    oMenuBar = new YAHOO.widget.MenuBar(prefix + 'menubar', { lazyload: true, itemdata: items });

    oMenuBar.renderEvent.subscribe(onMenuBarRender);
    oMenuBar.render(document.getElementById(prefix + 'navbar'));
}

function uninit_task_edit_menu(prefix) {
    var div = document.getElementById(prefix + 'navbar');
    var MenuManager = YAHOO.widget.MenuManager;

    var submenus = oMenuBar.getSubmenus();
    for (var i in submenus) {
        MenuManager.removeMenu(submenus[i]);
        delete submenus[i];
    }

    div.removeChild(document.getElementById(prefix + 'menubar'));
    MenuManager.removeMenu(oMenuBar);
    delete oMenuBar;
}




//
// AJAX Functions
/////////////////////////////////



//function called when a new task is created to save it into the database
var SaveNewTask = {
    handleSuccess:function(o) {
        if(o.responseXML !== undefined) {
            var root    = o.responseXML.documentElement;
            var taskid  = root.getElementsByTagName('taskid')[0].firstChild.nodeValue;

            document.getElementById('nf_progress_bar').style.display = 'none';
            add_task(taskid, SaveNewTaskVars[0], SaveNewTaskVars[1], SaveNewTaskVars[2], SaveNewTaskVars[3]);
        }
    },

    handleFailure:function(o) {
    },

    request:function(type, offsetLeft, offsetTop, isInteractive) {
        document.getElementById('nf_progress_bar').style.display = '';

        SaveNewTaskVars[0] = type;
        SaveNewTaskVars[1] = offsetLeft;
        SaveNewTaskVars[2] = offsetTop;
        SaveNewTaskVars[3] = isInteractive;

        var postData = 'op=save_new_task&templateid=' + templateid + '&steptype=' + type + '&offsetleft=' + offsetLeft + '&offsettop=' + offsetTop;
        YAHOO.util.Connect.asyncRequest('POST', ajax_action_url + 'edit_ajax_update.php', SaveNewTaskCallback, postData);
    }
}

var SaveNewTaskVars = [];

var SaveNewTaskCallback = {
    success:SaveNewTask.handleSuccess,
    failure:SaveNewTask.handleFailure,
    timeout:30000,
    scope: SaveNewTask
};



//function called 'on drop' after a drag and drop
var SaveTaskPosition = {
    handleSuccess:function(o) {
        document.getElementById('nf_progress_bar').style.display = 'none';
    },

    handleFailure:function(o) {
    },

    request:function(task) {
        document.getElementById('nf_progress_bar').style.display = '';

        var taskid = task.id.substr(4);

        var postData = 'op=save_task_position&taskid=' + taskid + '&offsetleft=' + task.offsetLeft + '&offsettop=' + task.offsetTop;
        YAHOO.util.Connect.asyncRequest('POST', ajax_action_url + 'edit_ajax_update.php', SaveTaskPositionCallback, postData);
    }
}

var SaveTaskPositionCallback = {
    success:SaveTaskPosition.handleSuccess,
    failure:SaveTaskPosition.handleFailure,
    timeout:30000,
    scope: SaveTaskPosition
};



//function called after second click on draw line
var SaveTaskLines = {
    handleSuccess:function(o) {
        document.getElementById('nf_progress_bar').style.display = 'none';
    },

    handleFailure:function(o) {
    },

    request:function(start, end, type) {
        document.getElementById('nf_progress_bar').style.display = '';

        var startid = start.id.substr(4);
        var endid = end.id.substr(4);
        var typevalue = (type) ? 1:0;

        var postData = 'op=save_task_lines&startid=' + startid + '&endid=' + endid + '&type=' + typevalue;
        YAHOO.util.Connect.asyncRequest('POST', ajax_action_url + 'edit_ajax_update.php', SaveTaskLinesCallback, postData);
    }
}

var SaveTaskLinesCallback = {
    success:SaveTaskLines.handleSuccess,
    failure:SaveTaskLines.handleFailure,
    timeout:30000,
    scope: SaveTaskLines
};



//function called after second click on draw line
var ClearTaskLines = {
    handleSuccess:function(o) {
        document.getElementById('nf_progress_bar').style.display = 'none';
    },

    handleFailure:function(o) {
    },

    request:function(task) {
        document.getElementById('nf_progress_bar').style.display = '';

        var taskid = task.id.substr(4);

        var postData = 'op=clear_task_lines&taskid=' + taskid;
        YAHOO.util.Connect.asyncRequest('POST', ajax_action_url + 'edit_ajax_update.php', ClearTaskLinesCallback, postData);
    }
}

var ClearTaskLinesCallback = {
    success:ClearTaskLines.handleSuccess,
    failure:ClearTaskLines.handleFailure,
    timeout:30000,
    scope: ClearTaskLines
};



//function called on task delete
var DeleteTask = {
    handleSuccess:function(o) {
        document.getElementById('nf_progress_bar').style.display = 'none';
    },

    handleFailure:function(o) {
    },

    request:function(task) {
        document.getElementById('nf_progress_bar').style.display = '';

        var taskid = task.id.substr(4);

        var postData = 'op=delete_task&taskid=' + taskid;
        YAHOO.util.Connect.asyncRequest('POST', ajax_action_url + 'edit_ajax_update.php', DeleteTaskCallback, postData);
    }
}

var DeleteTaskCallback = {
    success:DeleteTask.handleSuccess,
    failure:DeleteTask.handleFailure,
    timeout:30000,
    scope: DeleteTask
};

//function called on panel create
var GetPanelForm = {
    handleSuccess:function(o) {
        document.getElementById('nf_progress_bar').style.display = 'none';

        var root                = o.responseXML.documentElement;
        var prefix              = root.getElementsByTagName('prefix')[0].firstChild.nodeValue;
        var isInteractive       = root.getElementsByTagName('isinteractive')[0].firstChild.nodeValue;
        if (isInteractive == 1) {
            var assignedByVariable  = root.getElementsByTagName('assignedbyvar')[0].firstChild.nodeValue;
            var prenotifyFlag       = (root.getElementsByTagName('prenotifyflag')[0].firstChild.nodeValue == 1) ? true:false;
            var postnotifyFlag      = (root.getElementsByTagName('postnotifyflag')[0].firstChild.nodeValue == 1) ? true:false;
            var reminderFlag        = (root.getElementsByTagName('reminderflag')[0].firstChild.nodeValue == 1) ? true:false;
        }
        var ohtml               = root.getElementsByTagName('html');
        var html = '';
        var i = 0;
        while (ohtml[0].childNodes[i] != null) {
            html += ohtml[0].childNodes[i++].nodeValue;
        }
        menuCheckArray = [];

        if (assignedByVariable == 1) {
            menuCheckArray[0] = false;
            menuCheckArray[1] = true;
        }
        else {
            menuCheckArray[0] = true;
            menuCheckArray[1] = false;
        }

        menuCheckArray[2] = prenotifyFlag;
        menuCheckArray[3] = postnotifyFlag;
        menuCheckArray[4] = reminderFlag;

        document.getElementById(prefix + 'body').innerHTML = html;
        edit_task(GetPanelFormVars[0]);

        document.getElementById(prefix + 'tasktitle').innerHTML = document.getElementById(prefix + 'frm_mainEdit').taskName.value;

        if (prefix == 'spv_') {
            updateFieldList(document.getElementById('formValue').value, document.getElementById('fieldValueCpy').value);
        }
    },

    handleFailure:function(o) {
    },

    request:function(task) {
        document.getElementById('nf_progress_bar').style.display = '';

        var taskid = task.substr(4);
        GetPanelFormVars[0] = task;

        var postData = 'op=get_panel_form&taskid=' + taskid;
        YAHOO.util.Connect.asyncRequest('POST', ajax_action_url + 'edit_ajax_update.php', GetPanelFormCallback, postData);
    }
}

var GetPanelFormVars = [];

var GetPanelFormCallback = {
    success:GetPanelForm.handleSuccess,
    failure:GetPanelForm.handleFailure,
    timeout:30000,
    scope: GetPanelForm
};

//function called on task edit save
var SaveEditChanges = {
    handleSuccess:function(o) {
        document.getElementById('nf_progress_bar').style.display = 'none';
        //reset the task div for the next edit

        var root    = o.responseXML.documentElement;
        var op      = root.getElementsByTagName('op')[0].firstChild.nodeValue;
        var taskid  = root.getElementsByTagName('taskid')[0].firstChild.nodeValue;
        var retval  = root.getElementsByTagName('retval')[0].firstChild.nodeValue;

        switch (op) {
        case 'assign_by_user':
        case 'assign_by_variable':
            document.getElementById('task' + taskid + '_assignment').innerHTML = retval;
            break;

        case 'save_task':
            var taskname = retval;
            if (taskname.length > 25) {
                taskname = taskname.substr(0, 25) + '...';
            }

            document.getElementById('task' + taskid + '_title').innerHTML = taskname;
            document.getElementById('task' + taskid + '_title').title = retval;
            break;
        }
    },

    handleFailure:function(o) {
    },

    request:function(formObject) {
        var i;
        document.getElementById('nf_progress_bar').style.display = '';

        //finish_task_edit('task' + formObject.taskid.value);
        switch (formObject.op.value) {
        case 'assign_by_user':
            for (i = 0; i < formObject.assignedUsers.options.length; i++) {
                formObject.assignedUsers.options[i].selected = true;
            }
            formObject.assignedUsers.name += '[]';
            break;

        case 'assign_by_variable':
        case 'notify_on_assign':
        case 'notify_on_complete':
        case 'notify_reminders':
            for (i = 0; i < formObject.assignedVariables.options.length; i++) {
                formObject.assignedVariables.options[i].selected = true;
            }
            formObject.assignedVariables.name += '[]';
            break;
        }

        YAHOO.util.Connect.setForm(formObject, true);
        YAHOO.util.Connect.asyncRequest('POST', ajax_action_url + 'edit_ajax_update.php', SaveEditChangesCallback);
    }
}

var SaveEditChangesCallback = {
    upload:SaveEditChanges.handleSuccess,
    failure:SaveEditChanges.handleFailure,
    timeout:30000,
    scope: SaveEditChanges
};

