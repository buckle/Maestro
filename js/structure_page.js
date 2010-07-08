
// Function attached to "Add Variable" Link on the template variable form.
    function addVariable(i) {
      document.getElementById('newtvar_container'+i).style.display = '';
      if (document.getElementById('vars' + i)) {
        document.getElementById('vars' + i).style.display = '';
      }
      document.getElementById('addvarlabel' + i).style.display = 'none';
    }

    // Function action attached to click event when user clicks on "Edit" Template
    // Reset all other template display areas and un-hide selected template
    function editTemplateAction() {
      if (window.event && window.event.srcElement)  {  // IE Method
        var id = window.event.srcElement.parentNode.id;
      }
      else {
        var id = this.id;
      }
      var templateid = id.split('_');
      document.getElementById('addtemplate').style.visibility = 'hidden';
      document.getElementById('tview' + templateid[1]).style.display = 'none';
      document.getElementById('tedit' + templateid[1]).style.display = '';

      // Hide any other template record details
      for (var i = 0; i < num_records; i++) {
        if (i != templateid[1]) {
          document.getElementById('tedit' + i).style.display = 'none';
          document.getElementById('tview' + i).style.display = '';
          document.getElementById('addvarlabel' + i).style.display = '';
          document.getElementById('newtvar_container' + i).style.display = 'none';
        }
      }
    }

    // Function action attached to click event when user clicks on "Cancel" button
    // Restore all form defaults
    function restoreAction() {
      document.getElementById('addtemplate').style.visibility = '';
      document.getElementById('newtemplate').style.display = 'none';

      // Hide any other template record details
      for (var i = 0; i < num_records; i++) {
        document.getElementById('tedit'+i).style.display = 'none';
        document.getElementById('tview'+i).style.display = '';
        document.getElementById('addvarlabel'+i).style.display = '';
        document.getElementById('newtvar_container'+i).style.display = 'none';
        if (document.getElementById('vars'+i)) {
          document.getElementById('vars'+i).style.display = 'none';
        }
      }
    }

    /* Locate all the template records and install listener for the edit action */
    function installListeners() {
      for (var i = 0; i < num_records; i++) {
        var element1 = document.getElementById('etemplate_' + i);
        var element2 = document.getElementById('tcancel_' + i);
        addEvent(element1, 'click', editTemplateAction, false);
        addEvent(element2, 'click', restoreAction, false);
      }
    }

    //addEvent(window, 'load', installListeners, false);

    // cross-browser event handling for IE5+, NS6+ and Mozilla/Gecko
    // By Scott Andrew
    function addEvent(elm, evType, fn, useCapture) {
      if (elm.addEventListener) {
        elm.addEventListener(evType, fn, useCapture);
        return true;
      }
      else if (elm.attachEvent) {
        var r = elm.attachEvent('on' + evType, fn);
        return r;
      }
      else {
        elm['on' + evType] = fn;
      }
    }