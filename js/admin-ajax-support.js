var xmlhttp = null;

// Template Variable Admin Ajax Support Functions 
function ajaxUpdateHandler(op,id) {
    var validcmd = false;
    var message = '';
    if (op == 'editHandler') {
        var v1 = eval('document.fhandlers.handler'+id);
        v1.readOnly=false;
        v1.focus();
        document.getElementById('edithandler'+id).style.display='none';
        document.getElementById('updatehandler'+id).style.display='';
        document.getElementById('vhdesc'+id).style.display='none';
        document.getElementById('ehdesc'+id).style.display='';
    } else if (op == 'update') {
        validcmd = true;
        message = 'Updating Handler ...';
        var v1 = eval('document.fhandlers.handler'+id);
        var v2 = eval('document.fhandlers.desc'+id);
        if (v2.value.indexOf("\n") != -1) {
            var regexCRLF = new RegExp( '\n', 'g' )
            desc = v2.value.replace(regexCRLF,'<br />');
        } else {
            desc = v2.value;
        }
        document.getElementById('vhdesc'+id).innerHTML = desc;
        document.getElementById('vhdesc'+id).style.display='';
        document.getElementById('ehdesc'+id).style.display='none';

    } else if (op == 'cancel') {
        var v1 = eval('document.fhandlers.handler'+id);
        v1.readOnly=true;
        v1.focus();
        document.getElementById('edithandler'+id).style.display='';
        document.getElementById('updatehandler'+id).style.display='none';
        document.getElementById('vhdesc'+id).style.display='';
        document.getElementById('ehdesc'+id).style.display='none';
    }

    if (validcmd) {

        var fa = document.getElementById('fieldaction'+id);
        var fs = document.getElementById('fieldstatus'+id);
        fa.style.display='none';
        fs.style.display='';
        fs.firstChild.nodeValue=message;

        xmlhttp = new XMLHttpRequest();
        var qs = '?op='+op+'&rec=' + id +
            '&handler=' + v1.value;
        if (v2) {
            qs = qs + '&description='+ desc;
        }
        xmlhttp.open('GET', 'ajaxupdate_handlers.php' + qs, true);
        xmlhttp.onreadystatechange = function() {
           if (xmlhttp.readyState == 4) {
                receiveRequestUpdateHandlers(xmlhttp.responseXML);
              }
       };
        xmlhttp.send(null);
    }

}

function receiveRequestUpdateHandlers(dom) {
    var orec = dom.getElementsByTagName('record');
    var rec = orec[0].firstChild.nodeValue
    //alert ('Return record is: ' + rec);
    var oop = dom.getElementsByTagName('operation');
    var ov1 = dom.getElementsByTagName('value1');
    var op = oop[0].firstChild.nodeValue;

    // Restore Edit/Delete links and Hide Save/Cancel Buttons
    document.getElementById('edithandler'+rec).style.display='';
    document.getElementById('updatehandler'+rec).style.display='none';

    // Remove updating... status and show buttons
    var fa = document.getElementById('fieldaction'+rec);
    var fs = document.getElementById('fieldstatus'+rec);
    fs.firstChild.nodeValue='&nbsp;';
    fa.style.display='';
    fs.style.display='none';

}



// Template Variable Admin Ajax Support Functions 


