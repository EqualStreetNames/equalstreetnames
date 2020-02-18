"use strict";

import mapboxgl, { Map, MapMouseEvent } from "mapbox-gl";

export default function(map: Map, event: MapMouseEvent): void {
  console.log(event.features[0].geometry);

  const { name } = event.features[0].properties;

  // Ensure that if the map is zoomed out such that multiple
  // copies of the feature are visible, the popup appears
  // over the copy being pointed to.
  // const coordinates = event.features[0].geometry.coordinates.slice();
  // while (Math.abs(event.lngLat.lng - coordinates[0]) > 180) {
  //   coordinates[0] += event.lngLat.lng > coordinates[0] ? 360 : -360;
  // }

  new mapboxgl.Popup()
    .setLngLat(event.lngLat)
    .setHTML(name)
    .addTo(map);
}
