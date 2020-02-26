"use strict";

import { Map, MapMouseEvent, MapboxGeoJSONFeature, EventData } from "mapbox-gl";

import onClick from "./events/click";
import onMouseEnter from "./events/mouseenter";
import onMouseLeave from "./events/mouseleave";

export default function(map: Map): void {
  // Add click event.
  map.on(
    "click",
    "layer-relations",
    (
      event: MapMouseEvent & { features?: MapboxGeoJSONFeature[] } & EventData
    ) => onClick(map, event.features ?? [], event.lngLat)
  );
  map.on(
    "click",
    "layer-ways",
    (
      event: MapMouseEvent & { features?: MapboxGeoJSONFeature[] } & EventData
    ) => onClick(map, event.features ?? [], event.lngLat)
  );

  // Change the cursor to a pointer when the mouse is over a layer.
  map.on("mouseenter", "layer-relations", event => onMouseEnter(map, event));
  map.on("mouseenter", "layer-ways", event => onMouseEnter(map, event));

  // Change it back to a pointer when it leaves.
  map.on("mouseleave", "layer-relations", event => onMouseLeave(map, event));
  map.on("mouseleave", "layer-ways", event => onMouseLeave(map, event));
}