function ajaxUpdateTemplateVar(op,tid,id,fid) {

    var validcmd = false;
    var message = '';
    if (op == 'add') {
        validcmd = true;
        message = 'Adding Variable ...';
        var v1 = eval('document.tvars'+id+'.addvarname'+id);
        var v2 = eval('document.tvars'+id+'.addvarvalue'+id);
        //alert ('Add variable: ' + v1.value+' with a value of: '+v2.value);

    } else if (op == 'edit') {
        var v1 = eval('document.tvars'+id+'.var'+fid+'_value');
        v1.readOnly=false;
        v1.focus();
        document.getElementById('editlabel_t'+id+'_f'+fid).style.display='none';
        document.getElementById('updatelabel_t'+id+'_f'+fid).style.display='';

        var v2 = eval('document.tvars'+id+'.var'+fid+'_value');
        //alert ('Edit  variable: ' + v1.value+' with a value of: '+v2.value);

    } else if (op == 'update') {
        validcmd = true;
        message = 'Updating Variable ...';
        var v1 = eval('document.tvars'+id+'.var'+fid+'_name');
        var v2 = eval('document.tvars'+id+'.var'+fid+'_value');

        //alert ('Save  variable: ' + v1.value+' with a value of: '+v2.value);

    } else if (op == 'cancel') {
        var v1 = eval('document.tvars'+id+'.var'+fid+'_value');
        v1.readOnly=true;
        v1.focus();
        document.getElementById('editlabel_t'+id+'_f'+fid).style.display='';
        document.getElementById('updatelabel_t'+id+'_f'+fid).style.display='none';

    } else if (op == 'delete') {
        validcmd = true;
        message = 'Deleting Variable ...';
        var v1 = eval('document.tvars'+id+'.var'+fid+'_name');
        var v2 = eval('document.tvars'+id+'.var'+fid+'_value');
        //alert ('Delete  variable: ' + v1.value+' with a value of: '+v2.value);

    } else if (op == 'editTemplateName') {
        var v1 = eval('document.tform_'+id+'.templateName');
        v1.readOnly=false;
        v1.focus();
        document.getElementById('editname_'+tid).style.display='none';
        document.getElementById('updatename_'+tid).style.display='';
        //var v2 = eval('document.tvars'+id+'.var'+fid+'_value');

    } else if (op == 'updateTemplateName') {
        validcmd = true;
        message = 'Updating Template ...';
        var v1 = eval('document.tform_'+id+'.templateName');
        v1.readOnly=true;
        v1.focus();
        var v1 = 'templateName';  // Value we want to send remote script
        var v2 = eval('document.tform_'+id+'.templateName');
        //alert ('Save  variable: ' + v1.value);

    } else if (op == 'cancelTemplateName') {
        var v1 = eval('document.tform_'+id+'.templateName');
        v1.readOnly=true;
        v1.focus();
        document.getElementById('editname_'+tid).style.display='';
        document.getElementById('updatename_'+tid).style.display='none';

    } else if (op == 'useProject') {
        validcmd=true;
        message = 'Updating Tracking Entry...';
        var v1 = eval('document.tform_'+id+'.templateName');
        v1.readOnly=true;
        v1.focus();
        document.getElementById('editname_'+tid).style.display='';
        document.getElementById('updatename_'+tid).style.display='none';
    } else if (op == 'updateApplicationGroup') {
        validcmd=true;
        message = 'Updating Application Group...';
        var v1 = eval('document.tform_'+id+'.templateName');
        v1.readOnly=true;
        v1.focus();
        var v2 = eval('document.tform_'+id+'.needApp'+tid);
        //document.getElementById('editname_'+tid).style.display='';
        //document.getElementById('updatename_'+tid).style.display='none';
    }

    if (validcmd) {

        var fa = document.getElementById('fieldaction_'+tid);
        var fs = document.getElementById('fieldstatus_'+tid);
        fa.style.display='none';
        fs.style.display='';
        fs.firstChild.nodeValue=message;

        xmlhttp = new XMLHttpRequest();
        var qs = '?op='+op+'&rec=' + tid +
            '&variable=' + v1.value;
        if (v2) {
            qs = qs + '&value='+ v2.value;
        }
        xmlhttp.open('GET', 'ajaxupdate_tvars.php' + qs, true);
        xmlhttp.onreadystatechange = function() {
          if (xmlhttp.readyState == 4) {
            receiveRequestUpdateTemplateVar(xmlhttp.responseXML);
          }
        };
        xmlhttp.send(null);
    }
}


function receiveRequestUpdateTemplateVar(dom) {
    var orec = dom.getElementsByTagName('record');
    var rec = orec[0].firstChild.nodeValue
    //alert ('Return record is: ' + rec);
    var oop = dom.getElementsByTagName('operation');
    var ov1 = dom.getElementsByTagName('value1');
    var op = oop[0].firstChild.nodeValue;

    // Remove updating... status and show buttons
    var fa = document.getElementById('fieldaction_'+rec);
    var fs = document.getElementById('fieldstatus_'+rec);
    fs.firstChild.nodeValue='&nbsp;';
    fa.style.display='';
    fs.style.display='none';

    // Un-Hide the "Add Variable" Link for this template
    var ov2 = dom.getElementsByTagName('value2');
    document.getElementById('addvarlabel'+ov2[0].firstChild.nodeValue).style.display = '';

    // Check if template name was updated - if so update the screen HTML to display it
    if (op =='useProject'){
        //trapping this state condition in the event we want to display any information regarding the use of project tracking
        //for nexform flows
    
    } else if (op == 'updateTemplateName') {
        var tname = document.getElementById('tname'+rec);
        document.getElementById('editname_'+rec).style.display='';
        document.getElementById('updatename_'+rec).style.display='none';

        if (ov1[0].firstChild) {
            tname.innerHTML = ov1[0].firstChild.nodeValue;
        } else {
            tname.innerHTML = '';
        }

    } else if (op == 'updateApplicationGroup') {
        
        //trapping this state condition

    }else {
        // Get HTML content returned and updated displayed Template Variables
        var obj = document.getElementById('tvar_container'+rec);
        if (obj.parentNode) {
            // Check for 3 chunks of data returning - look into using the DOM normalize method
            html = ov1[0].childNodes[0].nodeValue;
            if (ov1[0].childNodes[1]) {
                html = html + ov1[0].childNodes[1].nodeValue;
            }
            if (ov1[0].childNodes[2]) {
                html = html + ov1[0].childNodes[2].nodeValue;
            }
            obj.parentNode.innerHTML = html;
        }
    }
}


