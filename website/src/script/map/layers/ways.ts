'use strict';

import { LinePaint, Map } from 'maplibre-gl';

import layout from '../style/layout';
import paint from '../style/paint';

import data from 'url:../../../../static/ways.geojson';

const attribution =
  '© <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

export default function (map: Map): void {
  map.addSource('geojson-ways', {
    type: 'geojson',
    attribution,
    data
  });

  map.addLayer({
    id: 'layer-ways',
    type: 'line',
    source: 'geojson-ways',
    layout,
    paint: paint() as LinePaint
  });
}
