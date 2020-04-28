"use strict";

import initChart from "./chart";
import initMap from "./map";

export let lang: string;

export let center: [number, number];
export let zoom: number;
export let bbox: [number, number, number, number];
export let countries: string;
export let style: string;

export function init() {
  const html = document.querySelector("html") as HTMLHtmlElement;
  lang = html.getAttribute("lang") ?? "en";

  initMap();

  initChart(
    document.querySelector("#gender-chart > canvas") as HTMLCanvasElement
  );
}
