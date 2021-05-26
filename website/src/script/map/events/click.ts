'use strict';

import mapboxgl, { Map, MapboxGeoJSONFeature, LngLat } from 'maplibre-gl';

import popupContent from '../../popup';

export default function (
  map: Map,
  features: MapboxGeoJSONFeature[],
  lnglat: LngLat
): void {
  const html = popupContent(features[0]);

  new mapboxgl.Popup({ maxWidth: 'none' })
    .setLngLat(lnglat)
    .setHTML(html)
    .addTo(map);
}
