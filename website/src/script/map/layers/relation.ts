'use strict';

import { LinePaint, Map } from 'maplibre-gl';

import layout from '../style/layout';
import paint from '../style/paint';

import data from 'url:../../../../static/relations.geojson';

const attribution =
  '© <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

export default function (map: Map): void {
  map.addSource('geojson-relations', {
    type: 'geojson',
    attribution,
    data
  });

  map.addLayer({
    id: 'layer-relations',
    type: 'line',
    source: 'geojson-relations',
    layout,
    paint: paint() as LinePaint
  });
}
