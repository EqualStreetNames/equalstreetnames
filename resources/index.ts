"use strict";

import initChart from "./chart";
import initMap from "./map";

export let lang: string;

export let center: [number, number];
export let zoom: number;
export let bbox: [number, number, number, number];
export let countries: string;

export function init() {
  const html = document.querySelector("html") as HTMLHtmlElement;
  lang = html.getAttribute("lang") ?? "en";

  initMap(lang, center, zoom, bbox, countries);

  initChart(
    document.querySelector("#gender-chart > canvas") as HTMLCanvasElement
  );
}
