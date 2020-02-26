"use strict";

export default {
  "line-cap": "round",
  "line-join": "round",
  "line-sort-key": [
    "case",
    ["==", ["to-boolean", ["get", "person"]], true],
    10,
    5
  ]
};
