var xmlhttp = null;

// Workflow Task Console Ajax Support Functions
 if(document.cookie == '') {
    document.cookie = 'nfhasCookies=yes';
    if (document.cookie.indexOf('nfhasCookies=yes') != -1) nfhasCookies = true;
  }
  else nfhasCookies = true;
// sets a cookie in the browser.
  function nfSetCookie (name, value, hours, path) {
    if (nfhasCookies) {
  	  if(hours) {
  	    if ( (typeof(hours) == 'string') && Date.parse(hours) ) var numHours = hours;
  	    else if (typeof(hours) == 'number') var numHours = (new Date((new Date()).getTime() + hours*3600000)).toGMTString();
  	  }
  	  document.cookie = name + '=' + escape(value) + ((numHours)?(';expires=' + numHours):'') + ((path)?';path=' + path:'');
    }
  }



function ajaxViewProjectDetails(id,projectid,useruid,taskid) {

    obj = document.getElementById('taskdetail_rec'+id);    // Used if in the MyTasks Screen
    if (!obj) {
        obj = document.getElementById('wfdetail_rec'+id);       // Used if in the MyFlows or AllFlows Screens
    }
    if (obj.style.display == 'none') {
        document.getElementById('pstatus').style.display='';
        document.getElementById('pstatuscontent').innerHTML='Retrieving Project Detail for record: ' + id;
        xmlhttp = new XMLHttpRequest();
        var qs = '?op=display&id='+id+'&project_id=' + projectid + '&taskuser=' + useruid + '&taskid=' + taskid;
        //alert('ajaxlib.php' + qs);
        xmlhttp.open('GET', 'ajaxlib.php' + qs, true);
        xmlhttp.onreadystatechange = function() {
           if (xmlhttp.readyState == 4) {
                receiveRequestProjectDetails(xmlhttp.responseXML);
           }
       }
       xmlhttp.send(null);
    } else {
        obj.style.display='none';
    }
}

function ajaxViewProjectComments(id,projectid,useruid,taskid) {

    obj = document.getElementById('taskdetail_rec'+id);    // Used if in the MyTasks Screen
    if (!obj) {
        obj = document.getElementById('wfdetail_rec'+id);       // Used if in the MyFlows or AllFlows Screens
    }
    if (obj.style.display == 'none') {
        document.getElementById('pstatus').style.display='';
        document.getElementById('pstatuscontent').innerHTML='Retrieving Project Detail for record: ' + id;
        xmlhttp = new XMLHttpRequest();
        var qs = '?op=displaycomments&id='+id+'&project_id=' + projectid + '&taskuser=' + useruid + '&taskid=' + taskid;
        //alert('ajaxlib.php' + qs);
        xmlhttp.open('GET', 'ajaxlib.php' + qs, true);
        xmlhttp.onreadystatechange = function() {
           if (xmlhttp.readyState == 4) {
                receiveRequestProjectDetails(xmlhttp.responseXML);
           }
       }
       xmlhttp.send(null);
    } else {
        obj.style.display='none';
    }
}

function ajaxProjectComment(op,id,projectid,useruid,comment,xcid) {
    var v2=document.getElementById('newcomment' + projectid);

    if (v2.value.indexOf("\n") != -1) {
        var regexCRLF = new RegExp( '\n', 'g' )
        comment = v2.value.replace(regexCRLF,'<br />');
    } else {
        comment = v2.value;
    }
    xmlhttp = new XMLHttpRequest();
    var qs = '?op=' + op  + '&id=' + id + '&project_id=' + projectid +
        '&taskuser=' + useruid + '&comment=' + comment + '&cid=' + xcid;

    xmlhttp.open('GET', 'ajaxlib.php' + qs, true);
    xmlhttp.onreadystatechange = function() {
       if (xmlhttp.readyState == 4) {
            receiveRequestProjectDetails(xmlhttp.responseXML);
       }
   };
   xmlhttp.send(null);

}

/* Ajax function to call backend task but no need to have return callback function defined */
function ajaxStartTask(id) {
    xmlhttp = new XMLHttpRequest();
    var qs = '?op=starttask&taskid='+id;

    xmlhttp.open('GET', 'ajaxlib.php' + qs, true);
    xmlhttp.send(null);
}