// Template Task Admin Ajax Support Functions 
function ajaxUpdateTask(op,id) {
    var validcmd = false;
    var message = '';
    var v1 = eval('document.template.templateTaskID');
   
    
    //alert('op:' + op + ' taskid: ' + v1.value);
    
    switch (op) {
    
     case 'setAssignmentType':
            v2 = document.getElementsByName('taskassigntype');
            var mode = '';
            for (i = 0; i < v2.length; i++) {
                if (v2[i].checked) mode = v2[i].value;
            }     
            parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=' + mode;
            validcmd = true;                          
            break;
     case 'setRegenerateOption':
            v2 = document.getElementById('chkregenerate');
            if (v2.checked) {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=1';
            } else {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=0';
            }
            validcmd = true;             
            break;    
    case 'setRegenerateAllOption':
            v2 = document.getElementById('chkregenerateAllLive');
            if (v2.checked) {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=1';
            } else {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=0';
            }
            validcmd = true;             
            break;                
    case 'setDynamicForm':
            v2 = document.getElementById('isDynamicForm');
            if (v2.checked) {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=1';
            } else {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=0';
            }
            validcmd = true;             
            break;        
    case 'setDynamicFormVariable':
            v2 = document.getElementById('dynamicFormVariableSelector');
            if (v2.checked) {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=' + v2.value;
            } else {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode='+ v2.value;
            }
            validcmd = true;             
            break;                            
    case 'setDynamicName':
            v2 = document.getElementById('isDynamicName');
            if (v2.checked) {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=1';
            } else {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=0';
            }
            validcmd = true;             
            break;        
    case 'setDynamicNameVariable':
            v2 = document.getElementById('dynamicNameVariableSelector');
            if (v2.checked) {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode=' + v2.value;
            } else {
                parmstring = '?op=' + op + '&taskid=' + v1.value + '&mode='+ v2.value;
            }
            validcmd = true;             
            break;                            
            
            
            
     case 'addAssignVar': 
            var v2 = eval('document.template.task_availableVariables');
            break;
     case 'delAssignVar': 
            var v2 = eval('document.template.task_assignedVariables');
            break;
     case 'addAssignUser': 
            var v2 = eval('document.template.task_availableUsers');
            break;
     case 'delAssignUser': 
            var v2 = eval('document.template.task_assignedUsers');
            break;
     case 'addPreNotifyVariable': 
            var v2 = eval('document.template.task_prenotifyVariables');
            break;
     case 'delPreNotifyVariable': 
            var v2 = eval('document.template.task_prenotify');
            break;
     case 'addPostNotifyVariable': 
            var v2 = eval('document.template.task_postnotifyVariables');
            break;
     case 'delPostNotifyVariable': 
            var v2 = eval('document.template.task_postnotify');
            break;
     case 'addReminderNotifyVariable': 
            var v2 = eval('document.template.task_remindernotifyVariables');
            break;
     case 'delReminderNotifyVariable': 
            var v2 = eval('document.template.task_remindernotify');
            break;
     case 'setReminderNotifyVariable': 
            var v2 = eval('document.template.selnotifyinterval');
            break;
     case 'setSubsequentReminderVariable': 
            var v2 = eval('document.template.selsubsequentinterval');
            break;            
     case 'updatePreNotifyMessage': 
            var v2 = eval('document.template.prenotify_message');
            var regexCRLF = new RegExp( '\n', 'g' )
            parm1 = v2.value.replace(regexCRLF,'<br />');
            parmstring = '?op=' + op + '&taskid=' + v1.value + '&message=' + v2.value;
            validcmd = true;                        
            break;              
     case 'updatePostNotifyMessage': 
            var v2 = eval('document.template.postnotify_message');
            var regexCRLF = new RegExp( '\n', 'g' )
            parm1 = v2.value.replace(regexCRLF,'<br />');
            parmstring = '?op=' + op + '&taskid=' + v1.value + '&message=' + v2.value;
            validcmd = true;                        
            break;
     case 'updateReminderMessage': 
            var v2 = eval('document.template.reminder_message');
            var regexCRLF = new RegExp( '\n', 'g' )
            parm1 = v2.value.replace(regexCRLF,'<br />');
            parmstring = '?op=' + op + '&taskid=' + v1.value + '&message=' + v2.value;
            validcmd = true;                        
            break;                                                                                                                   
    }
   
    if (v2.selectedIndex >= 0) {
        v2.value = v2.options[v2.selectedIndex].value;
        parmstring = '?op=' + op + '&taskid=' + v1.value + '&parm1=' + v2.value;
        validcmd = true;
    }
    
    //alert('op:' + op + ' taskid: ' + v1.value + ' value: ' + v2.value);
    if (validcmd) {
        var fa = document.getElementById('tskaction');
        var fs = document.getElementById('tskstatus');         
        fa.style.display='none';
        fs.style.display='';
        fs.firstChild.nodeValue = 'Updating Task ' + v1.value + ' ....';
        xmlhttp = new XMLHttpRequest();
        //alert('ajaxupdate_tasks.php' + parmstring);         
        xmlhttp.open('GET', 'ajaxupdate_tasks.php' + parmstring, true);
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4) {
                receiveRequestUpdateTask(xmlhttp.responseXML);
            }
       };
       xmlhttp.send(null);
    }

}

