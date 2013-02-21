
<?php if ($place->has('geo:long')): ?>
  <script src="http://openlayers.org/api/OpenLayers.js"></script>
  <div style="border:solid 1px #ccc;width:300px; height:300px;float:right" id="map"></div>
  <script>
$(document).ready( function() {
  var map = new OpenLayers.Map("map");
  var wms = new OpenLayers.Layer.OSM();
  map.addLayer(wms);
  
  var lonLat = new OpenLayers.LonLat( <?php echo $place->get('geo:long'); ?>,<?php echo $place->get('geo:lat'); ?> )
           .transform(
              new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
              map.getProjectionObject() // to Spherical Mercator Projection
            );
   
  var zoom = 14;
  
  var markers = new OpenLayers.Layer.Markers( "Markers" );
  map.addLayer(markers);
  markers.addMarker(new OpenLayers.Marker(lonLat));
  map.setCenter( lonLat, zoom );
});
  </script>
<?php endif; ?>

<h1><?php echo $place->label(); ?></h1>

<ul>
  <?php foreach (($place->all('-event:place' )?:array()) as $event): ?>
    <li><a href='<?php echo $event; ?>'><?php echo $event->label(); ?></a> <?php echo $event->get('event:time')->get('tl:start'); ?></li>
  <?php endforeach; ?>
</ul>
<div style='clear:both'></div>
