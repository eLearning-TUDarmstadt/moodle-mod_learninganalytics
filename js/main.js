

function transposeDataTable(dataTable) {
//step 1: let us get what the columns would be
    var rows = []; //the row tip becomes the column header and the rest become
    for (var rowIdx = 0; rowIdx < dataTable.getNumberOfRows(); rowIdx++) {
        var rowData = [];
        for (var colIdx = 0; colIdx < dataTable.getNumberOfColumns(); colIdx++) {
            rowData.push(dataTable.getValue(rowIdx, colIdx));
        }
        rows.push(rowData);
    }
    var newTB = new google.visualization.DataTable();
    newTB.addColumn('string', dataTable.getColumnLabel(0));
    newTB.addRows(dataTable.getNumberOfColumns() - 1);
    var colIdx = 1;
    for (var idx = 0; idx < (dataTable.getNumberOfColumns() - 1); idx++) {
        var colLabel = dataTable.getColumnLabel(colIdx);
        newTB.setValue(idx, 0, colLabel);
        colIdx++;
    }
    for (var i = 0; i < rows.length; i++) {
        var rowData = rows[i];
        //console.log(rowData[0]);
        newTB.addColumn('number', rowData[0]); //assuming the first one is always a header
        var localRowIdx = 0;
        for (var j = 1; j < rowData.length; j++) {
            newTB.setValue(localRowIdx, (i + 1), rowData[j]);
            localRowIdx++;
        }
    }
    return newTB;
}

