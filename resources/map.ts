"use strict";

import mapboxgl, { Map, MapSourceDataEvent } from "mapbox-gl";
import MapboxGeocoder from "@mapbox/mapbox-gl-geocoder";

import addRelations from "./map/layers/relation";
import addWays from "./map/layers/ways";
import addEvents from "./map/events";

export let map: Map;

mapboxgl.accessToken = process.env.MAPBOX_TOKEN;

export default function (lang: string): Map {
  const center = process.env.MAP_CENTER
    ? JSON.parse(process.env.MAP_CENTER)
    : [0, 0];
  const zoom = process.env.MAP_ZOOM ? parseInt(process.env.MAP_ZOOM) : 2;
  const bbox = process.env.GEOCODER_BBOX
    ? JSON.parse(process.env.GEOCODER_BBOX)
    : null;
  const countries = process.env.GEOCODER_COUNTRIES;

  const defaultMapStyle = "mapbox://styles/mapbox/dark-v10";
  const mapStyle = process.env.MAP_STYLE ?? defaultMapStyle;
  // Initialize map.
  map = new mapboxgl.Map({
    center,
    container: "map",
    hash: true,
    style: mapStyle,
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
