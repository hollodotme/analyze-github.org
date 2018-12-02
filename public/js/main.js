$(document).ready(function () {
    const form = $('#analyzeForm');
    const logOutput = $('#eventSourceOutput');
    const analyzeBtn = $('#analyzeBtn');
    const stopBtn = $('#stopBtn');
    const waitImage = $('#waitImage');

    form.on('submit', function (e) {
        e.preventDefault();
        const eventSourceUrl = form.data('eventsource-url') + '?' + form.serialize();
        console.log(eventSourceUrl);
        if (!!window.EventSource) {
            logOutput.text('');
            let source = new EventSource(eventSourceUrl);

            stopBtn.off().click(function (e) {
                e.preventDefault();
                source.close();
                waitImage.hide();
                analyzeBtn.prop('disabled', false);
                stopBtn.prop('disabled', true);
                logOutput.append('<div class="text-danger">Analysis stopped.</div>');
                logOutput.scrollTop(99999999);
            });

            source.addEventListener('message', function (e) {
                // No output when only keeping alive
                if (e.data === '[KEEPALIVE]') {
                    return;
                }

                logOutput.append('<div class="text-success">' + e.data + '</div>');
                logOutput.scrollTop(99999999);
            }, false);

            source.addEventListener('debug', function (e) {
                logOutput.append('<div class="text-warning">' + e.data + '</div>');
                logOutput.scrollTop(99999999);
            }, false);

            source.addEventListener('jsonResult', function (e) {
                logOutput.append("Fetching result: " + e.data + "\n");
                fetch('/results.php?resultType=repositories&' + form.serialize())
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Result not found.');
                        }
                        return response.json();
                    })
                    .then(function (series) {
                        $(series).each(function (i) {
                            $(this.data).each(function (j) {
                                series[i].data[j].x = Date.parse(this.x);
                            });
                        });
                        console.dir(series);
                        $.drawBubbleChart(series);
                    })
                    .catch(error => console.error('Error:', error));
            }, false);

            source.addEventListener('error', function (e) {
                logOutput.append('<div class="text-danger">' + e.data + '</div>');
                logOutput.scrollTop(99999999);
            }, false);

            source.addEventListener('beginOfStream', function () {
                logOutput.append("Stream started.\n");
            }, false);

            source.addEventListener('open', function () {
                logOutput.text('');
                console.log('Event Source opened.');
                analyzeBtn.prop('disabled', true);
                stopBtn.prop('disabled', false);
                waitImage.show();
            }, false);

            source.addEventListener('endOfStream', function () {
                source.close();
                logOutput.append("Stream ended.\n");
                logOutput.scrollTop(99999999);
                analyzeBtn.prop('disabled', false);
                stopBtn.prop('disabled', true);
                waitImage.hide();
                console.log('Event Source closed.');
            }, false);

            source.addEventListener('error', function (e) {
                if (e.readyState === EventSource.CLOSED) {
                    console.log('Event Source closed unexpectedly.');
                    source.close();
                    analyzeBtn.prop('disabled', false);
                    waitImage.hide();
                }
            }, false);
        } else {
            alert('No event source available. Please use another, modern browser!');
        }
    });
});

$.drawBubbleChart = function (dataSet) {
    const chartModal = $('#chartModal');
    chartModal.on('show.bs.modal', function () {
        Highcharts.chart('bubbleChart', {
            chart: {
                type: 'bubble',
                plotBorderWidth: 1,
                zoomType: 'x'
            },
            legend: {
                enabled: true
            },
            title: {
                text: 'Age, commit count, disk size and language analysis'
            },
            xAxis: {
                gridLineWidth: 1,
                type: 'datetime',
                title: {
                    text: 'month/year'
                },
            },
            yAxis: {
                type: 'logarithmic',
                title: {
                    text: 'Count commits'
                },
                labels: {
                    format: '{value}'
                },
            },
            tooltip: {
                useHTML: true,
                headerFormat: '<table>',
                pointFormat: '<tr><th colspan="2"><h3>{point.name}</h3></th></tr>' +
                    '<tr><th>Created at:</th><td>{point.createdAt}</td></tr>' +
                    '<tr><th>Disk usage:</th><td>{point.diskUsage} KB</td></tr>' +
                    '<tr><th>Commits:</th><td>{point.countCommits}</td></tr>' +
                    '<tr><th>Last tag:</th><td>{point.lastTag}</td></tr>',
                footerFormat: '</table>',
                followPointer: true
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '{point.name}'
                    }
                }
            },
            series: dataSet
        });
    });
    chartModal.modal('show');
};