var modOverview = {
// http://visapi-gadgets.googlecode.com/svn/trunk/termcloud/doc.html
    drawDivGrid: function (divID) {
        var topActivities = '<div class="row"><div class="col-md-12"><div class="panel panel-info"><div class="panel-heading"><h3 class="panel-title">In den letzten 7 Tagen am h&auml;ufigsten gesehen:</h3></div><div class="panel-body" id="topActivities"></div></div></div></div>';
        html = topActivities;
        $('#' + divID).html(html);
        $('#topActivities').html(GUIcontroller.loader);
    },
    termCloud: function (divID, res) {
        if (res.length < 2) {
            $('#' + divID).html("<h3>Keine Aktivit&auml;ten!</h3>");
        }
        else {
            var data = new google.visualization.arrayToDataTable(res);
            data.removeColumns(2, 5);
            var tc = new TermCloud(document.getElementById(divID));
            //console.log(tc);
            tc.draw(data, {
                allowHtml: true
            });
            // Select Handler. Call the table's getSelection() method
            function selectHandler() {
                var dataraw = new google.visualization.arrayToDataTable(res);
                var selection = tc.getSelection();
                var row = selection[0].row; //console.log(dataraw);
                var activityName = dataraw.getValue(row, 0);
                var modid = dataraw.getValue(row, 4);
                var modinstance = dataraw.getValue(row, 5);
                var modname = dataraw.getValue(row, 6);
                var iconLink = dataraw.getValue(row, 2);
                //console.log(iconLink);
                //console.log(dataraw.getValue(selection[0].row, 4));
                //console.log(modid + " " + modinstance + " " + modname);
                if (dataraw.getValue(row, 4) !== null) {
                    GUIcontroller.plugin = {};
                    GUIcontroller.plugin.name = eval(modname);
                    GUIcontroller.plugin.modid = modid;
                    GUIcontroller.plugin.modinstance = modinstance;
                    GUIcontroller.plugin.modname = modname;
                    GUIcontroller.plugin.iconLink = iconLink;
                    GUIcontroller.header.iconLink = iconLink;
                    GUIcontroller.header.text = activityName;
                    GUIcontroller.navigation.disabled = '',
                            GUIcontroller.setNavigation("modOverview.draw('plugin')", "Zur&uuml;ck zur Kurs&uuml;bersicht");
                    GUIcontroller.draw();
                }
            }
            
            // Setup listener
            google.visualization.events.addListener(tc, 'select', selectHandler);
        }
    },
    table: function (divID, res) {
        var data = new google.visualization.arrayToDataTable(res);
        console.log(data);
        tree = new google.visualization.TreeMap(document.getElementById(divID));
        tree.draw(data, {
            //minColor: '#FFFF99',
            //midColor: '#FFB266',
            //maxColor: '#FF8000',
            headerHeight: 25,
            textStyle: {
                color: 'black',
                fontName: 'Helvetica',
                fontSize: 15,
                bold: true
            },
            //fontColor: 'black',
            maxDepth: 2,
            //showScale: true,
            height: 600,
            useWeightedAverageForAggregation: true
        });
        // Setup listener
        google.visualization.events.addListener(tree, 'select', selectHandler);
        // Select Handler. Call the table's getSelection() method
        function selectHandler() {
            var selection = tree.getSelection();
            var modid = data.getValue(selection[0].row, 4);
            var modinstance = data.getValue(selection[0].row, 5);
            var modname = data.getValue(selection[0].row, 6);
            console.log(data.getValue(selection[0].row, 4));
            //console.log(modid + " " + modinstance + " " + modname);
            if (data.getValue(selection[0].row, 4) !== null) {
                GUIcontroller.plugin.name = eval(modname);
                GUIcontroller.setNavigation("modOverview.draw('plugin')", "Zur&uuml;ck zur Kurs&uuml;bersicht");
                GUIcontroller.draw();
            }
        }
    }, draw: function (divID) {
        GUIcontroller.navigation.disabled = 'disabled';
        GUIcontroller.drawNavigation();
        GUIcontroller.header.iconLink = '';
        GUIcontroller.header.text = 'Kurs&uuml;bersicht';
        GUIcontroller.drawHeader();
        modOverview.drawDivGrid(divID);
        link = "/mod/learninganalytics/rest/rest.php/uniqueViews/" + GUIcontroller.course;
        GUIcontroller.loadData(link, function (data) {
            $("#" + divID).attr("style", "width: 75%;");
            modOverview.termCloud('topActivities', data);
        });
    }
};
var choice = {
    divGrid: {
        Row1: {
            col1: {
                id: 'choicePieChart',
                header: 'Ergebnisse - Diagramm',
                size: 4,
                REST: 'getParticipation'
            },
            col2: {
                id: 'choiceTable',
                header: 'Ergebnisse - Tabelle',
                size: 8,
                REST: 'getChoices'
            }
        },
        Row2: {
            col1: {
                id: 'choiceColumnChart',
                header: 'Ergebnisse - Diagramm',
                size: 12,
                REST: 'getParticipation'
            }
        }
    },
    drawDivGrid: function (divID) {
        GUIcontroller.drawDivGrid(divID, this.divGrid);
    },
    choiceColumnChart: function (divID, res) {
        GUIcontroller.drawColumnChart(divID, res);
    },
    choicePieChart: function (divID, res) {
        GUIcontroller.drawPieChart(divID, res);
    },
    choiceTable: function (divID, res) {
        google.load('visualization', '1.0', {'packages': ['controls']});
        var data = new google.visualization.DataTable(res);
        var view = new google.visualization.DataView(data);
        $('#' + divID).html('<div id="dashboard"><div class="row"><div class="col-md-8"></div><div class="col-md-4" id="rangeSlider"></div></div><div id="table"></div></div>');
        // Create a dashboard.
        var dashboard = new google.visualization.Dashboard(document.getElementById('dashboard'));
        // Create a range slider, passing some options
        var tableRangeSlider = new google.visualization.ControlWrapper({
            'controlType': 'CategoryFilter',
            'containerId': 'rangeSlider',
            'options': {'filterColumnLabel': 'Abstimmung',
                'ui': {
                    'label': 'Abstimmung',
                    //'labelStacking': 'vertical',
                    'allowTyping': false,
                    'allowMultiple': true
                }
            }
        });
        var table = new google.visualization.ChartWrapper({
            chartType: 'Table',
            containerId: 'table',
            options: {
                showRowNumber: false,
                //page: 'disable',
                //pageSize: 50,
                allowHtml: true,
                //sortColumn: 1,
                //sortAscending: true
            }
        });
        dashboard.bind([tableRangeSlider], table);
        // Draw the dashboard.
        dashboard.draw(view);
    },
    draw: function (divID) {
        choice.drawDivGrid(divID);
        GUIcontroller.fillDivGridWithContent(divID, choice.divGrid, 'choice');
    }
};
var assign = {
    divGrid: {
        Row1: {
            col1: {
                id: 'pieChart',
                header: 'Kursbeteiligung',
                size: 5,
                REST: 'getDataForPieChart'
            },
            col2: {
                id: 'assignTable',
                header: 'Bewertungen',
                size: 7,
                REST: 'getTableWithGrades'
            }
        },
        Row2: {
            col1: {
                id: 'columnChart',
                header: 'Bewertungen',
                size: 12,
                REST: 'getGradesForColumnChart'
            }
        }
    },
    drawDivGrid: function (divID) {
        GUIcontroller.drawDivGrid(divID, assign.divGrid);
    },
    assignTable: function (divID, res) {
        if (res.rows.length < 1) {
            $('#' + divID).html("<h3>Keine Bewertungen!</h3>");
        }
        else {
            google.load('visualization', '1.0', {'packages': ['controls']});
            var data = new google.visualization.DataTable(res);
            var view = new google.visualization.DataView(data);
            $('#' + divID).html('<div id="dashboard"><div class="row"><div class="col-md-5"></div><div class="col-md-7" id="rangeSlider"></div></div><div id="table"></div></div>');
            // Create a dashboard.
            var dashboard = new google.visualization.Dashboard(document.getElementById('dashboard'));
            // Create a range slider, passing some options
            var tableRangeSlider = new google.visualization.ControlWrapper({
                'controlType': 'NumberRangeFilter',
                'containerId': 'rangeSlider',
                'options': {
                    'filterColumnLabel': 'Bewertung'
                }
            });
            var table = new google.visualization.ChartWrapper({
                chartType: 'Table',
                containerId: 'table',
                options: {
                    showRowNumber: false,
                    page: 'enable',
                    pageSize: 50,
                    allowHtml: true
                            //sortColumn: 0,
                            //sortAscending: false
                }
            });
            // Establish dependencies, declaring that 'filter' drives 'pieChart',
            // so that the pie chart will only display entries that are let through
            // given the chosen slider range.
            dashboard.bind([tableRangeSlider], table);
            // Draw the dashboard.
            dashboard.draw(view);
        }

    },
    columnChart: function (divID, res) {
        GUIcontroller.drawColumnChart(divID, res);
    },
    pieChart: function (divID, res) {
        GUIcontroller.drawPieChart(divID, res);
    },
    draw: function (divID) {
        this.drawDivGrid(divID);
        GUIcontroller.fillDivGridWithContent(divID, assign.divGrid, 'assign');
    }
}

