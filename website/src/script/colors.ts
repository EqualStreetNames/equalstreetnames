'use strict';

const colorsLight: {[key: string]: string} = {
  f: '#800080', // female (cis)
  m: '#C8C800', // male (cis)
  fx: '#00a050', // female (transgender)
  mx: '#00a050', // male (transgender)
  x: '#00a050', // intersex
  nb: '#00a050', // non-binary
  u: '#808080', // unknown
  p: '#A46440', // multiple
  o: '#AAAAAA' // other (not a person or multiple)
};

const colorsDark: {[key: string]: string} = {
  f: '#F46D43', // female (cis)
  m: '#FEE296', // male (cis)
  fx: '#74ADD1', // female (transgender)
  mx: '#74ADD1', // male (transgender)
  x: '#74ADD1', // intersex
  nb: '#74ADD1', // non-binary
  u: '#808080', // unknown
  p: '#8073AC', // multiple
  o: '#525252' // other (not a person or multiple)
};

export { colorsLight, colorsDark };
