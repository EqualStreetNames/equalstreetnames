'use strict';

import { Map } from 'maplibre-gl';

import layout from '../style/layout';
import paint from '../style/paint';

const attribution =
  '© <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

export default function (map: Map): void {
  map.addSource('geojson-relations', {
    type: 'geojson',
    attribution,
    data: '/relations.geojson'
  });

  map.addLayer({
    id: 'layer-relations',
    type: 'line',
    source: 'geojson-relations',
    layout,
    paint
  });
}
