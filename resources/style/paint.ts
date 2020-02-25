"use strict";

import colors from "../colors";

export default {
  "line-color": [
    "case",
    ["==", ["to-boolean", ["get", "person"]], true],
    [
      "case",
      ["==", ["get", "gender", ["get", "person"]], "F"],
      colors.female,
      ["==", ["get", "gender", ["get", "person"]], "M"],
      colors.male,
      ["==", ["get", "gender", ["get", "person"]], "X"],
      colors.other,
      "#DDDDDD"
    ],
    "#DDDDDD"
  ],
  "line-width": ["case", ["==", ["to-boolean", ["get", "person"]], true], 3, 1],
  "line-opacity": 0.8
};
