<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    // function draw chart
    function drawChart() {
        var data = google.visualization.arrayToDataTable([<?php echo $dataArr; ?>]);
        var options = {
            title: '<?php echo $graphTitle;?>',
            width: spChartWidth('curve_chart'),
            vAxis: {
                <?php echo !empty($reverseDir) ? "direction: -1," : ""; ?>
                viewWindow: {
                    <?php echo !empty($minValue) ? "min: $minValue," : ""; ?>
                    <?php echo !empty($maxValue) ? "max: $maxValue," : ""; ?>
                }
            },
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
        chart.draw(data, options);
    }

    // this chart used to force a 900px-wide container regardless of
    // viewport, causing horizontal page scroll on any phone - every
    // graphical report across the app (rank/backlinks/AI referral/etc.)
    // embeds this same partial, so sizing it off the container's real
    // width instead fixes all of them at once.
    var curveChartResizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(curveChartResizeTimer);
        curveChartResizeTimer = setTimeout(drawChart, 200);
    });
</script>
<div id="curve_chart" style="min-height: 500px"></div>