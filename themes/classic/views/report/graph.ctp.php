<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    // function draw chart
    function drawChart() {
        var data = google.visualization.arrayToDataTable([<?php echo $dataArr; ?>]);  
        var options = {
            title: '<?php echo $graphTitle;?>',
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
</script>
<div id="curve_chart" style="min-width: 900px; min-height: 500px"></div>