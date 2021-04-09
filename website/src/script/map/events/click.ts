'use strict';

import mapboxgl, { Map, MapboxGeoJSONFeature, LngLat } from 'maplibre-gl';

import popupContent from '../../popup';

interface Property {
  name: string;
  wikidata: string | null;
  etymology: string | null;
  details?: string;
}

export default function (
  map: Map,
  features: MapboxGeoJSONFeature[],
  lnglat: LngLat
): void {
  const properties = features[0].properties as Property;

  const streetname = getStreetname(properties);
  const details =
    typeof properties.details !== 'undefined' && properties.details !== null
      ? JSON.parse(properties.details)
      : null;

  const html = popupContent(streetname, details);

  new mapboxgl.Popup({ maxWidth: 'none' })
    .setLngLat(lnglat)
    .setHTML(html)
    .addTo(map);
}

function getStreetname (properties: { name: string }): string {
  // Bug in MapboxGL (see https://github.com/mapbox/mapbox-gl-js/issues/8497)
  if (properties.name === null || properties.name === 'null') {
    return '';
  }

  const matches = properties.name.match(/^(.+) - (.+)$/);

  if (matches !== null && matches.length > 1) {
    matches.shift();

    return matches.join('<br>');
  }

  return properties.name;
}