var feedback = {
    divGrid: {
        Row1: {
            col1: {
                id: 'pieChart',
                header: 'Kursbeteiligung',
                size: 5,
                REST: 'getParticipation'
            },
            col2: {
                id: 'assignTable',
                header: 'Bewertungen',
                size: 7,
                REST: 'getTableWithGrades'
            }
        },
        Row2: {
            col1: {
                id: 'tableCompletedFeedback',
                header: 'Nutzer, die Feedback gaben',
                size: 6,
                REST: 'getUsersWithCompletedFeedback'
            },
            col2: {
                id: 'tableUncompletedFeedback',
                header: 'Nutzer, die bisher kein Feedback gaben',
                size: 6,
                REST: 'getUsersWithoutCompletedFeedback'
            }
        }
    },
    drawDivGrid: function (divID) {
        GUIcontroller.drawDivGrid(divID, feedback.divGrid);
    }, 
    columnChart: function (divID, res) {
        GUIcontroller.drawColumnChart(divID, res);
    },
    pieChart: function (divID, res) {
        GUIcontroller.drawPieChart(divID, res);
    },
    tableCompletedFeedback: function (divID, res) {
        GUIcontroller.drawTable(divID, res);
    },
    tableUncompletedFeedback: function (divID, res) {
        GUIcontroller.drawTable(divID, res);
    },
    draw: function (divID) {
        this.drawDivGrid(divID);
        GUIcontroller.fillDivGridWithContent(divID, feedback.divGrid, 'feedback');
    }
}

