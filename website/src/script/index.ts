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
export let countries: string;
export let style: string;

export function init () {
  const html = document.querySelector('html') as HTMLHtmlElement;
  lang = html.getAttribute('lang') ?? 'en';

  initTheme();
}
