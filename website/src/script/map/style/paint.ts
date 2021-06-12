'use strict';

import { colorsLight, colorsDark } from '../../colors';
import { theme } from '../../theme';

export default function () {
  const colors = theme === 'dark' ? colorsDark : colorsLight;

  return {
    'line-color': [
      'case',
      ['==', ['get', 'gender'], 'F'],
      colors.f,
      ['==', ['get', 'gender'], 'M'],
      colors.m,
      ['==', ['get', 'gender'], 'FX'],
      colors.fx,
      ['==', ['get', 'gender'], 'MX'],
      colors.mx,
      ['==', ['get', 'gender'], 'X'],
      colors.x,
      ['==', ['get', 'gender'], '?'],
      colors.u,
      ['==', ['get', 'gender'], '+'],
      colors.p,
      colors.o
    ],
    'line-width': [
      'interpolate',
      ['linear'],
      ['zoom'],
      13,
      1,
      22,
      ['case', ['==', ['typeof', ['get', 'gender']], 'string'], 20, 10]
    ],
    'line-opacity': 0.8
  };
};
