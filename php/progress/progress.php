<!DOCTYPE html>
<meta charset="utf-8">
<style>
.area {
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
var inter = setInterval(function() {
                updateData();
        }, 50); 



var svg = d3.select("svg"),
    margin = {top: 20, right: 20, bottom: 110, left: 40},
    margin2 = {top: 430, right: 20, bottom: 30, left: 40},
    width = +svg.attr("width") - margin.left - margin.right,
    height = +svg.attr("height") - margin.top - margin.bottom,
    height2 = +svg.attr("height") - margin2.top - margin2.bottom;

var parseDate = d3.timeParse("%Y-%m-%dT%H:%M:%S.%fZ");

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
    .x(function(d) { return x(d.epoch); })
    .y0(height)
    .y1(function(d) { return y(d.loss); });


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



var csvPath = "../../logs/"+localStorage.getItem("reqId")+"_log.csv";
d3.csv(csvPath, type, function(error, data) {


  x.domain(d3.extent(data, function(d) { return d.epoch; }));
  y.domain([0, d3.max(data, function(d) { return d.loss*1.5; })]);
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

  
  focus.append("path")
  .datum(data)
  .attr("class", "areaReal")
  .attr("d", areaReal);


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

function updateData(){
var csvPath = "../../logs/"+localStorage.getItem("reqId")+"_log.csv";
d3.csv(csvPath, type, function(error, data) {


  x.domain(d3.extent(data, function(d) { return d.epoch; }));
  y.domain([0, d3.max(data, function(d) { return d.loss*1.5; })]);
  x2.domain(x.domain());
  y2.domain(y.domain());	

  focus.select("path")
      .datum(data)
      .attr("class", "area")
      .attr("d", area);

});
}
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
	d.epoch = +d.epoch;

  return d;
}

</script>
</body>

