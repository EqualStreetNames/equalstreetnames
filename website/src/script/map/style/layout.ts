'use strict';

export default {
  'line-cap': 'round',
  'line-join': 'round',
  'line-sort-key': [
    'case',
    ['==', ['typeof', ['get', 'gender']], 'string'],
    10,
    5
  ]
};
