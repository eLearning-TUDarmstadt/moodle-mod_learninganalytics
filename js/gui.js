/**
 * Generalized functions to draw tables, graphs and other gui elements
 */

function drawTable(div) {
    var data = new google.visualization.DataTable();
    res = GUI.data;
    $.each(res.header, function(index, content) {
        data.addColumn('string', content);
    });

    result = res.data;
    var array = new Array();
    $.each(result, function(index, content) {
        var subarray = new Array();
        subarray.push(index);

        $.each(content, function(id, numbers) {
            subarray.push(numbers.toString());
        });
        array.push(subarray);
    });
    data.addRows(array);
    var formatter = new google.visualization.NumberFormat(
            {prefix: '$', negativeColor: 'red', negativeParens: true});
    formatter.format(data, 1);
    var table = new google.visualization.Table(document.getElementById(div));
    table.draw(data, {
        showRowNumber: false,
        allowHtml: true,
    });


    // Setup listener
    google.visualization.events.addListener(table, 'select', selectHandler);
    // Select Handler. Call the table's getSelection() method
    function selectHandler() {
        GUI.drawLoader();
        var selection = table.getSelection();
        var translatedPluginName = data.getValue(selection[0].row, 0);
        console.log(translatedPluginName);
        GUI.level = GUI.level + 1;
        var link = "https://mdl-alpha.un.hrz.tu-darmstadt.de/mod/learninganalytics/rest/rest.php/detailedModView/" + GUI.course + "/" + translatedPluginName;
        GUI.loadData(link, function() {
            GUI.generateButton('GUI.level = GUI.level - 1; GUI.draw();', 'Zur&uuml;ck');
            drawTable('left');
            drawStackedColumnChart('right');
        });
    }
}

function drawStackedColumnChart(div) {
    var data = new google.visualization.DataTable();
    res = GUI.data;
    var counter = 0;
    $.each(res.header, function(index, content) {
        if (counter == 0) {
            data.addColumn('string', content);
        }
        else {
            data.addColumn('number', content);
        }
        counter++;
    });

    result = res.data;
    var array = new Array();
    $.each(result, function(index, content) {
        var subarray = new Array();
        subarray.push(index.toString());

        $.each(content, function(id, numbers) {
            subarray.push(parseInt(numbers));
        });
        array.push(subarray);
    });
    data.addRows(array);
    var options = {
        isStacked: true,
    };
    var chart = new google.visualization.ColumnChart(document
            .getElementById(div));

    chart.draw(transposeDataTable(data), options);
}