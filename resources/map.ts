"use strict";

import mapboxgl, { LngLatBoundsLike, Map, NavigationControl, ScaleControl } from "maplibre-gl";
import MapboxGeocoder from "@mapbox/mapbox-gl-geocoder";
import { feature as turfFeature } from "@turf/helpers";
import turfBBox from "@turf/bbox";

import addBoundary from "./map/layers/boundary";
import addRelations from "./map/layers/relation";
import addWays from "./map/layers/ways";
import addEvents from "./map/events";

import { lang, center, zoom, bbox, countries, style } from "./index";

export let map: Map;

mapboxgl.accessToken = process.env.MAPBOX_TOKEN;

var initialLocation = location.hash

export default async function (): Promise<Map> {
  // Initialize map.
  map = new Map({
    center: center || [0,0],
    container: "map",
    hash: true,
    style,
    zoom: zoom || 0,
  });

  const response = await fetch("/boundary.geojson");
  if (response.ok === true) {
    const boundary = await response.json();
    const boundingBox = turfBBox(turfFeature(boundary.geometries[0]));

    if(!initialLocation) {
      map.fitBounds(boundingBox as LngLatBoundsLike, { padding: 25 });
    }
  }

  // Add controls.
  const nav = new NavigationControl({ showCompass: false });
  map.addControl(nav, "top-left");

  const scale = new ScaleControl({ unit: "metric" });
  map.addControl(scale);

  const geocoder = new MapboxGeocoder({
    accessToken: mapboxgl.accessToken,
    bbox: boundingBox || bbox,
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

    if (typeof boundary !== "undefined") {
      addBoundary(map, boundary);
    }

    // Add events
    addEvents(map);
  });

  map.on("idle", () => {
    document.body.classList.add("loaded");
  });

  return map;
}
