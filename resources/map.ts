"use strict";

import mapboxgl, { Map, MapSourceDataEvent } from "mapbox-gl";
import MapboxGeocoder from "@mapbox/mapbox-gl-geocoder";

import addRelations from "./map/layers/relation";
import addWays from "./map/layers/ways";
import addEvents from "./map/events";

export let map: Map;

mapboxgl.accessToken = process.env.MAPBOX_TOKEN;

export default function (
  lang: string,
  center: [number, number],
  zoom: number,
  bbox: [number, number, number, number],
  countries: string
): Map {
  // Initialize map.
  map = new mapboxgl.Map({
    center,
    container: "map",
    hash: true,
    style: "mapbox://styles/mapbox/dark-v10",
    zoom,
  });

  // Add controls.
  const nav = new mapboxgl.NavigationControl({ showCompass: false });
  map.addControl(nav, "top-left");

  const scale = new mapboxgl.ScaleControl({ unit: "metric" });
  map.addControl(scale);

  const geocoder = new MapboxGeocoder({
    accessToken: mapboxgl.accessToken,
    bbox,
    countries,
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

  map.on("idle", () => {
    document.body.classList.add("loaded");
  });

  return map;
}
