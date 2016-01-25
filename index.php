
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
  <script type="text/javascript" src="js/d3.js"></script>
  <script type="text/javascript" src="js/d3.geo.js"></script>
  <script type="text/javascript" src="js/jquery-1.11.1.js"></script>
  <script type="text/javascript" src="js/jquery.mousewheel.js"></script>    
  <link type="text/css" rel="stylesheet" href="css/style.css"/>
  <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
  <style type="text/css">

  #geo {
/*    height: 750px;
*/    padding: 0; 
  }

.globe {
    stroke: rgba(255, 255, 255);
    stroke-width: .25px;
}

svg {
  width: 100%;
  height: 100%px;
  pointer-events: all;
}

circle {
  fill: #dbe4f0;
}

path {
  fill: #aaa;
  stroke: #fff;
}

.trip {
  fill: none;
  stroke: #0100FF;
}

.row {
  margin-top: 10px;
}

#map_selector {
  //margin: 15px;  
}

#solselector {
  /*margin: 10px;*/
  margin-bottom: 15px;
}

#tripselector {
  margin: 15px;
}

.btn-md {  
  margin-bottom: 15px;
  margin-right: 10px;
}

.legenda > span{
  display: block;
}

.map-title {
  font-size: 1em;
}

h3 {
  margin-top: 0;
  margin-bottom: 15px;
}

</style>
</head>

<body>
  <div class="container">  
    <div class="row">        

      <div class="col-md-3">     
        <h3>Santa's Stolen Sleigh <small>Ultimate Solution Visualizer</small></h3>
        
        <!-- <span>by Daniel Dias</span> -->


        <div>
          <span>Solution</span>
          <select id="solselector">
            <option value="null" selected></option>
            <?php 
            $folders = array_filter(glob('solutions/*'), 'is_dir');
            natsort ($folders);
            foreach ($folders as $folder) {
              $folder = str_replace("solutions/","",$folder);
                //$file = str_replace(".json","",$folder);
              echo "<option value='".$folder."'>".$folder."</option>";
            }
            ?>
          </select>

        </div> 


        <button id="loadpoints" class="btn btn-primary btn-md">Load all Gifts</button>
        <button id="loadtrips" class="btn btn-primary btn-md">Load all Routes</button>

        <div class="panel panel-default">
          <div class="panel-heading text-center">Solution Data</div>
          <table class="table table-condensed table-bordered">
            <tr>
              <td>
                <span class = "dados">Cost</span>
              </td>
              <td>
                <span id="sol_cost"></span>
              </td>
            </tr> 
            <tr>
              <td>
                <span class = "dados">Number of Trips</span>
              </td>
              <td>
                <span id="num_trips"></span>
              </td>
            </tr> 
          </table>
        </div> <!--END panel panel-default-->
        <div>
          <span>Trip</span>
          <select id="tripselector">
            <option value="null" selected></option>
            <?php 
            $files = array_filter(glob('solutions/sol_1/Trips/*'), 'is_file');
            natsort ($files);
            foreach ($files as $file) {
              $file = str_replace("solutions/sol_1/Trips/","",$file);
              $file = str_replace(".json","",$file);
              echo "<option value='".$file."'>".$file."</option>";
            }
            ?>
          </select>
        </div>  
        <div class="panel panel-default">
          <div class="panel-heading text-center">Trip Data</div>
          <table class="table table-condensed table-bordered">
            <tr>
              <td>
                <span class = "dados">Cost</span>
              </td>
              <td>
                <span id="trip_cost"></span>
              </td>
            </tr> 
            <tr>
              <td> 
                <span class = "dados"># Gifts</span>
              </td>
              <td>
                <span id="trip_size"></span>
              </td>
            </tr> 
            <tr>
              <td> 
                <span class = "dados">Length</span>
              </td>
              <td>
                <span id="trip_length"></span>
              </td>
            </tr> 
            <tr>
              <td> 
                <span class = "dados">Weight</span>
              </td>
              <td>
                <span id="trip_weight"></span>
              </td>
            </tr> 
          </table>
        </div> <!--END panel panel-default-->
      </div> <!-- end col-md-4 -->

      <div class="col-md-9">
        <div class="panel grafo panel-default">
          <div class="panel-heading">

            <div class="row">
              <div class="col-md-4"></div>
              <div class="col-md-4 text-center map-title">World Map</div>


              <div class="col-md-4">
                <div class="pull-right">
                  <span>Projection:</span>
                  <select id="map_selector">
                    <option value="equalarea">equalarea</option>
                    <option value="equidistant">equidistant</option>
                    <option value="gnomonic">gnomonic</option>
                    <option value="orthographic" selected>orthographic</option>
                    <option value="stereographic">stereographic</option>
                  </select>
                </div>
              </div>

            </div>

          </div>

          <div id="geo" class="panel-body">
            

          </div>  

          <div class="pull-right legenda">
            <span>Drag to rotate the origin</span> 
            <span>Scroll to zoom</span>  
          </div> 
        </div>  
      </div>  

    </div> <!--END row-->
  </div> <!--END container-->
