"use strict";

import mapboxgl, { Map, MapSourceDataEvent } from "mapbox-gl";
import MapboxGeocoder from "@mapbox/mapbox-gl-geocoder";

import addRelations from "./map/layers/relation";
import addWays from "./map/layers/ways";
import addEvents from "./map/events";

export let map: Map;

mapboxgl.accessToken = process.env.MAPBOX_TOKEN;

export default function (lang: string): Map {
  // Initialize map.
  map = new mapboxgl.Map({
    center: [4.3651, 50.8355],
    container: "map",
    hash: true,
    style: "mapbox://styles/mapbox/dark-v10",
    zoom: 11,
  });

  // Add controls.
  const nav = new mapboxgl.NavigationControl({ showCompass: false });
  map.addControl(nav, "top-left");

  const scale = new mapboxgl.ScaleControl({ unit: "metric" });
  map.addControl(scale);

  const geocoder = new MapboxGeocoder({
    accessToken: mapboxgl.accessToken,
    bbox: [4.243544, 50.763726, 4.482277, 50.913384],
    countries: "be",
    enableEventLogging: false,
    language: lang,
    mapboxgl: mapboxgl,
  });
  map.addControl(geocoder);

  map.on("load", () => {
    map.resize();

    // Add GeoJSON sources.
    addRelations(map);
    addWays(map);

    // Add events
    addEvents(map);
  });

  let sourceLoaded: number = 0;
  map.on("sourcedata", (event: MapSourceDataEvent) => {
    if (event.isSourceLoaded === true) {
      sourceLoaded++;
    }

    console.log(sourceLoaded, event);
    if (sourceLoaded > 0) {
      document.body.classList.add("loaded");
    }
  });

  return map;
}
