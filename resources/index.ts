"use strict";

import mapboxgl from "mapbox-gl";

import initChart from "./chart";
import initMap from "./map";

const html = document.querySelector("html") as HTMLHtmlElement;
const lang = html.getAttribute("lang") ?? "en";

initMap(lang);

initChart(
  document.querySelector("#gender-chart > canvas") as HTMLCanvasElement
);
