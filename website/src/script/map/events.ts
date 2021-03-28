'use strict';

import { Map, MapMouseEvent } from 'maplibre-gl';

import onClick from './events/click';
import onMouseEnter from './events/mouseenter';
import onMouseLeave from './events/mouseleave';

export default function (map: Map): void {
  // Add click event.
  map.on('click', (event: MapMouseEvent) => {
    const bbox = [
      [event.point.x - 5, event.point.y - 5],
      [event.point.x + 5, event.point.y + 5]
    ];
    const features = map.queryRenderedFeatures(bbox, {
      layers: ['layer-relations', 'layer-ways']
    });

    if (features.length > 0) {
      onClick(map, features, event.lngLat);
    }
  });

  // Change the cursor to a pointer when the mouse is over a layer.
  map.on('mouseenter', 'layer-relations', event => onMouseEnter(map, event));
  map.on('mouseenter', 'layer-ways', event => onMouseEnter(map, event));

  // Change it back to a pointer when it leaves.
  map.on('mouseleave', 'layer-relations', event => onMouseLeave(map, event));
  map.on('mouseleave', 'layer-ways', event => onMouseLeave(map, event));
}
