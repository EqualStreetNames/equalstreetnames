"use strict";

import mapboxgl, { Map, MapboxGeoJSONFeature, LngLat } from "mapbox-gl";

import popupContent from "../../popup";

interface Property {
  name: string;
  "name:fr"?: string | null;
  "name:nl"?: string | null;
  wikidata: string | null;
  etymology: string | null;
  details?: string;
}

export default function(
  map: Map,
  features: MapboxGeoJSONFeature[],
  lnglat: LngLat
): void {
  const properties = features[0].properties as Property;
  const details =
    typeof properties.details !== "undefined" && properties.details !== null
      ? JSON.parse(properties.details)
      : null;

  const lang =
    (document.querySelector("html") as HTMLElement).getAttribute("lang") ??
    "en";

  const html = popupContent(
    getStreetname(properties),
    properties.etymology ?? null,
    details
  );

  new mapboxgl.Popup({ maxWidth: "none" })
    .setLngLat(lnglat)
    .setHTML(html)
    .addTo(map);
}

function getStreetname(properties: {
  name: string;
  "name:fr"?: string | null;
  "name:nl"?: string | null;
}): string {
  const streetname = properties.name;
  const streetnameFr = properties["name:fr"] ?? "";
  const streetnameNl = properties["name:nl"] ?? "";

  // Bug in MapboxGL (see https://github.com/mapbox/mapbox-gl-js/issues/8497)
  if (
    streetnameFr !== null &&
    streetnameNl !== null &&
    streetnameFr !== "null" &&
    streetnameNl !== "null"
  ) {
    return `${streetnameFr}<br>${streetnameNl}`;
  } else {
    return streetname;
  }
}
