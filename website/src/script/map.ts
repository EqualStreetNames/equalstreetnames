'use strict';

import mapboxgl, { Map, MapboxOptions, NavigationControl, ScaleControl } from 'maplibre-gl';
import MapboxGeocoder from '@mapbox/mapbox-gl-geocoder';

import addBoundary from './map/layers/boundary';
import addRelations from './map/layers/relation';
import addWays from './map/layers/ways';
import addEvents from './map/events';

import { lang, center, zoom, bbox, style, bounds } from './index';
import { theme } from './theme';

export let map: Map;

mapboxgl.accessToken = process.env.MAPBOX_TOKEN || '';

export default async function (): Promise<Map> {
  const options: MapboxOptions = {
    container: 'map',
    hash: true,
    style: typeof style !== 'undefined' ? style : (theme === 'dark' ? 'mapbox://styles/mapbox/dark-v10' : 'mapbox://styles/mapbox/light-v10')
  };

  if (typeof center !== 'undefined' && typeof zoom !== 'undefined') {
    options.center = center;
    options.zoom = zoom;
  } else {
    options.bounds = bbox || bounds;
    options.fitBoundsOptions = { padding: 50 };
  }

  // Initialize map.
  if (typeof map !== 'undefined') {
    map.remove();
  }
  map = new Map(options);

  // Add controls.
  const nav = new NavigationControl({ showCompass: false });
  map.addControl(nav, 'top-left');

  const scale = new ScaleControl({ unit: 'metric' });
  map.addControl(scale);

  const geocoder = new MapboxGeocoder({
    accessToken: mapboxgl.accessToken,
    bbox: bbox || bounds,
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

  map.on('idle', () => {
    document.body.classList.add('loaded');
  });

  return map;
}
