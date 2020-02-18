"use strict";

import mapboxgl from "mapbox-gl";
import MapboxGeocoder from "@mapbox/mapbox-gl-geocoder";

import onClick from "./events/click";
import onMouseEnter from "./events/mouseenter";
import onMouseLeave from "./events/mouseleave";
import addRelations from "./layers/relation";
import addWays from "./layers/ways";

mapboxgl.accessToken =
  "pk.eyJ1IjoiamJlbGllbiIsImEiOiJjazZxa2t1OTUwYTc4M25xbGRsZWZ6bWhvIn0.h4pue9yL6pEYH8rjluftMw";

// Initialize map.
const map = new mapboxgl.Map({
  center: [4.3651, 50.8355],
  container: "map",
  hash: true,
  style: "mapbox://styles/mapbox/light-v10",
  zoom: 10
});

// Add controls.
const nav = new mapboxgl.NavigationControl({ showCompass: false });
map.addControl(nav, "top-left");

const scale = new mapboxgl.ScaleControl({ unit: "metric" });
map.addControl(scale);

const geocoder = new MapboxGeocoder({
  accessToken: mapboxgl.accessToken,
  countries: "be",
  enableEventLogging: false,
  mapboxgl: mapboxgl
});
map.addControl(geocoder);

// Add GeoJSON sources & mouse events.
map.on("load", () => {
  // Add GeoJSON sources.
  addRelations(map);
  addWays(map);

  // Add click event.
  map.on("click", "layer-relations", event => onClick(map, event));
  map.on("click", "layer-ways", event => onClick(map, event));

  // Change the cursor to a pointer when the mouse is over a layer.
  map.on("mouseenter", "layer-relations", event => onMouseEnter(map, event));
  map.on("mouseenter", "layer-ways", event => onMouseEnter(map, event));

  // Change it back to a pointer when it leaves.
  map.on("mouseleave", "layer-relations", event => onMouseLeave(map, event));
  map.on("mouseleave", "layer-ways", event => onMouseLeave(map, event));
});
