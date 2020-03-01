"use strict";

export default {
  "line-cap": "round",
  "line-join": "round",
  "line-sort-key": [
    "case",
    [
      "all",
      ["==", ["to-boolean", ["get", "details"]], true],
      ["==", ["get", "person", ["get", "details"]], true]
    ],
    10,
    5
  ]
};
