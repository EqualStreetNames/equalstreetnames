"use strict";

import { Map, MapMouseEvent } from "mapbox-gl";

export default function(map: Map, event: MapMouseEvent): void {
  map.getCanvas().style.cursor = "pointer";
}