function receiveRequestProjectDetails(dom) {
    var oRec = dom.getElementsByTagName('record');
    var rec = oRec[0].firstChild.nodeValue
    var oOperation = dom.getElementsByTagName('operation');
    var op = oOperation[0].firstChild.nodeValue
    var ohtml = dom.getElementsByTagName('html');

    // Link the new content and unhide it
    var htmlpnode = ohtml[0];
    var html = '';
    for (var i = 0; i < htmlpnode.childNodes.length; i++) {
        html += htmlpnode.childNodes[i].nodeValue;
    }

    var pobj = document.getElementById('projectdetail_rec'+rec);
    if (pobj.parentNode && html != '') {
        pobj.parentNode.innerHTML = html;
        document.getElementById('pstatus').style.display='none';
        document.getElementById('wfdetail_rec'+rec).style.display='';
    }

    var tobj = document.getElementById('taskdetail_rec'+rec);
    if (tobj) tobj.style.display='';

    if (op == 'addcomment' || op == 'delcomment') {
        obj = document.getElementById('projectCommentsOpen_rec'+rec);
        if (obj) obj.style.display='';

        obj = document.getElementById('projectCommentsClosed_rec'+rec);
        if (obj) obj.style.display='none';
    }
}


function ajaxUpdateTaskAssignment(id,projectid,useruid,taskid,variableid) {

    //alert('id:'+id+' projectid:'+projectid+' userid:'+useruid+' taskid:'+taskid);
    var obj = document.getElementById('outstandingTasksOpen_rec'+id);
    if (obj) {
        obj.scrollIntoView();
        xmlhttp = new XMLHttpRequest();
        var qs = '?op=setowner&id='+id+'&project_id=' + projectid + '&taskuser=' + useruid + '&taskid='+taskid + '&variableid='+variableid;
        xmlhttp.open('GET', 'ajaxlib.php' + qs, true);
        xmlhttp.onreadystatechange = function() {
           if (xmlhttp.readyState == 4) {
                receiveRequestTaskAssignment(xmlhttp.responseXML);
           }
       };
       xmlhttp.send(null);
    }
}

function receiveRequestTaskAssignment(dom) {
    var oRec = dom.getElementsByTagName('record');
    var rec = oRec[0].firstChild.nodeValue;

    var oOperation = dom.getElementsByTagName('operation');
    var op = oOperation[0].firstChild.nodeValue
    var ohtml = dom.getElementsByTagName('html');

    var htmlpnode = ohtml[0];
    var html = '';
    for (var i = 0; i < htmlpnode.childNodes.length; i++) {
        html += htmlpnode.childNodes[i].nodeValue;
    }

    var obj = document.getElementById('outstanding_task_rec'+rec);
    if (obj && html != '') {
        obj.innerHTML = html;
    }

}



function ajaxPutProcessOnHold(rowNumber, processID){
    xmlhttp = new XMLHttpRequest();
    var qs = '?op=holdprocess&taskid='+processID;
    //alert('ajaxlib.php' + qs);
    xmlhttp.open('GET', 'ajaxlib.php' + qs, true);
    xmlhttp.send(null);

    //change the icon from the task icon to the hold Icon
    var obj=document.getElementById('procIconImg'+rowNumber);
    if(obj.style.display=='none'){
        obj.style.display='';
    }else{
        obj.style.display='none';
    }

}


function ajaxPutOnHold(rowNumber, taskID){
    xmlhttp = new XMLHttpRequest();
    var qs = '?op=holdtask&taskid='+taskID;

    xmlhttp.open('GET', 'ajaxlib.php' + qs, true);
    xmlhttp.send(null);

    //change the icon from the task icon to the hold Icon
    var obj=document.getElementById('taskIconImg'+rowNumber);
    if(obj.src=="{imgset}/onhold.png"){
        obj.src="{imgset}/{task_icon}";
    }else{
        obj.src="{imgset}/onhold.png";
    }

}



function ajaxUpdateDeleteProject(projectid,id) {
    if (id > 0 && projectid > 0) {
        if (confirm('Do you really want to delete this project?')) {
            xmlhttp = new XMLHttpRequest();
            var qs = '?op=deleteproject&project_id=' + projectid+'&id='+id;
            xmlhttp.open('GET', 'ajaxlib.php' + qs, true);
            xmlhttp.onreadystatechange = function() {
               if (xmlhttp.readyState == 4) {
                    receiveRequestDeleteProject(xmlhttp.responseXML);
               }
           };
           xmlhttp.send(null);
        } else {
            return false;
        }
    }
}

function receiveRequestDeleteProject(dom) {
    var oRec = dom.getElementsByTagName('record');
    var ohtml = dom.getElementsByTagName('html');

    // Link the new content and unhide it
    html = '';
    var htmlpnode = ohtml[0];
    for (var i = 0; i < htmlpnode.childNodes.length; i++) {
        html += htmlpnode.childNodes[i].nodeValue;
    }
    if (html == '') {
        html = '<div class="pluginAlert" style="margin:5px 20px 5px 20px ;padding:10px;">Project has been deleted - refresh the page.</div>';
    }

    var rec = oRec[0].firstChild.nodeValue;
    if (rec > 0) {
        var obj = document.getElementById('projectdetail_rec'+rec);
        if (obj.parentNode) {
            obj.parentNode.innerHTML = html;
        }
    }
}

