"use strict";

import { Map } from "mapbox-gl";

import layout from "../style/layout";
import paint from "../style/paint";

import relations from "../../../data/relations.geojson";

const attribution =
  '© <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

export default function(map: Map): void {
  map.addSource("geojson-relations", {
    type: "geojson",
    attribution,
    data: relations
  });

  map.addLayer({
    id: "layer-relations",
    type: "line",
    source: "geojson-relations",
    layout,
    paint
  });
}