</body>


<script type="text/javascript">

var nRoutes = [];
<?php 
$dirs = array_filter(glob('solutions/*'), 'is_dir');
natsort ($dirs);
foreach ($dirs as $dir) {
  $nome_sol = str_replace("solutions/","",$dir);
  $trips = array_filter(glob("solutions/".$nome_sol."/trips/*"), 'is_file');
  natsort ($trips);
  $trip = end($trips);
  $n = str_replace("solutions/".$nome_sol."/trips/trip","",$trip);
  $n = str_replace(".json","",$n);
  $n = str_replace("_data","",$n);
  echo "nRoutes['".$nome_sol."'] = [".$n."];".PHP_EOL;
}
?>
//$("#solselector").val($("#solselector option:first").val());
//fill_trips();

function fill_trips() {
  var sol = $("#solselector").find(":selected").text();
  var n = sol.replace('sol_','');

  $("#tripselector").html('');
 // var pasta_inst = $("#lista_inst").find(":selected").text();
 for (i = 1, len = nRoutes[sol]; i <= len; i++) { 
   $("#tripselector").append("<option value='trip".concat(i,"'>trip",i,"</option>"));
 }
}

var feature;
var feature2;

var scaleValue = 380;

$('#geo').height($( window ).height()*0.8);

var projection = d3.geo.azimuthal()
.scale(scaleValue)
.origin([0,90])
.mode("orthographic")
.translate([$('#geo').width()/2, $('#geo').height()/2]);

//.translate([640, 400]);

var circle = d3.geo.greatCircle()
.origin(projection.origin());


// TODO fix d3.geo.azimuthal to be consistent with scale
var scale = {
  orthographic: 380,
  stereographic: 380,
  gnomonic: 380,
  equidistant: 380 / Math.PI * 2,
  equalarea: 380 / Math.SQRT2
};

var path = d3.geo.path()
.projection(projection);

var svg = d3.select("#geo").append("svg:svg")
.attr("id", "svg")
.attr("width", $('#geo').width())
.attr("height", $('#geo').height())
.on("mousedown", mousedown);

$('svg').width( $('#geo').width() );
$('svg').height( $('#geo').height() );

//.attr("width", 1280)
//.attr("height", 800)

// var backgroundCircle = svg.selectAll("path")
//       .data("dM 423, 139 m -75, 0 a 75,75 0 1,0 150,0 a 75,75 0 1,0 -150,0","")
//       .enter()
//       .append("svg:path")
//       .attr('class', 'globe')
//       .attr("d", clip);

d3.json("world-countries.json", function(collection) {
  console.log(collection);
  feature = svg.selectAll("path")
  .data(collection.features,function(d){return d.id;})
  .enter().append("svg:path")
  .attr("d", clip);

  feature.append("svg:title")
  .text(function(d) { return d.properties.name; });  

  feature = svg.selectAll("path");
});

console.log($('#geo').width()/2);
console.log($('#geo').height()/2);

   //   .attr('cx', $('#geo').width()/2)
   //   .attr('cy', $('#geo').height()/2)
  //    .attr('r', projection.scale())

feature = svg.selectAll("path");

function loadAllPoints() {  
  //"gifts.json" is the first bugged inverted json that i generated
  //gifts2.json" is just a few points, no weight attribure
  //gifts3.json" is all points, no weight attribure
  //gifts4.json" is all points and the North Pole, with weight attribute
  //gifts5.json" is 1000 points and the North Pole, with weight attribute
  d3.json("gifts4.json", function(collection) {
    feature = svg.selectAll("path")
    .data(collection.features,function(d){return d.id;})
    .enter().append("svg:path")
    .attr("style", function(d) { return color_scale(d.properties.weight);})
    .attr("class", "point")
    .attr("d", clip);

    
    feature.append("svg:title")
    .text(function(d) { return d.properties.name + "\n" + d.properties.weight  + "\n" + d.geometry.coordinates[1] + "\n" + d.geometry.coordinates[0]; });

    feature = svg.selectAll("path");

  });


}

function loadAllRoutes() {  

  var sol = $("#solselector").find(":selected").text();
  var n = sol.replace('sol_','');

 for (i = 1, len = nRoutes[sol]; i <= len; i++) { 
   
  var tripfile = "solutions/"+sol+"/trips/trip" + i +".json";

  d3.json(tripfile, function(collection) {       
        feature = svg.selectAll("path")
        .data(collection.features,function(d){return d.id;})
        .enter().append("svg:path")
        .attr("class", "trip")
        .attr("d", clip);
        
        feature.append("svg:title")
        .text(function(d) { return d.properties.name + "\n" + d.properties.weight; });

        feature = svg.selectAll("path");

      });
  }

}

d3.select(window)
.on("mousemove", mousemove)
.on("mouseup", mouseup);

d3.select("#geo")
.on("mousewheel", mousewheel);

d3.select("#map_selector").on("change", function() {
  projection.mode(this.value).scale(scale[this.value]);
  refresh(750);
});