function receiveRequestUpdateTask(dom) {
    // get object handles
    var orec = dom.getElementsByTagName('record');
    var oop = dom.getElementsByTagName('operation');
    var ov1 = dom.getElementsByTagName('value1');
    
    // get required values
    var rec = orec[0].firstChild.nodeValue;     
    var op = oop[0].firstChild.nodeValue;

    var fa = document.getElementById('tskaction');       
    fa.style.display='';    
    var fs = document.getElementById('tskstatus');
    fs.style.display='none';
    fs.firstChild.nodeValue = '';    
    
    //alert('Add variable: ' + rec);
    // Check for 3 chunks of data returning - look into using the DOM normalize method
    html = '';
    if (ov1[0].childNodes[0]) {     
        html = ov1[0].childNodes[0].nodeValue;
    }
    if (ov1[0].childNodes[1]) {
        html = html + ov1[0].childNodes[1].nodeValue;
    }
    if (ov1[0].childNodes[2]) {
        html = html + ov1[0].childNodes[2].nodeValue;
    }     
    
    if (op ==  'addAssignVar' || op ==  'delAssignVar') {
        var obj = document.getElementById('selvariableassignment');
        if (obj.parentNode && html != '') {                
            obj.parentNode.innerHTML = html;   
        }
    } else if (op ==  'addAssignUser' || op ==  'delAssignUser') {
        var obj = document.getElementById('seluserassignment');
        if (obj.parentNode && html != '') {                
            obj.parentNode.innerHTML = html;   
        }
    } else if (op ==  'addPreNotifyVariable' || op ==  'delPreNotifyVariable') {
        var obj = document.getElementById('selprenotify');
        if (obj.parentNode && html != '') {                
            obj.parentNode.innerHTML = html;   
        }
    } else if (op ==  'addPostNotifyVariable' || op ==  'delPostNotifyVariable') {
        var obj = document.getElementById('selpostnotify');
        if (obj.parentNode && html != '') {                
            obj.parentNode.innerHTML = html;   
        }
    } else if (op ==  'addReminderNotifyVariable' || op ==  'delReminderNotifyVariable') {
        var obj = document.getElementById('selremindernotify');
        if (obj.parentNode && html != '') {                
            obj.parentNode.innerHTML = html;   
        }        
        
    }
}

function exporttemplate(tid){
    var x;
    x=confirm('Export This template?');   
    if(x){
        window.open('export.php?templateid=' + tid);
    }else{
        return false;    
    }
    
}


function putTaskOnHOld(taskid){
    var url;
    xmlhttp = new XMLHttpRequest();
    url='ajaxupdate_tasks.php?op=onhold&taskid=' + taskid;
    xmlhttp.open('GET',url , true);
    xmlhttp.onreadystatechange = function() {
       if (xmlhttp.readyState == 4) {
            receiveHoldResults(xmlhttp.responseXML);
          }
    };
    xmlhttp.send(null);
}

function receiveHoldResults(dom){
    var orec = dom.getElementsByTagName('record');
    var oop = dom.getElementsByTagName('operation');
    var ov1 = dom.getElementsByTagName('value1');
    var rec = orec[0].firstChild.nodeValue;     
    var op = oop[0].firstChild.nodeValue;
    var val = ov1[0].firstChild.nodeValue;
    //alert(val);
    var x, src;
    x=document.getElementById('onholdimg');
    src=x.src;
    src=src.substring(0,src.lastIndexOf('/'));
    
    if(val=='onhold'){
        x.src=src + '/onhold2.png';
        x.title="ON HOLD";
    }else{
        x.src=src + '/onhold.png';
        x.title="NOT ON HOLD";
    }
}