"use strict";

import { Map } from "mapbox-gl";

import layout from "../style/layout";
import paint from "../style/paint";

import { increaseCountSource } from "../../map";

const attribution =
  '© <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

export default function (map: Map): void {
  increaseCountSource();

  map.addSource("geojson-relations", {
    type: "geojson",
    attribution,
    data: "/relations.geojson",
  });

  map.addLayer({
    id: "layer-relations",
    type: "line",
    source: "geojson-relations",
    layout,
    paint,
  });
}
