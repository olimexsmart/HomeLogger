var graphs;
// Adapting to window size such that both graphs can be displayed
$("div[id^='graph']").css({ height: $(window).height() * 0.5, width: $(window).width() * 0.9 });

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


function newRequest(start, end) {
    graphs = [];
    sensors = [];
    filename = [];

    checked = $(':checked');
    for (let i = 0; i < checked.length; i++) {
        sensors.push(checked[i].name);
    }

    // Both temperature and humidity
    for (let type = 0; type < 2; type++) {
        // First check if file is alread present
        filename.push('buffer/' + start + '-' + end + '-' + type + '-' + oneHotEncode(sensors) + '.csv');
        $.ajax({
            url: filename[type],
            type: 'HEAD',
            success: function () {
                //file exists
                console.log('File exists');
            },
            error: function () {
                //file not exists
                console.log('File not found, requesting');
                $.ajax({
                    url: "retreiveData.php",
                    method: "POST",
                    dataType: "text",
                    data: JSON.stringify({ start: start, end: end, type: type, sensors: sensors }),
                    timeout: 30000,
                    success: function (result) {
                        /*
                            Using the result as index is necessary because otherwise
                            the async callbacks would be called once the for loop had already 
                            finished. And thus the variables used would be alwayse the 
                            same of the last cycle.
                        */
                        result = parseInt(result); // Converting string to int
                        graphs.push(newGraph("graph" + type, filename[result], 30, result));
                        if (graphs.length > 1)
                            Dygraph.synchronize(graphs);
                    },
                    error: function (result) {
                        alert('Error retreiving data from server');
                    }
                });
            }
        });
    }    
}

function newGraph(divID, filename, roll, type) {
    switch (type) {
        case 0:
            return g = new Dygraph(
                document.getElementById(divID),
                filename,
                {
                    rollPeriod: roll,
                    //showRoller: true,
                    title: 'Temperature',
                    ylabel: 'Temperature',
                    legend: 'always',
                    axis: {
                        x: {
                            //valueFormatter: Dygraph.dateString_,
                            valueParser: function (x) { return 1000 * parseInt(x); },
                            ticker: Dygraph.dateTicker
                        }
                    }
                }
            );
            break;
        case 1:
            return g = new Dygraph(
                document.getElementById(divID),
                filename,
                {
                    rollPeriod: roll,
                    //showRoller: true,
                    title: 'Humidity',
                    ylabel: 'Humidity',
                    legend: 'always',
                    axis: {
                        x: {
                            //valueFormatter: Dygraph.dateString_,
                            valueParser: function (x) { return 1000 * parseInt(x); },
                            ticker: Dygraph.dateTicker
                        }
                    }
                }
            );
            break;
    }    
}

function oneHotEncode(intArray) {
    result = 0;

    for (let i = 0; i < intArray.length; i++) {
        mask = 1;
        result = result | mask << intArray[i];
    }

    return result;
}