var m0,
o0;

function mousedown() {
  m0 = [d3.event.pageX, d3.event.pageY]; //started dragging in m0
  o0 = projection.origin();
  d3.event.preventDefault();
}

function mousemove() {
  if (m0) {
    var m1 = [d3.event.pageX, d3.event.pageY], //where the mouse dragging headed
        o1 = [o0[0] + (m0[0] - m1[0]) / 8, o0[1] + (m1[1] - m0[1]) / 8]; // x_o1 = x_o0 + (x_m0 - x_m1)/8  // y_o1 = y_o0 + (y_m0 - y_m1)/8 
    projection.origin(o1); //atualiza a origem
    circle.origin(o1);
    refresh();
  }
}

function mouseup() {
  if (m0) {
    mousemove();
    m0 = null; //ended dragging
  }
}

var scaleDownLimit = 0;
var scaleUpLimit = 10000;

function mousewheel() {
  scaleValue -= event.deltaY*2;
  if (scaleValue < scaleDownLimit) {scaleValue = scaleDownLimit};
  if (scaleValue > scaleUpLimit) {scaleValue = scaleUpLimit};
  projection.scale(scaleValue);
  //console.log(scaleValue);
  refresh();
}

function refresh(duration) {
  (duration ? feature.transition().duration(duration) : feature).attr("d", clip);
  //(duration ? feature2.transition().duration(duration) : feature2).attr("d", clip);
}

function clip(d) {
  return path(circle.clip(d));
}

$("#loadpoints").click(function() {
  svg.selectAll("path.point").remove();
  loadAllPoints();
});

$("#loadtrips").click(function() {
  svg.selectAll("path.trips").remove();
  loadAllRoutes();
});

$("#tripselector").change(function() {
   loadRoute();
   preencheTrip();
});

 $("#solselector").change(function() {
  preencheSol();
  fill_trips();
  loadRoute();
  preencheTrip();
 });


function color_scale(weight)
{
  var minValue=0;
  var maxValue=255;

  var minWeight = 1;
  var maxWeight = 50;
  
  var red;
  // console.log(weight);
  red = Math.round(maxValue - ((weight-minWeight)/(maxWeight-minWeight))*(maxValue-minValue));
 // console.log(red);
 return "fill: rgb(255,"+red+","+red+")";
}


function loadRoute() {
  var sol = $("#solselector").find(":selected").text();
  var tripfile = $("#tripselector").find(":selected").text();
  svg.selectAll("path.trip").remove(); 
  svg.selectAll("path.point").remove();

  if (tripfile != "") {
    var pointsfile = "solutions/"+sol+"/points/points_" + tripfile +".json";
    var tripfile = "solutions/"+sol+"/trips/" + tripfile +".json";
      

      d3.json(tripfile, function(collection) {
        feature = svg.selectAll("path")
        .data(collection.features,function(d){return d.id;})
        .enter().append("svg:path")
        .attr("class", "trip")
        .attr("d", clip);
        
        feature.append("svg:title")
        .text(function(d) { return d.properties.name + "\n" + d.properties.weight; });

        d3.json(pointsfile, function(collection) {
          feature = svg.selectAll("path")
          .data(collection.features,function(d){return d.id;})
          .enter().append("svg:path")
          .attr("class", "point")
          .attr("style", function(d) { return color_scale(d.properties.weight);})
          .attr("d", clip);

          feature.append("svg:title")
          .text(function(d) { return d.properties.name + "\n" + d.properties.weight + "\n" + d.geometry.coordinates[1] + "\n" + d.geometry.coordinates[0]; });

          feature = svg.selectAll("path");
        });
      });
    };
}

function preencheSol() {
  pasta = $("#solselector").find(":selected").text();
  var request = $.ajax({
    cache: false,
    url: "solutions/".concat(pasta,"/solutiondata.json"),
    dataType:'json',
    error: function() {
      $('#sol_cost').text("");
      $('#num_trips').text("");      
    },
    success: function(dados) {
      var sol_cost = dados.sol_cost;
      $('#sol_cost').text(sol_cost.toLocaleString());
      $('#num_trips').text(dados.num_trips); 
    }
  });
}

function preencheTrip() {
  var sol = $("#solselector").find(":selected").text();
  var tripfile = $("#tripselector").find(":selected").text();

  tripfile = "solutions/"+sol+"/trips/" + tripfile +"_data.json";

  var request = $.ajax({
    cache: false,
    url: tripfile,
    dataType:'json',
    error: function() {
      $('#trip_cost').text("");
      $('#trip_length').text("");  
      $('#trip_size').text("");  
      $('#trip_weight').text("");      
    },
    success: function(dados) {
      $('#trip_cost').text(dados.trip_cost.toLocaleString());
      $('#trip_length').text(dados.trip_length);  
      $('#trip_size').text(dados.trip_size);  
      $('#trip_weight').text(dados.trip_weight); 
    }
  });
}


</script>
</body>
</html>

