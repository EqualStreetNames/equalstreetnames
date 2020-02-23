"use strict";

import mapboxgl, { Map, MapboxGeoJSONFeature, LngLat } from "mapbox-gl";

import getName from "../wikidata/labels";
import getBirth from "../wikidata/birth";
import getDeath from "../wikidata/death";
import getDescription from "../wikidata/descriptions";
import getWikipedia from "../wikidata/sitelinks";

import popupContent from "../popup";

interface Property {
  name: string;
  "name:fr"?: string;
  "name:nl"?: string;
  wikidata: string | null;
  etymology: string | null;
  person?: string;
}

export default function(
  map: Map,
  features: MapboxGeoJSONFeature[],
  lnglat: LngLat
): void {
  const properties = features[0].properties as Property;
  const person =
    typeof properties.person !== "undefined" && properties.person !== null
      ? JSON.parse(properties.person)
      : null;

  console.log(person);

  const lang =
    (document.querySelector("html") as HTMLElement).getAttribute("lang") ??
    "en";

  const html = popupContent(
    getStreetname(properties),
    properties.etymology ?? null,
    person !== null ? getName(person, lang) : null,
    person !== null ? getBirth(person) : null,
    person !== null ? getDeath(person) : null,
    person !== null ? getDescription(person, lang) : null,
    person !== null ? getWikipedia(person, lang) : null
  );

  new mapboxgl.Popup({ maxWidth: "none" })
    .setLngLat(lnglat)
    .setHTML(html)
    .addTo(map);
}

function getStreetname(properties: {
  name: string;
  "name:fr"?: string;
  "name:nl"?: string;
}): string {
  const streetname = properties.name;
  const streetnameFr = properties["name:fr"] ?? null;
  const streetnameNl = properties["name:nl"] ?? null;

  if (streetnameFr !== null && streetnameNl !== null) {
    return `${streetnameFr}<br>${streetnameNl}`;
  } else {
    return streetname;
  }
}
