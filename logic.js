var graphs;
// Adapting to window size such that both graphs can be displayed
$("div[id^='graph']").css({ height: $(window).height() * 0.5, width: Math.min($(window).width() * 0.9, 1175) });

$('#today').click(function () {
    var now = Math.round(new Date() / 1000);
    newRequest(now - 86400, now);
});

$('#week').click(function () {
    var now = Math.round(new Date() / 1000);
    newRequest(now - 86400 * 7, now);
});

$('#month').click(function () {
    var now = Math.round(new Date() / 1000);
    newRequest(now - 86400 * 30, now);
});

$('#confirm').click(function () {
    // MANAGE EXCEPTIONS IN PARSING HERE
    start = Math.round(Date.parse($('#start').val()) / 1000);
    end = Math.round(Date.parse($('#end').val()) / 1000);
    if (isNaN(start) || isNaN(end)) {
        alert("Dates badly formatted.");
        return;
    }
    if (start < end)
        newRequest(start, end);
    else
        alert('Start date should be before end date\nCannot warp the space continuum here');
});

$('#roll').click(function () {
    if (graphs == undefined)
        return;
    for (let index = 0; index < graphs.length; index++) {
        graphs[index].adjustRoll($('#rollperiod').val());
    }
});

////////////////////////////////////////////////////////////////////////////////////////////////

function newRequest(start, end) {
    graphs = [];
    // Get which sensors are checked
    sensors = [];
    sensorNames = [];
    checked = $(':checked');
    for (let i = 0; i < checked.length; i++) {
        sensors.push(checked[i].id);
        sensorNames.push(checked[i].name);
    }

    $.ajax({
        url: "/logger/retreiveData.php",
        method: "POST",
        dataType: "text",
        data: JSON.stringify({ start: start, end: end, sensors: sensors }),
        timeout: 30000,
        success: function (result) {
            // Create graphs both for temperature and humidity
            result = JSON.parse(result);
            for (let type = 0; type < 2; type++) {
                graphs.push(newGraph("graph" + type, result[type], 30, type));
                if (graphs.length > 1)
                    Dygraph.synchronize(graphs, { range: false });
            }
        },
        error: function (result) {
            alert('Error retreiving data from server');
        }
    });
}

// Set specific options depending on the graph
// At the same time retreive label names from the checkboxes
function newGraph(divID, data, roll, type) {
    label = "";
    switch (type) {
        case 0:
            label = 'Temperature';
            break;
        case 1:
            label = 'Humidity';
            break;
    }

    return g = new Dygraph(
        document.getElementById(divID),
        data,
        {
            rollPeriod: roll,
            //showRoller: true,
            title: label,
            ylabel: label,
            legend: 'follow',
            axis: {
                x: {
                    //valueFormatter: Dygraph.dateString_,
                    valueParser: function (x) { return 1000 * parseInt(x); },
                    ticker: Dygraph.dateTicker
                }
            }
        }
    );
}


