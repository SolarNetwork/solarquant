<!DOCTYPE html>
<head>

<title>solarquant.Admin</title>
<link href='../../css/solarStyle.css' type='text/css' rel='stylesheet'>
<link href='../../css/bootstrap.min.css' type='text/css'
	rel='stylesheet'>
<link href='../../css/bootstrap-theme.min.css' type='text/css'
	rel='stylesheet'>
<script src='../../js/bootstrap.min.js'></script>
<link href='../../includes/calendar.css' rel='stylesheet'
	type='text/css' />
<script language='javascript' src='../../includes/calendar.js'></script>

</head>
<meta charset="utf-8">
<style>
.area {
	fill: None;
	stroke: orange;
	clip-path: url(#clip);
}

.area2 {
	fill: None;
	stroke: orange;
	clip-path: url(#clip);
}

.areaReal2 {
	fill: None;
	stroke: steelBlue;
	clip-path: url(#clip);
}

.areaReal {
	fill: None;
	stroke: steelBlue;
	clip-path: url(#clip);
}

body {min-width = 90%;min-height = 100%;
	
}

.zoom {
	cursor: move;
	fill: none;
	pointer-events: all;
}
</style>
<svg id="svg1" height="500" width="1000"></svg>
<script src="https://d3js.org/d3.v4.min.js"></script>

<body>
	<script
		src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script>

var svg = d3.select("svg"),
    margin = {top: 20, right: 20, bottom: 110, left: 40},
    margin2 = {top: 430, right: 20, bottom: 30, left: 40},
    width = +svg.attr("width") - margin.left - margin.right,
    height = +svg.attr("height") - margin.top - margin.bottom,
    height2 = +svg.attr("height") - margin2.top - margin2.bottom;

var parseDate = d3.timeParse("%Y-%m-%d %H:%M:%S");

var x = d3.scaleTime().range([0, width]),
    x2 = d3.scaleTime().range([0, width]),
    y = d3.scaleLinear().range([height, 0]),
    y2 = d3.scaleLinear().range([height2, 0]);

var xAxis = d3.axisBottom(x),
    xAxis2 = d3.axisBottom(x2),
    yAxis = d3.axisLeft(y);

var brush = d3.brushX()
    .extent([[0, 0], [width, height2]])
    .on("brush end", brushed);

var zoom = d3.zoom()
    .scaleExtent([1, Infinity])
    .translateExtent([[0, 0], [width, height]])
    .extent([[0, 0], [width, height]])
    .on("zoom", zoomed);

var area = d3.area()
    .curve(d3.curveMonotoneX)
    .x(function(d) { return x(d.DATE); })
    .y0(height)
    .y1(function(d) { return y(d.PREDICTED_WATT_HOURS); });

var area2 = d3.area()
    .curve(d3.curveMonotoneX)
    .x(function(d) { return x2(d.DATE); })
    .y0(height2)
    .y1(function(d) { return y2(d.PREDICTED_WATT_HOURS); });
    
var areaReal = d3.area()
.curve(d3.curveMonotoneX)
.x(function(d) { return x(d.DATE); })
.y0(height)
.y1(function(d) { return y(d.WATT_HOURS); });

var areaReal2 = d3.area()
.curve(d3.curveMonotoneX)
.x(function(d) { return x2(d.DATE); })
.y0(height2)
.y1(function(d) { return y2(d.WATT_HOURS); });

svg.append("defs").append("clipPath")
    .attr("id", "clip")
  .append("rect")
    .attr("width", width)
    .attr("height", height);

var focus = svg.append("g")
    .attr("class", "focus")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var context = svg.append("g")
    .attr("class", "context")
    .attr("transform", "translate(" + margin2.left + "," + margin2.top + ")");


var csvLink = "https://data.solarnetwork.net/solarquery/api/v1/pub/datum/list?"+
		"nodeId=205&aggregation=Hour&startDate=2017-09-01T12%3A00&endDate=2017-10-01T12%3A00&sourceIds=Lighting";
var csvPath = "../../prediction_output/predictions/"+localStorage.getItem("reqId")+"_prediction.csv";

var csv2Path = "../../prediction_output/predictions/"+localStorage.getItem("reqId")+"_real.csv";
"./graphFromDatabase.php?reqId="+localStorage.getItem("reqId")
d3.json("./graphFutureFromDatabase.php?reqId="+localStorage.getItem("reqId"), function(error, data) {

  data.forEach(function(d){
	d.DATE = parseDate(d.DATE);
	});


  x.domain(d3.extent(data, function(d) { return d.DATE; }));
  y.domain([0, d3.max(data, function(d) { return d.PREDICTED_WATT_HOURS*1.5; })]);
  x2.domain(x.domain());
  y2.domain(y.domain());


 
  focus.append("path")
      .datum(data)
      .attr("class", "area")
      .attr("d", area);

  focus.append("g")
      .attr("class", "axis axis--x")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis);

  focus.append("g")
      .attr("class", "axis axis--y")
      .call(yAxis);

  context.append("path")
      .datum(data)
      .attr("class", "area")
      .attr("d", area2);
  
  focus.append("path")
  .datum(data)
  .attr("class", "areaReal")
  .attr("d", areaReal);

  context.append("path")
  .datum(data)
  .attr("class", "areaReal")
  .attr("d", areaReal2);

  context.append("g")
      .attr("class", "axis axis--x")
      .attr("transform", "translate(0," + height2 + ")")
      .call(xAxis2);

  context.append("g")
      .attr("class", "brush")
      .call(brush)
      .call(brush.move, x.range());

  svg.append("rect")
      .attr("class", "zoom")
      .attr("width", width)
      .attr("height", height)
      .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
      .call(zoom);

});


function brushed() {
  if (d3.event.sourceEvent && d3.event.sourceEvent.type === "zoom") return; // ignore brush-by-zoom
  var s = d3.event.selection || x2.range();
  x.domain(s.map(x2.invert, x2));
  focus.select(".area").attr("d", area);
  focus.select(".areaReal").attr("d", areaReal);
  focus.select(".axis--x").call(xAxis);
  svg.select(".zoom").call(zoom.transform, d3.zoomIdentity
      .scale(width / (s[1] - s[0]))
      .translate(-s[0], 0));
}

function zoomed() {
  if (d3.event.sourceEvent && d3.event.sourceEvent.type === "brush") return; // ignore zoom-by-brush
  var t = d3.event.transform;
  x.domain(t.rescaleX(x2).domain());
  focus.select(".area").attr("d", area);
  focus.select(".areaReal").attr("d", areaReal);
  focus.select(".axis--x").call(xAxis);
  context.select(".brush").call(brush.move, x.range().map(t.invertX, t));
}

function type(d) {
  d.DATE = parseDate(d.DATE);
  d.WATT_HOURS = parseFloat(d.WATT_HOURS);
  d.PREDICTED_WATT_HOURS = parseFloat(d.PREDICTED_WATT_HOURS);
  return d;
}

</script>
<?php
	$id = $_REQUEST['reqId'];
	echo '<a href=downloadCSV.php?reqId='.$id."><button>Download CSV</button></a>"
?>
</body>



