"use strict";

import mapboxgl from "mapbox-gl";

import initChart from "./chart";
import initMap from "./map";

mapboxgl.accessToken = process.env.MAPBOX_TOKEN;

console.log(process.env.MAPBOX_TOKEN);

const html = document.querySelector("html") as HTMLHtmlElement;
const lang = html.getAttribute("lang") ?? "en";

initMap(lang);

initChart(
  document.querySelector("#gender-chart > canvas") as HTMLCanvasElement
);
