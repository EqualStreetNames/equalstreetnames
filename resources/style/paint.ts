"use strict";

export default {
  "line-color": [
    "case",
    ["==", ["to-boolean", ["get", "person"]], true],
    [
      "case",
      ["==", ["get", "gender", ["get", "person"]], "F"],
      "#800080",
      ["==", ["get", "gender", ["get", "person"]], "M"],
      "#FFFF00",
      ["==", ["get", "gender", ["get", "person"]], "X"],
      "#008040",
      "#DDDDDD"
    ],
    "#DDDDDD"
  ],
  "line-width": ["case", ["==", ["to-boolean", ["get", "person"]], true], 5, 1],
  "line-opacity": 0.8
};
