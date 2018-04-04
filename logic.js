var graph;

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
    start = Math.round(Date.parse($('#start').val()) / 1000);
    end = Math.round(Date.parse($('#end').val()) / 1000);
    newRequest(start, end);
});


function newRequest(start, end) {
    //alert(start + "\n" + end); return;
    // As for now just get temperature from all sensors
    sensors = [1, 2, 3, 4, 5];
    type = 0;

    // First check if file is alread present
    filename = 'buffer/' + start + '-' + end + '-' + type + '-' + oneHotEncode(sensors) + '.csv';
    $.ajax({
        url: filename,
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
                data: JSON.stringify({start: start, end: end, type: type, sensors : sensors}),
                timeout: 3000,
                success: function (result) {
                    graph = newGraph("graph1", filename, 10);
                },
                error: function (result) {
                    alert('Error retreiving data from server');
                }
            });
        }
    });
}

function newGraph(divID, filename, roll) {
    return g = new Dygraph(
        document.getElementById(divID),
        filename,
        {
            rollPeriod: roll,
            //showRoller: true,
            title: 'Temperatura Stanze',
            ylabel: 'Temperatura',
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
}

function oneHotEncode(intArray) {
    result = 0;

    for (let i = 0; i < intArray.length; i++) {
        mask = 1;
        result = result | mask << intArray[i];
    }

    return result;
}