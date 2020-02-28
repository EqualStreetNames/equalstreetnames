"use strict";

import { global as colors } from "../../colors";

export default {
  "line-color": [
    "case",
    ["==", ["to-boolean", ["get", "person"]], true],
    [
      "case",
      ["==", ["get", "gender", ["get", "person"]], "F"],
      colors.f,
      ["==", ["get", "gender", ["get", "person"]], "M"],
      colors.m,
      ["==", ["get", "gender", ["get", "person"]], "X"],
      colors.x,
      colors.o
    ],
    colors.o
  ],
  "line-width": ["case", ["==", ["to-boolean", ["get", "person"]], true], 3, 1],
  "line-opacity": 0.8
};
