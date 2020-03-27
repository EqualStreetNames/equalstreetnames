"use strict";

import colors from "../../colors";

export default {
  "line-color": [
    "case",
    [
      "all",
      ["==", ["to-boolean", ["get", "details"]], true],
      ["==", ["get", "person", ["get", "details"]], true]
    ],
    [
      "case",
      ["==", ["get", "gender", ["get", "details"]], "F"],
      colors.f,
      ["==", ["get", "gender", ["get", "details"]], "M"],
      colors.m,
      ["==", ["get", "gender", ["get", "details"]], "FX"],
      colors.fx,
      ["==", ["get", "gender", ["get", "details"]], "MX"],
      colors.mx,
      ["==", ["get", "gender", ["get", "details"]], "X"],
      colors.x,
      colors.o
    ],
    colors.o
  ],
  "line-width": [
    "interpolate",
    ["linear"],
    ["zoom"],
    13,
    [
      "case",
      [
        "all",
        ["==", ["to-boolean", ["get", "details"]], true],
        ["==", ["get", "person", ["get", "details"]], true]
      ],
      3,
      [
        "all",
        ["==", ["to-boolean", ["get", "details"]], true],
        ["==", ["get", "person", ["get", "details"]], false]
      ],
      2,
      1
    ],
    22,
    20
  ],
  "line-opacity": 0.8
};
