
var init_nexflow = function() {
    var dd1, dd2, dd3, resize;


    YAHOO.example.DDRegion = function(id, sGroup, config) {
        this.cont = config.cont;
        YAHOO.example.DDRegion.superclass.constructor.apply(this, arguments);
    };

    YAHOO.extend(YAHOO.example.DDRegion, YAHOO.util.DD, {
        cont: null,
        init: function() {
            //Call the parent's init method
            YAHOO.example.DDRegion.superclass.init.apply(this, arguments);
        },
        onDrag: function (e) {
            if (document.frm_animate.animateFlag.checked) {
                var el = this.getEl();

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
        },
        startDrag: function (e) {
            var el = this.getEl();
            el.style.zIndex = 2;
        },
        endDrag: function (e) {
            var el = this.getEl();
            el.style.zIndex = 1;

            document.getElementById(el.id + '_left').value = el.offsetLeft;
            document.getElementById(el.id + '_top').value = el.offsetTop;

            //update transactions involving el with the new coords
            for (var i in lines) {
                if (lines[i] != null && lines[i][8] != null) {
                    if (lines[i][5] == el || lines[i][6] == el) {
                        lines[i][8].clear();
                        lines[i] = connect_tasks(lines[i][5], lines[i][6], lines[i][7], lines[i][8]);
                    }
                }
            }

            SaveTaskPosition.request(el);
        }
    });

    Event.onDOMReady(function() {
        init_panel('mw_', 1);
        init_panel('and_', 0);
        init_panel('bat_', 0);
        init_panel('if_', 0);
        init_panel('bf_', 0);
        init_panel('int_', 1);
        init_panel('nfm_', 1);
        init_panel('spv_', 0);

        var length = existing_tasks.length;
        var cnt = 0;

        for (var i in existing_tasks) {
            if (++cnt > length) {
                break;
            }
            var dragdrop = [];
            dragdrop[0] = existing_tasks[i][0];
            dragdrop[1] = new YAHOO.example.DDRegion(existing_tasks[i][0], '', { cont: 'workflow_container' });
            dragdrop[1].setHandleElId(existing_tasks[i][0] + '_handle');

            var el = document.getElementById(existing_tasks[i][0]);
            var attributes = {
                points: { to: [existing_tasks[i][1], existing_tasks[i][2]]}
            };
            var ani = new YAHOO.util.Motion(el, attributes);
            ani.duration = 1;
            ani.method = YAHOO.util.Easing.backOut;
            ani.animate();

            dragdrop[2] = init_task_context_menu(existing_tasks[i][0]);

            dd.push(dragdrop);
        }

        ani.onComplete.subscribe(function () {
            initialize_lines();
        });

        init_context_menu();
    });

    function init_panel(prefix, isInteractive) {

        YAHOO.namespace("example.container");

        // Instantiate a Panel from markup
        var panel = new YAHOO.widget.Panel(prefix + 'template',
            {
                width:"500px",
                fixedcenter: true,
                close: true,
                dragable: false,
                zindex: 5,
                modal: true,
                visible:false
            }
        );
        if (isInteractive == 1) {
            panel.showEvent.subscribe(function() {
                init_task_edit_menu(prefix);
            });
            panel.hideEvent.subscribe(function() {
                uninit_task_edit_menu(prefix);
                show_panel_tab('main', prefix);
            });
        }
        panel.render();
        panels.push(panel);
    }

};

