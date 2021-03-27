"use strict";

import { Feature, FeatureCollection } from "@turf/helpers";
import { Map } from "maplibre-gl";

const attribution =
  '© <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

export default function (map: Map, data: Feature|FeatureCollection): void {
  map.addSource("geojson-boundary", {
    type: "geojson",
    attribution,
    data,
  });

  map.addLayer({
    id: "layer-boundary",
    type: "line",
    source: "geojson-boundary",
    paint: {
      "line-color": "#000",
      "line-width": 3,
      "line-opacity": 0.5
    },
  });
}
