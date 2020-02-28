"use strict";

interface StringMap {
  [key: string]: string;
}

const global: StringMap = {
  f: "#cc00ca", // female
  m: "#C8C800", // male
  x: "#00a050", // transgender
  o: "#DDDDDD" // other (not a person)
};

const popup: StringMap = {
  f: "#800080",
  m: global.m,
  x: global.x,
  o: global.o
};

export { global, popup };
