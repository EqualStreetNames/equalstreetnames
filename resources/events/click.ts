"use strict";

import mapboxgl, { Map, MapMouseEvent } from "mapbox-gl";

const lang = "fr";

export default function(map: Map, event: MapMouseEvent): void {
  console.log(event.features[0].geometry);

  const properties = event.features[0].properties;

  const name = properties[`name:${lang}`] ?? properties.name;

  // Ensure that if the map is zoomed out such that multiple
  // copies of the feature are visible, the popup appears
  // over the copy being pointed to.
  // const coordinates = event.features[0].geometry.coordinates.slice();
  // while (Math.abs(event.lngLat.lng - coordinates[0]) > 180) {
  //   coordinates[0] += event.lngLat.lng > coordinates[0] ? 360 : -360;
  // }

  let html = `<strong>${name}</strong><br>`;

  if (properties.person !== null) {
    const personData = JSON.parse(properties.person);

    html += "<div>";

    // if (personData.image !== null) {
    //   html += `<div><img src="${personData.image}"></div>`;
    // }

    if (Object.keys(personData.labels).length > 0) {
      const label =
        personData.labels[lang] ??
        personData.labels.en ??
        personData.labels.fr ??
        personData.labels.nl ??
        personData.labels.de;

      html += `<div>${label.value}</div>`;
    }

    if (Object.keys(personData.descriptions).length > 0) {
      const description =
        personData.descriptions[lang] ??
        personData.descriptions.en ??
        personData.descriptions.fr ??
        personData.descriptions.nl ??
        personData.descriptions.de;

      html += `<div>${description.value}</div>`;
    }

    if (Object.keys(personData.sitelinks).length > 0) {
      const wikipedia =
        personData.sitelinks[`${lang}wiki`] ??
        personData.sitelinks.enwiki ??
        personData.sitelinks.frwiki ??
        personData.sitelinks.nlwiki ??
        personData.sitelinks.dewiki;

      html += `<div>Link to <a target="_blank" href="${wikipedia.url}">Wikipedia</a>.</div>`;
    }

    html += `<div>Data from <a target="_blank" href="https://www.wikidata.org/wiki/${properties.etymology}">Wikidata</a></div>`;

    html += "</div>";
  }

  new mapboxgl.Popup({
    maxWidth: "none"
  })
    .setLngLat(event.lngLat)
    .setHTML(html)
    .addTo(map);
}
