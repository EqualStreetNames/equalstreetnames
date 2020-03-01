"use strict";

import initChart from "./chart";
import initMap from "./map";

export let lang: string;

const html = document.querySelector("html") as HTMLHtmlElement;
lang = html.getAttribute("lang") ?? "en";

initMap(lang);

initChart(
  document.querySelector("#gender-chart > canvas") as HTMLCanvasElement
);