var forum = {
    drawDivGrid: function (divID) {
        var unseen = '<div class="row"><div class="col-md-12" id="unseen_frame"></div></div>';
        var forumMostViews = '<div class="row"><div class="col-md-12"><div class="panel panel-info"><div class="panel-heading"><h3 class="panel-title">Am h&auml;ufigsten gesehen</h3></div><div class="panel-body" id="forumMostViews"></div></div></div></div>';
        html = unseen + forumMostViews;
        $('#' + divID).html(html);
        $('#forumMostViews').html(GUIcontroller.loader);
    },
    termCloud: function (divID, res) {
        var data = new google.visualization.DataTable(res);
        var tc = new TermCloud(document.getElementById(divID));
        //console.log(tc);
        tc.draw(data, {
            allowHtml: true,
            target: '_blanc'
        });
    },
    table: function (divID, res) {
        if (res.rows.length != 0) {
            html = '<div class="panel panel-warning"><div class="panel-heading"><h3 class="panel-title">Die folgenden Diskussionen wurden weder von einem Tutor noch Assistenten oder Lehrenden gesehen:</h3></div><div class="panel-body" id="unseen"></div></div>';
            $('#unseen_frame').html(html);
            var data = new google.visualization.DataTable(res);
            var view = new google.visualization.DataView(data);
            //view.hideColumns([view.getNumberOfColumns() - 1]);
            var table = new google.visualization.Table(document.getElementById(divID));
            table.draw(view, {
                showRowNumber: false,
                allowHtml: true
            }
            );
            // Setup listener
            google.visualization.events.addListener(table, 'select', selectHandler);
            // Select Handler. Call the table's getSelection() method
            function selectHandler() {
                var selection = table.getSelection();
                var modName = data.getValue(selection[0].row, 8);
                GUIcontroller.plugin.name = eval(modName);
                GUIcontroller.setNavigation("modOverview.draw('plugin')", "Zur&uuml;ck zur Kurs&uuml;bersicht");
                GUIcontroller.draw();
            }
        }
    },
    draw: function (divID) {
        forum.drawDivGrid(divID);
        link = "/mod/learninganalytics/rest/rest.php/detailedModView/" + GUIcontroller.course + "/forum/" + GUIcontroller.plugin.modinstance;
        linkForTable = link + "/getUnseenForumTopics";
        GUIcontroller.loadData(linkForTable, function (data) {
            //console.log(data);
            forum.table('unseen', data);
        });
        linkForCloud = link + "/getTopicsAndViewsForCloud";
        GUIcontroller.loadData(linkForCloud, function (data) {
//console.log(data);
            forum.termCloud('forumMostViews', data);
        });
    }
};
var choicegroup = {
    divGrid: {
        Row1: {
            col1: {
                id: 'tngroups',
                header: 'Mehr Teilnehmer als Gruppenpl&auml;tze!',
                size: 12,
                REST: 'getSumOfUsersAndGroupLimits'
            }
        },
        Row2: {
            col1: {
                id: 'groupsoverloaded',
                header: 'Gruppenlimit &uuml;berschritten!',
                size: 12,
                REST: 'IsThereAGroupOverloaded'
            }
        },
        Row3: {
            col1: {
                id: 'choicegroupPieChart',
                header: 'Beteiligung',
                size: 6,
                REST: 'getPieChartData'
            },
            col2: {
                id: 'choicegroupTable',
                header: 'Gruppenauswahl',
                size: 6,
                REST: 'getTableWithAllGroupsInChoice'
            }
        }
    },
    drawDivGrid: function (divID) {
        GUIcontroller.drawDivGrid(divID, this.divGrid);
    },
    // Shows a warning banner if there are more users than places in groups
    tngroups: function (divID, res) {
        var usersTotal = res.usersTotal;
        var sumOfGroupLimitations = res.sumOfGroupLimitations;
        if (usersTotal > sumOfGroupLimitations) {
            var html = '<div class="alert alert-danger" role="alert"><strong>Warnung: Zu wenig Gruppenpl&auml;tze!</strong> Es gibt ' + usersTotal + ' Teilnehmer aber nur ' + sumOfGroupLimitations + ' Gruppenpl&auml;tze! </div>';
            $('#' + divID).html(html);
        }
        else {
            $('#' + divID).parent().parent().parent().remove();
        }
    },
    // Shows a warning if there are groups with more participants than allowed
    groupsoverloaded: function (divID, res) {
        var groupsOverloaded = res;
        if (groupsOverloaded) {
            var html = '<div class="alert alert-danger" role="alert"><strong>Warnung: Gruppenlimit &uuml;berschritten!</strong> In einer oder mehreren Gruppen gibt es mehr Teilnehmer als erlaubt!</div>';
            $('#' + divID).html(html);
        }
        else {
            $('#' + divID).parent().parent().parent().remove();
        }
    },
    choicegroupTable: function (divID, res) {
        var data = new google.visualization.DataTable(res);
        var view = new google.visualization.DataView(data); //view.hideColumns([view.getNumberOfColumns() - 1]);
        var table = new google.visualization.Table(document.getElementById(divID));
        table.draw(view, {
            showRowNumber: false,
            allowHtml: true
        }
        );
        // Setup listener
        google.visualization.events.addListener(table, 'select', selectHandler);
        // Select Handler. Call the table's getSelection() method
        function selectHandler() {
            var selection = table.getSelection();
            var modName = data.getValue(selection[0].row, 8);
            GUIcontroller.plugin.name = eval(modName);
            GUIcontroller.setNavigation("modOverview.draw('plugin')", "Zur&uuml;ck zur Kurs&uuml;bersicht");
            GUIcontroller.draw();
        }
    },
    choicegroupPieChart: function (divID, res) {
        GUIcontroller.drawPieChart(divID, res);
    },
    draw: function (divID) {
        choicegroup.drawDivGrid(divID);
        GUIcontroller.fillDivGridWithContent(divID, this.divGrid, 'choicegroup');
    }
};
var modUnknown = {draw: function (divID) {
        $('#' + divID).html('<h1>Modul ist noch nicht implementiert!</h1>');
    }
};
var GUIcontroller = {
    course: 0,
    parentDiv: "",
    header: {
        iconLink: '',
        text: 'Kurs'
    },
    navigation: {
        func: "",
        text: "Zur&uuml;ck zur Kurs&uuml;bersicht",
        // 'disabled' for disabled, '' for enabled
        disabled: "disabled"
    },
    plugin: {
        name: modOverview
    },
    footer: '',
    loader: "<img src='/mod/learninganalytics/pix/loader.gif' style='display: block; margin-left: auto; margin-right: auto;'></src>",
    draw: function () {
        if (!GUIcontroller.course || !GUIcontroller.parentDiv) {
            alert("missing Parameters: course, parentDiv or arrayForDivGrid");
        }
        else {
            $('#' + GUIcontroller.parentDiv).html(GUIcontroller.HTMLGrid());
            GUIcontroller.drawHeader();
            GUIcontroller.drawNavigation();
            if (typeof GUIcontroller.plugin.name.draw === 'undefined') {
                console.log(GUIcontroller.plugin.name);
                modUnknown.draw('plugin');
            }
            else {
                GUIcontroller.plugin.name.draw('plugin');
            }

        }
    },
    HTMLGrid: function () {
        var header = '<div class="row"><div class="col-md-12" id="header"></div></div>';
        var navigation = '<div class="row"><div class="col-md-12" id="navigation"></div></div>';
        var plugin = '<div class="row"><div class="col-md-12" id="plugin">' + GUIcontroller.loader + '</div></div>';
        var footer = '<div class="row"><div class="col-md-12" id="footer"></div></div>';
        return '<div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title" id="header"></h3></div><div class="panel-body">' + navigation + plugin + footer + '</div></div>';
    },
    fillDivGridWithContent: function (divID, divGrid, modName) {
        var link = "/mod/learninganalytics/rest/rest.php/detailedModView/" + GUIcontroller.course + "/" + modName + "/" + GUIcontroller.plugin.modinstance + "/";
        $.each(divGrid, function (rowName, rowData) {
            $.each(rowData, function (colName, colData) {
                GUIcontroller.loadData(link + colData.REST, function (data) {
                    window[modName][colData.id](colData.id, data);
                });
            });
        });
    },
    loadData: function (link, success) {
        $.ajax({
            url: link,
            type: 'GET',
            dataType: 'json'
        }).done(function (data) {
            success(data);
        });
    },
    /**
     * 
     */
    drawDivGrid: function (divID, grid) {
        var html = '';
        $.each(grid, function (rowName, rowData) {
            html += '<div class="row">\n';
            $.each(rowData, function (colName, colData) {
                html += '\t<div class="col-md-' + colData.size + '"><div class="panel panel-info"><div class="panel-heading"><h3 class="panel-title">' + colData.header + '</h3></div><div class="panel-body" id="' + colData.id + '"></div></div></div>';
            });
            html += '</div>\n';
        });
        $('#' + divID).html(html);

        //draw a loader in every element
        $.each(grid, function (rowName, rowData) {
            $.each(rowData, function (colName, colData) {
                GUIcontroller.drawLoader(colData.id);
            });
        });

    },
    drawHeader: function () {
        if (GUIcontroller.header.iconLink !== '') {
            var icon = '<img class="activityIcon" src="' + GUIcontroller.header.iconLink + '">';
        }
        else {
            var icon = '';
        }

        var html = '<h3>' + icon + GUIcontroller.header.text + '</h3>';
        $('#header').html(html);
    },
    drawLoader: function (divID) {
        $('#' + divID).html(GUIcontroller.loader);
    },
    drawNavigation: function () {
        var html = '<div class="btn-group" role="group"><button type="button" class="btn btn-default" onclick="$(\'#plugin\').html(GUIcontroller.loader); GUIcontroller.navigation.disabled = \'disabled\'; ' + GUIcontroller.navigation.func + '" ' + GUIcontroller.navigation.disabled + '>' + GUIcontroller.navigation.text + '</button></div>';
        $('#navigation').html(html);
    },
    setNavigation: function (func, text) {
        GUIcontroller.navigation.func = func;
        GUIcontroller.navigation.text = text;
    },
    drawColumnChart: function (divID, res, options) {
        var data = new google.visualization.DataTable(res);
        var defaultOptions = {
            height: 400,
            vAxis: {minValue: 1}
        };
        if (!options) {
            options = defaultOptions;
        }
        var chart = new google.visualization.ColumnChart(document.getElementById(divID));
        chart.draw(data, options);
    },
    drawPieChart: function (divID, res, options) {
        var data = new google.visualization.DataTable(res);
        var defaultOptions = {
            height: 400
        };
        if (!options) {
            options = defaultOptions;
        }
        var chart = new google.visualization.PieChart(document.getElementById(divID));
        chart.draw(data, options);
    },
    drawTable: function (divID, res, options) {
        var data = new google.visualization.DataTable(res);
        var defaultOptions = {
            
        };
        if (!options) {
            options = defaultOptions;
        }
        var chart = new google.visualization.Table(document.getElementById(divID));
        chart.draw(data, options);
    }
};
