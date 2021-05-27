'use strict';

import mapboxgl, { Map, MapboxOptions, NavigationControl, ScaleControl } from 'maplibre-gl';
import MapboxGeocoder from '@mapbox/mapbox-gl-geocoder';

import addBoundary from './map/layers/boundary';
import addRelations from './map/layers/relation';
import addWays from './map/layers/ways';
import addEvents from './map/events';

import { lang, center, zoom, bbox, countries, style, bounds } from './index';

export let map: Map;

mapboxgl.accessToken = process.env.MAPBOX_TOKEN || '';

export default async function (): Promise<Map> {
  const options: MapboxOptions = {
    container: 'map',
    hash: true,
    style
  };

  if (typeof center !== 'undefined' && typeof zoom !== 'undefined') {
    options.center = center;
    options.zoom = zoom;
  } else {
    options.bounds = bbox || bounds;
    options.fitBoundsOptions = { padding: 50 };
  }

  // Initialize map.
  map = new Map(options);

  // Change the map theme when the browser theme changes or the user changes it (only when the default theme is light or dark)
  if (style === 'mapbox://styles/mapbox/dark-v10' || style === 'mapbox://styles/mapbox/light-v10') {
    changeTheme();

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', _ => {
      changeTheme();
    });

    document.getElementById('theme-light')?.addEventListener('click', changeTheme);
    document.getElementById('theme-dark')?.addEventListener('click', changeTheme);
    document.getElementById('theme-system')?.addEventListener('click', changeTheme);
  } else {
    createMap();
  }

  map.on('idle', () => {
    document.body.classList.add('loaded');
  });

  return map;

  function changeTheme () {
    // Change the style
    switch (localStorage.getItem('theme') ?? 'system') {
      case 'light':
        options.style = 'mapbox://styles/mapbox/light-v10';
        break;
      case 'dark':
        options.style = 'mapbox://styles/mapbox/dark-v10';
        break;
      default:
        options.style = (window.matchMedia('(prefers-color-scheme: dark)').matches)
          ? 'mapbox://styles/mapbox/dark-v10'
          : 'mapbox://styles/mapbox/light-v10';
    }

    // Recreate the map
    // map.setStyle would remove the data
    map.remove();
    map = new Map(options);

    createMap();
  }

  function createMap () {
    // Add controls.
    const nav = new NavigationControl({ showCompass: false });
    map.addControl(nav, 'top-left');

    const scale = new ScaleControl({ unit: 'metric' });
    map.addControl(scale);

    const geocoder = new MapboxGeocoder({
      accessToken: mapboxgl.accessToken,
      bbox: bbox || bounds,
      countries,
      enableEventLogging: false,
      language: lang,
      mapboxgl: mapboxgl
    });
    map.addControl(geocoder);

    map.on('load', () => {
      map.resize();

      // Add GeoJSON sources.
      addRelations(map);
      addWays(map);
      addBoundary(map);

      // Add events
      addEvents(map);
    });
  }
}
