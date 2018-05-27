<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script type="text/javascript">



            google.charts.load('current', {'packages': ['corechart']});
            google.charts.setOnLoadCallback(drawChart);


            function drawChart() {

                var array;
                $.ajax({
                    url: "index",
                    context: document.body
                }).done(function (datao) {
//                $(this).addClass("done");
                    console.log(datao);
                    array = datao;

                });
                var data = google.visualization.arrayToDataTable(JSON.parse(array));

                var options = {
                    title: 'Company Performance',
                    curveType: 'function',
                    legend: {position: 'bottom'}
                };

                var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="curve_chart" style="width: 900px; height: 500px"></div>
    </body>
</html>