"use strict";

interface StringMap {
  [key: string]: string;
}

const colors: StringMap = {
  f: "#cc00ca", // female
  fDesaturated: "#800080",
  m: "#C8C800", // male
  x: "#00a050", // transgender
  o: "#DDDDDD" // other (not a person)
};

export default colors;
