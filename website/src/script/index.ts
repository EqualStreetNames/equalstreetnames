'use strict';

import 'bootstrap/js/dist/collapse';
import 'bootstrap/js/dist/dropdown';
import 'bootstrap/js/dist/modal';

import initTheme from './theme';

export { bounds, lastUpdate, statistics } from '../../static/static.json';

export let lang: string;

export let center: [number, number];
export let zoom: number;
export let bbox: [number, number, number, number];
export let style: string;

(function () {
  const html = document.querySelector('html') as HTMLHtmlElement;
  lang = html.getAttribute('lang') ?? 'en';

  const data = document.getElementById('map')?.dataset;

  if (typeof data?.style !== 'undefined') {
    style = data.style;
  }
  if (typeof data?.center !== 'undefined' && typeof data?.zoom !== 'undefined') {
    center = JSON.parse(data.center);
    zoom = parseFloat(data.zoom);
  }
  if (typeof data?.bbox !== 'undefined') {
    bbox = JSON.parse(data.bbox);
  }

  initTheme();
})();
